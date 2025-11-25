<?php
require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../helpers/session.php';
require_once __DIR__ . '/../helpers/utils.php';

ensureLoggedIn();
$db = getDb();
$errors = [];
$id = (int) getParam('id', 0);

// Load existing
$stmt = $db->prepare('SELECT * FROM tournaments WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$tournament = $result->fetch_assoc();
$stmt->close();

if (!$tournament) {
	echo '<div class="alert alert-danger">Tournament not found.</div>';
	return;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$name = postParam('name');
	$gameType = postParam('game_type');
	$description = postParam('description');
	if ($name === '' || ($gameType !== 'BR' && $gameType !== 'VS')) {
		$errors[] = 'Name and valid game type are required.';
	}
	if (!$errors) {
		$stmt = $db->prepare('UPDATE tournaments SET name=?, game_type=?, description=? WHERE id=?');
		$stmt->bind_param('sssi', $name, $gameType, $description, $id);
		if ($stmt->execute()) {
			redirect('index.php?page=tournaments');
		} else {
			$errors[] = 'Could not update tournament.';
		}
		$stmt->close();
	}
}
?>
<div class="row justify-content-center">
	<div class="col-12 col-md-8 col-lg-7">
		<h2 class="mb-3">Edit Tournament</h2>
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
				<label class="form-label">Name</label>
				<input type="text" name="name" class="form-control" required value="<?php echo sanitize($tournament['name']); ?>">
			</div>
			<div class="mb-3">
				<label class="form-label">Game Type</label>
				<select name="game_type" class="form-select" required>
					<option value="BR" <?php echo $tournament['game_type'] === 'BR' ? 'selected' : ''; ?>>Battle Royale</option>
					<option value="VS" <?php echo $tournament['game_type'] === 'VS' ? 'selected' : ''; ?>>Versus</option>
				</select>
			</div>
			<div class="mb-3">
				<label class="form-label">Description</label>
				<textarea name="description" rows="4" class="form-control"><?php echo sanitize($tournament['description']); ?></textarea>
			</div>
			<button class="btn btn-primary" type="submit">Save</button>
			<a href="/index.php?page=tournaments" class="btn btn-outline-secondary">Cancel</a>
		</form>
	</div>
</div>

