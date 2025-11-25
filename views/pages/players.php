<?php
require_once __DIR__ . '/../../src/helpers/session.php';
require_once __DIR__ . '/../../src/helpers/utils.php';
require_once __DIR__ . '/../../src/db/connection.php';

ensureLoggedIn();
$db = getDb();

// Seed dummy players/teams on demand
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['seed'])) {
	$userId = (int) $_SESSION['user_id'];
	// Ensure at least one team for current user
	$teamIds = [];
	$res = $db->prepare('SELECT id FROM teams WHERE owner_user_id = ? ORDER BY id DESC');
	$res->bind_param('i', $userId);
	$res->execute();
	$teamIds = $res->get_result()->fetch_all(MYSQLI_ASSOC);
	$res->close();
	if (!$teamIds) {
		$stmt = $db->prepare('INSERT INTO teams (name, owner_user_id) VALUES (?, ?)');
		$names = ['Spartans', 'Valkyries', 'Titans'];
		foreach ($names as $n) {
			$stmt->bind_param('si', $n, $userId);
			$stmt->execute();
		}
		$stmt->close();
		$res = $db->prepare('SELECT id FROM teams WHERE owner_user_id = ? ORDER BY id DESC');
		$res->bind_param('i', $userId);
		$res->execute();
		$teamIds = $res->get_result()->fetch_all(MYSQLI_ASSOC);
		$res->close();
	}

	// Insert sample players per team (ignore duplicates by nickname/team)
	$sample = ['Rogue', 'Blaze', 'Shadow', 'Nova', 'Viper', 'Echo', 'Maverick', 'Ghost', 'Falcon', 'Zephyr'];
	$stmt = $db->prepare('INSERT INTO players (team_id, nickname) VALUES (?, ?)');
	foreach ($teamIds as $row) {
		$tid = (int) $row['id'];
		shuffle($sample);
		$pick = array_slice($sample, 0, 3);
		foreach ($pick as $nick) {
			$nn = $nick;
			$stmt->bind_param('is', $tid, $nn);
			@$stmt->execute();
		}
	}
	$stmt->close();
	$seedMsg = 'Sample players seeded.';
}

// List players
$q = $db->query('
	SELECT p.id, p.nickname, t.name AS team, u.username AS owner
	FROM players p
	JOIN teams t ON t.id = p.team_id
	LEFT JOIN users u ON u.id = t.owner_user_id
	ORDER BY p.id DESC
');
$players = $q ? $q->fetch_all(MYSQLI_ASSOC) : [];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
	<h2 class="mb-0">Players</h2>
	<form method="post">
		<input type="hidden" name="seed" value="1">
		<button class="btn btn-primary">Seed sample players</button>
	</form>
</div>
<?php if (!empty($seedMsg)): ?>
	<div class="alert alert-success"><?php echo sanitize($seedMsg); ?></div>
<?php endif; ?>

<?php if (!$players): ?>
	<div class="alert alert-info">
		No players yet. Use “Seed sample players” para crear datos de ejemplo.
	</div>
<?php else: ?>
	<div class="row g-3">
		<?php foreach ($players as $p): ?>
			<div class="col-12 col-md-6 col-lg-4">
				<div class="card h-100 shadow-sm">
					<div class="card-body">
						<h5 class="card-title mb-1"><?php echo sanitize($p['nickname']); ?></h5>
						<div class="small text-muted">Team: <?php echo sanitize($p['team']); ?></div>
						<div class="small text-muted">Owner: <?php echo sanitize($p['owner'] ?? ''); ?></div>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>


