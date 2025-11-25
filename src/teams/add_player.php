<?php
require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../helpers/session.php';
require_once __DIR__ . '/../helpers/utils.php';

ensureLoggedIn();
$db = getDb();
$teamId = (int) getParam('team_id', 0);
if ($teamId <= 0) {
	redirect('/index.php?page=teams');
}

// Verify ownership
$stmt = $db->prepare('SELECT * FROM teams WHERE id = ? AND owner_user_id = ? LIMIT 1');
$stmt->bind_param('ii', $teamId, $_SESSION['user_id']);
$stmt->execute();
$team = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$team) {
	echo '<div class="alert alert-danger">Team not found.</div>';
	return;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$nickname = postParam('nickname');
	if ($nickname === '') {
		$errors[] = 'Player nickname is required.';
	} else {
		$stmt = $db->prepare('INSERT INTO players (team_id, nickname) VALUES (?, ?)');
		$stmt->bind_param('is', $teamId, $nickname);
		if ($stmt->execute()) {
			redirect('/index.php?page=teams');
		} else {
			$errors[] = 'Could not add player.';
		}
		$stmt->close();
	}
}
?>
<div class="row justify-content-center">
	<div class="col-12 col-md-8 col-lg-6">
		<h2 class="mb-3">Add Player to <?php echo sanitize($team['name']); ?></h2>
		<?php if ($errors): ?>
			<div class="alert alert-danger">
				<ul class="mb-0">
					<?php foreach ($errors as $e): ?>
						<li><?php echo sanitize($e); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>
		<form method="post" class="card p-3 shadow-sm">
			<div class="mb-3">
				<label class="form-label">Player Nickname</label>
				<input type="text" name="nickname" class="form-control" required>
			</div>
			<button class="btn btn-primary" type="submit">Add Player</button>
			<a class="btn btn-outline-secondary" href="/index.php?page=teams">Back</a>
		</form>
	</div>
</div>

