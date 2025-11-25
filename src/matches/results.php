<?php
require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../helpers/session.php';
require_once __DIR__ . '/../helpers/utils.php';
require_once __DIR__ . '/standings.php';

ensureLoggedIn();
$db = getDb();
$matchId = (int) getParam('match_id', 0);
if ($matchId <= 0) {
	redirect('index.php?page=tournaments');
}

// Load match and tournament
$stmt = $db->prepare('SELECT m.*, t.name AS tournament_name, t.game_type FROM matches m JOIN tournaments t ON t.id = m.tournament_id WHERE m.id = ?');
$stmt->bind_param('i', $matchId);
$stmt->execute();
$match = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$match) {
	echo '<div class="alert alert-danger">Match not found.</div>';
	return;
}

// Teams in tournament
$stmt = $db->prepare('SELECT tt.team_id AS id, tm.name FROM tournament_teams tt JOIN teams tm ON tm.id = tt.team_id WHERE tt.tournament_id = ? ORDER BY tm.name');
$stmt->bind_param('i', $match['tournament_id']);
$stmt->execute();
$teams = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if ($match['game_type'] === 'VS') {
		$teamA = (int) postParam('team_a_id', 0);
		$teamB = (int) postParam('team_b_id', 0);
		$scoreA = (int) postParam('score_a', 0);
		$scoreB = (int) postParam('score_b', 0);
		if ($teamA <= 0 || $teamB <= 0 || $teamA === $teamB) {
			$errors[] = 'Select two different teams.';
		} else {
			$stmt = $db->prepare('UPDATE matches SET team_a_id=?, team_b_id=?, score_a=?, score_b=? WHERE id=?');
			$stmt->bind_param('iiiii', $teamA, $teamB, $scoreA, $scoreB, $matchId);
			if ($stmt->execute()) {
				recalculateStandings($match['tournament_id']);
				$success = 'Result saved and standings updated.';
			} else {
				$errors[] = 'Could not save result.';
			}
			$stmt->close();
		}
	} else {
		// BR results: loop over posted arrays placement[teamId], kills[teamId]
		$placements = isset($_POST['placement']) && is_array($_POST['placement']) ? $_POST['placement'] : [];
		$kills = isset($_POST['kills']) && is_array($_POST['kills']) ? $_POST['kills'] : [];

		// Clear previous results for this match to avoid duplicates
		$del = $db->prepare('DELETE FROM match_results WHERE match_id = ?');
		$del->bind_param('i', $matchId);
		$del->execute();
		$del->close();

		$ins = $db->prepare('INSERT INTO match_results (match_id, team_id, placement, kills) VALUES (?, ?, ?, ?)');
		foreach ($teams as $t) {
			$tid = (int) $t['id'];
			$pl = isset($placements[$tid]) && $placements[$tid] !== '' ? max(1, (int) $placements[$tid]) : null;
			$kl = isset($kills[$tid]) && $kills[$tid] !== '' ? max(0, (int) $kills[$tid]) : null;
			if ($pl !== null || $kl !== null) {
				$ins->bind_param('iiii', $matchId, $tid, $pl, $kl);
				$ins->execute();
			}
		}
		$ins->close();
		recalculateStandings($match['tournament_id']);
		$success = 'Battle Royale results saved and standings updated.';
	}
}
?>
<div class="row justify-content-center">
	<div class="col-12 col-lg-10">
		<h2 class="mb-1">Enter Results</h2>
		<div class="text-muted mb-3"><?php echo sanitize($match['tournament_name']); ?> (<?php echo $match['game_type'] === 'BR' ? 'Battle Royale' : 'Versus'; ?>)</div>
		<?php if ($success): ?>
			<div class="alert alert-success"><?php echo sanitize($success); ?></div>
		<?php endif; ?>
		<?php if ($errors): ?>
			<div class="alert alert-danger">
				<ul class="mb-0">
					<?php foreach ($errors as $e): ?>
						<li><?php echo sanitize($e); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<?php if ($match['game_type'] === 'VS'): ?>
			<form method="post" class="card p-3 shadow-sm">
				<div class="row g-3 align-items-end">
					<div class="col-12 col-md-4">
						<label class="form-label">Team A</label>
						<select name="team_a_id" class="form-select" required>
							<option value="">Select...</option>
							<?php foreach ($teams as $t): ?>
								<option value="<?php echo (int) $t['id']; ?>" <?php echo $match['team_a_id'] == $t['id'] ? 'selected' : ''; ?>>
									<?php echo sanitize($t['name']); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-12 col-md-4">
						<label class="form-label">Team B</label>
						<select name="team_b_id" class="form-select" required>
							<option value="">Select...</option>
							<?php foreach ($teams as $t): ?>
								<option value="<?php echo (int) $t['id']; ?>" <?php echo $match['team_b_id'] == $t['id'] ? 'selected' : ''; ?>>
									<?php echo sanitize($t['name']); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-6 col-md-2">
						<label class="form-label">Score A</label>
						<input type="number" name="score_a" class="form-control" min="0" value="<?php echo (int) $match['score_a']; ?>">
					</div>
					<div class="col-6 col-md-2">
						<label class="form-label">Score B</label>
						<input type="number" name="score_b" class="form-control" min="0" value="<?php echo (int) $match['score_b']; ?>">
					</div>
				</div>
				<div class="mt-3">
					<button class="btn btn-primary" type="submit">Save Result</button>
					<a class="btn btn-outline-secondary" href="index.php?page=tournaments">Back</a>
				</div>
			</form>
		<?php else: ?>
			<form method="post" class="card p-3 shadow-sm">
				<div class="table-responsive">
					<table class="table align-middle">
						<thead>
							<tr>
								<th>Team</th>
								<th style="width: 140px;">Placement</th>
								<th style="width: 140px;">Kills</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($teams as $t): ?>
								<tr>
									<td><?php echo sanitize($t['name']); ?></td>
									<td><input type="number" min="1" name="placement[<?php echo (int) $t['id']; ?>]" class="form-control" placeholder="e.g. 1"></td>
									<td><input type="number" min="0" name="kills[<?php echo (int) $t['id']; ?>]" class="form-control" placeholder="e.g. 5"></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				<div>
					<button class="btn btn-success" type="submit">Save BR Results</button>
					<a class="btn btn-outline-secondary" href="index.php?page=tournaments">Back</a>
				</div>
			</form>
		<?php endif; ?>
	</div>
</div>

