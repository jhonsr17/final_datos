<?php
require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../helpers/session.php';
require_once __DIR__ . '/../helpers/utils.php';
require_once __DIR__ . '/standings.php';

startSession();
$db = getDb();

// Tournaments dropdown
$tournaments = $db->query('SELECT id, name FROM tournaments ORDER BY created_at DESC')->fetch_all(MYSQLI_ASSOC);
$tournamentId = (int) getParam('tournament_id', 0);

$standings = [];
$tournamentType = null;
if ($tournamentId > 0) {
	$stmt = $db->prepare('SELECT game_type FROM tournaments WHERE id = ?');
	$stmt->bind_param('i', $tournamentId);
	$stmt->execute();
	$tournamentType = $stmt->get_result()->fetch_assoc()['game_type'] ?? null;
	$stmt->close();

	$q = $db->prepare('SELECT s.*, t.name AS team_name FROM standings s JOIN teams t ON t.id = s.team_id WHERE s.tournament_id = ? ORDER BY s.points DESC, s.kills DESC, s.placement_points DESC');
	$q->bind_param('i', $tournamentId);
	$q->execute();
	$standings = $q->get_result()->fetch_all(MYSQLI_ASSOC);
	$q->close();
}
?>
<div class="row justify-content-center">
	<div class="col-12 col-lg-10">
		<h2 class="mb-3">Standings</h2>
		<form class="card p-3 shadow-sm mb-3" method="get">
			<input type="hidden" name="page" value="standings">
			<div class="row g-3 align-items-end">
				<div class="col-12 col-md-8">
					<label class="form-label">Tournament</label>
					<select name="tournament_id" class="form-select" onchange="this.form.submit()">
						<option value="">Select...</option>
						<?php foreach ($tournaments as $t): ?>
							<option value="<?php echo (int) $t['id']; ?>" <?php echo $tournamentId === (int) $t['id'] ? 'selected' : ''; ?>>
								<?php echo sanitize($t['name']); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="col-12 col-md-4">
					<button class="btn btn-outline-secondary w-100" type="submit">View</button>
				</div>
			</div>
		</form>

		<?php if ($tournamentId > 0): ?>
			<div class="d-flex justify-content-between align-items-center mb-2">
				<div class="text-muted">Scoring: <?php echo $tournamentType === 'BR' ? 'BR (placement + kills)' : 'VS (3/1/0)'; ?></div>
				<form method="post" action="/index.php?page=standings&tournament_id=<?php echo (int) $tournamentId; ?>">
					<input type="hidden" name="recalc" value="1">
					<button class="btn btn-sm btn-outline-primary">Recalculate</button>
				</form>
			</div>
			<?php
				if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recalc'])) {
					recalculateStandings($tournamentId);
					redirect('/index.php?page=standings&tournament_id=' . (int) $tournamentId);
				}
			?>
			<div class="table-responsive card shadow-sm">
				<table class="table table-striped align-middle mb-0">
					<thead class="table-light">
						<tr>
							<th>#</th>
							<th>Team</th>
							<th>Points</th>
							<th>W</th>
							<th>D</th>
							<th>L</th>
							<th>Kills</th>
							<th>Placement Pts</th>
						</tr>
					</thead>
					<tbody>
						<?php if (!$standings): ?>
							<tr><td colspan="8" class="text-center text-muted">No standings yet.</td></tr>
						<?php else: ?>
							<?php $rank = 1; foreach ($standings as $s): ?>
								<tr>
									<td><?php echo $rank++; ?></td>
									<td><?php echo sanitize($s['team_name']); ?></td>
									<td class="fw-bold"><?php echo (int) $s['points']; ?></td>
									<td><?php echo (int) $s['wins']; ?></td>
									<td><?php echo (int) $s['draws']; ?></td>
									<td><?php echo (int) $s['losses']; ?></td>
									<td><?php echo (int) $s['kills']; ?></td>
									<td><?php echo (int) $s['placement_points']; ?></td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
	</div>
</div>

