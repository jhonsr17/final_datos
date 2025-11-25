<?php
require_once __DIR__ . '/../db/connection.php';

/**
 * Applies scoring rules and updates the standings table for a tournament.
 * - For VS: 3 points per win, 1 per draw, 0 for loss.
 * - For BR: placement points + 1 point per kill.
 */
function recalculateStandings($tournamentId) {
	$db = getDb();
	$tournamentId = (int) $tournamentId;
	if ($tournamentId <= 0) return;

	// Clear existing standings for the tournament
	$stmt = $db->prepare('DELETE FROM standings WHERE tournament_id = ?');
	$stmt->bind_param('i', $tournamentId);
	$stmt->execute();
	$stmt->close();

	// Determine tournament type
	$stmt = $db->prepare('SELECT game_type FROM tournaments WHERE id = ?');
	$stmt->bind_param('i', $tournamentId);
	$stmt->execute();
	$type = $stmt->get_result()->fetch_assoc()['game_type'] ?? null;
	$stmt->close();
	if (!$type) return;

	// Prepare map of teams participating
	$teamsRes = $db->prepare('SELECT team_id FROM tournament_teams WHERE tournament_id = ?');
	$teamsRes->bind_param('i', $tournamentId);
	$teamsRes->execute();
	$teamIds = array_map(function($row){ return (int) $row['team_id']; }, $teamsRes->get_result()->fetch_all(MYSQLI_ASSOC));
	$teamsRes->close();
	if (!$teamIds) return;

	$standings = [];
	foreach ($teamIds as $tid) {
		$standings[$tid] = [
			'points' => 0,
			'wins' => 0,
			'losses' => 0,
			'draws' => 0,
			'kills' => 0,
			'placement_points' => 0,
		];
	}

	if ($type === 'VS') {
		// Sum VS results from matches table
		$q = $db->prepare('SELECT team_a_id, team_b_id, score_a, score_b FROM matches WHERE tournament_id = ? AND team_a_id IS NOT NULL AND team_b_id IS NOT NULL');
		$q->bind_param('i', $tournamentId);
		$q->execute();
		$rows = $q->get_result()->fetch_all(MYSQLI_ASSOC);
		$q->close();
		foreach ($rows as $m) {
			$a = (int) $m['team_a_id']; $b = (int) $m['team_b_id'];
			$sa = (int) $m['score_a']; $sb = (int) $m['score_b'];
			if (!isset($standings[$a]) || !isset($standings[$b])) continue;
			if ($sa > $sb) {
				$standings[$a]['wins'] += 1; $standings[$a]['points'] += 3;
				$standings[$b]['losses'] += 1;
			} elseif ($sa < $sb) {
				$standings[$b]['wins'] += 1; $standings[$b]['points'] += 3;
				$standings[$a]['losses'] += 1;
			} else {
				$standings[$a]['draws'] += 1; $standings[$b]['draws'] += 1;
				$standings[$a]['points'] += 1; $standings[$b]['points'] += 1;
			}
		}
	} else {
		// BR placement points and kills from match_results
		// Placement scoring simple rule:
		// 1st=10, 2=8, 3=6, 4=5, 5=4, 6-10=2, else 0
		$placementPoints = function($placement) {
			if ($placement === 1) return 10;
			if ($placement === 2) return 8;
			if ($placement === 3) return 6;
			if ($placement === 4) return 5;
			if ($placement === 5) return 4;
			if ($placement >= 6 && $placement <= 10) return 2;
			return 0;
		};

		$q = $db->prepare('SELECT mr.team_id, mr.placement, mr.kills FROM match_results mr JOIN matches m ON m.id = mr.match_id WHERE m.tournament_id = ?');
		$q->bind_param('i', $tournamentId);
		$q->execute();
		$rows = $q->get_result()->fetch_all(MYSQLI_ASSOC);
		$q->close();
		foreach ($rows as $r) {
			$tid = (int) $r['team_id'];
			if (!isset($standings[$tid])) continue;
			$pl = isset($r['placement']) ? (int) $r['placement'] : null;
			$kl = isset($r['kills']) ? (int) $r['kills'] : 0;
			$pp = $pl ? $placementPoints($pl) : 0;
			$standings[$tid]['placement_points'] += $pp;
			$standings[$tid]['kills'] += $kl;
			$standings[$tid]['points'] += $pp + $kl; // total BR points = placement + kills
		}
	}

	// Insert aggregated standings
	$ins = $db->prepare('INSERT INTO standings (tournament_id, team_id, points, wins, losses, draws, kills, placement_points) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
	foreach ($standings as $tid => $s) {
		$ins->bind_param('iiiiiiii', $tournamentId, $tid, $s['points'], $s['wins'], $s['losses'], $s['draws'], $s['kills'], $s['placement_points']);
		$ins->execute();
	}
	$ins->close();
}


