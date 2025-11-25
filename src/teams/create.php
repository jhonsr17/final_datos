<?php
require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../helpers/session.php';
require_once __DIR__ . '/../helpers/utils.php';

ensureLoggedIn();
$errors = [];
$next = getParam('next');
$returnTournamentId = (int) getParam('tournament_id', 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$name = postParam('name');
	if ($name === '') {
		$errors[] = 'Team name is required.';
	} else {
		$db = getDb();
		$stmt = $db->prepare('INSERT INTO teams (name, owner_user_id) VALUES (?, ?)');
		$stmt->bind_param('si', $name, $_SESSION['user_id']);
		if ($stmt->execute()) {
			if ($next === 'join' && $returnTournamentId > 0) {
				redirect('index.php?page=tournaments_join&id=' . (int) $returnTournamentId . '&created_team=1');
			} else {
				redirect('index.php?page=teams');
			}
		} else {
			$errors[] = 'Could not create team. Name may already exist.';
		}
		$stmt->close();
	}
}
?>
<div class="row justify-content-center">
	<div class="col-12 col-md-8 col-lg-6">
		<h2 class="mb-3">Create Team</h2>
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
			<?php if ($next): ?>
				<input type="hidden" name="next" value="<?php echo sanitize($next); ?>">
			<?php endif; ?>
			<?php if ($returnTournamentId): ?>
				<input type="hidden" name="tournament_id" value="<?php echo (int) $returnTournamentId; ?>">
			<?php endif; ?>
			<div class="mb-3">
				<label class="form-label">Team Name</label>
				<input type="text" name="name" class="form-control" required>
			</div>
			<button class="btn btn-primary" type="submit">Create</button>
			<?php if ($next === 'join' && $returnTournamentId > 0): ?>
				<a class="btn btn-outline-secondary" href="index.php?page=tournaments_join&id=<?php echo (int) $returnTournamentId; ?>">Cancel</a>
			<?php else: ?>
				<a class="btn btn-outline-secondary" href="index.php?page=teams">Cancel</a>
			<?php endif; ?>
		</form>
	</div>
</div>

