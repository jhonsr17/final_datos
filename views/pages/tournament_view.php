<?php
require_once __DIR__ . '/../../src/helpers/session.php';
require_once __DIR__ . '/../../src/helpers/utils.php';
require_once __DIR__ . '/../../src/db/connection.php';

ensureLoggedIn();
$db = getDb();
$tournamentId = (int) getParam('id', 0);

if ($tournamentId <= 0) redirect('index.php?page=tournaments');

// Load tournament
$stmt = $db->prepare('SELECT * FROM tournaments WHERE id = ?');
$stmt->bind_param('i', $tournamentId);
$stmt->execute();
$tournament = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$tournament) {
	echo '<div class="alert alert-danger">Tournament not found.</div>';
	return;
}

// Load teams in tournament
$stmt = $db->prepare('SELECT t.id, t.name FROM tournament_teams tt JOIN teams t ON t.id = tt.team_id WHERE tt.tournament_id = ?');
$stmt->bind_param('i', $tournamentId);
$stmt->execute();
$teams = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Load players per team
$players = [];
foreach ($teams as $team) {
	$stmt = $db->prepare('SELECT nickname FROM players WHERE team_id = ?');
	$stmt->bind_param('i', $team['id']);
	$stmt->execute();
	$players[$team['id']] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
	$stmt->close();
}

// Load matches
$stmt = $db->prepare('SELECT * FROM matches WHERE tournament_id = ? ORDER BY id DESC');
$stmt->bind_param('i', $tournamentId);
$stmt->execute();
$matches = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Load standings
$stmt = $db->prepare('SELECT s.*, t.name FROM standings s JOIN teams t ON t.id = s.team_id WHERE tournament_id = ? ORDER BY points DESC');
$stmt->bind_param('i', $tournamentId);
$stmt->execute();
$standings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<h1 class="mb-4"><?php echo sanitize($tournament['name']); ?></h1>

<!-- INFO -->
<div class="card p-3 mb-4 bg-dark text-light">
	<h5>Info</h5>
	<p>Game type: <?php echo $tournament['game_type'] === 'BR' ? 'Battle Royale' : 'Versus'; ?></p>
	<a href="index.php?page=tournaments_join&id=<?php echo $tournamentId; ?>" class="btn btn-primary btn-sm">Add Team</a>
</div>

<!-- TEAMS -->
<div class="card p-3 mb-4 bg-dark text-light">
	<h5>Teams in tournament</h5>
	<?php if (!$teams): ?>
		<p class="text-muted">No teams yet.</p>
	<?php endif; ?>

	<?php foreach ($teams as $team): ?>
		<div class="mb-3">
			<strong><?php echo sanitize($team['name']); ?></strong>
			<ul>
				<?php if (empty($players[$team['id']])): ?>
					<li class="text-muted">No players</li>
				<?php endif; ?>
				<?php foreach ($players[$team['id']] as $p): ?>
					<li><?php echo sanitize($p['nickname']); ?></li>
				<?php endforeach; ?>
			</ul>
			<a href="index.php?page=teams_add_player&team_id=<?php echo $team['id']; ?>" class="btn btn-outline-secondary btn-sm">Add Player</a>
		</div>
	<?php endforeach; ?>
</div>

<!-- MATCHES -->
<div class="card p-3 mb-4 bg-dark text-light">
	<h5>Matches</h5>

	<a href="index.php?page=matches_create&tournament_id=<?php echo $tournamentId; ?>" class="btn btn-success btn-sm mb-3">Create Match</a>

	<?php foreach ($matches as $m): ?>
		<div class="mb-2 p-2 bg-secondary rounded">
			<strong>Match #<?php echo $m['id']; ?></strong>
			<a href="index.php?page=matches_results&match_id=<?php echo $m['id']; ?>" class="btn btn-primary btn-sm float-end">Enter Results</a>
		</div>
	<?php endforeach; ?>
</div>

<!-- STANDINGS -->
<div class="card p-3 mb-4 bg-dark text-light">
	<h5>Standings</h5>

	<?php if (!$standings): ?>
		<p class="text-muted">No standings yet.</p>
	<?php else: ?>
		<table class="table table-dark table-striped">
			<thead>
				<tr>
					<th>Team</th>
					<th>Points</th>
					<th>Wins</th>
					<th>Losses</th>
					<th>Draws</th>
					<th>Kills</th>
					<th>Placement Points</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($standings as $s): ?>
					<tr>
						<td><?php echo sanitize($s['name']); ?></td>
						<td><?php echo $s['points']; ?></td>
						<td><?php echo $s['wins']; ?></td>
						<td><?php echo $s['losses']; ?></td>
						<td><?php echo $s['draws']; ?></td>
						<td><?php echo $s['kills']; ?></td>
						<td><?php echo $s['placement_points']; ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>


