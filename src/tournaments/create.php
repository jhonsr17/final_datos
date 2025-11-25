<?php
require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../helpers/session.php';
require_once __DIR__ . '/../helpers/utils.php';

ensureLoggedIn();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$name = postParam('name');
	$gameType = postParam('game_type');
	$game = strtolower(trim(postParam('game', 'pub')));
	$description = postParam('description');

	if ($name === '' || $description === '' || ($gameType !== 'BR' && $gameType !== 'VS')) {
		$errors[] = 'Name, description and valid game type are required.';
	}

	if (!$errors) {
		$db = getDb();

		// ensure 'game' column exists (one-time lazy migration)
		$colRes = $db->query("SHOW COLUMNS FROM tournaments LIKE 'game'");
		if ($colRes && $colRes->num_rows === 0) {
			$db->query("ALTER TABLE tournaments ADD COLUMN game VARCHAR(50) NULL AFTER game_type");
		}

		// normalize game to allowed set
		$allowedGames = ['apex','cod','pub','rocketleague'];
		if (!in_array($game, $allowedGames, true)) {
			$game = 'pub';
		}

		$stmt = $db->prepare('INSERT INTO tournaments (name, game_type, game, description, created_by) VALUES (?, ?, ?, ?, ?)');
		$stmt->bind_param('ssssi', $name, $gameType, $game, $description, $_SESSION['user_id']);
		if ($stmt->execute()) {
			$newId = $db->insert_id;
			redirect('index.php?page=tournament_view&id=' . (int) $newId);
		} else {
			$errors[] = 'Could not create tournament.';
		}
		$stmt->close();
	}
}
?>
<div class="row justify-content-center">
	<div class="col-12 col-md-8 col-lg-7">
		<h2 class="mb-3">Create Tournament</h2>
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
				<input type="text" name="name" class="form-control" required>
			</div>
			<div class="mb-3">
				<label class="form-label">Game</label>
				<input type="hidden" name="game" id="gameInput" value="pub">
				<div class="d-flex gap-2 flex-wrap">
					<button type="button" class="btn btn-outline-secondary game-btn" data-game="apex">Apex</button>
					<button type="button" class="btn btn-outline-secondary game-btn" data-game="cod">COD</button>
					<button type="button" class="btn btn-outline-secondary game-btn" data-game="pub">PUBG</button>
					<button type="button" class="btn btn-outline-secondary game-btn" data-game="rocketleague">Rocket League</button>
				</div>
			</div>
			<div class="mb-4">
				<div class="tournament-card card bg-dark text-light shadow-sm" style="max-width: 420px;">
					<img id="gamePreviewImg" src="public/img/pub.jpg" class="card-img-top rounded" alt="">
					<div class="card-body">
						<h5 class="card-title mb-1">Game preview</h5>
						<p class="card-date">Select a game to update the image</p>
					</div>
				</div>
			</div>
			<div class="mb-3">
				<label class="form-label">Game Type</label>
				<select name="game_type" class="form-select" required>
					<option value="">Select...</option>
					<option value="BR">Battle Royale</option>
					<option value="VS">Versus</option>
				</select>
			</div>
			<div class="mb-3">
				<label class="form-label">Description</label>
				<textarea name="description" rows="4" class="form-control" required></textarea>
			</div>
			<button class="btn btn-primary" type="submit">Create</button>
			<a href="index.php?page=tournaments" class="btn btn-outline-secondary">Cancel</a>
		</form>
	</div>
</div>
<script>
(function () {
	const imgMap = {
		apex: 'public/img/apex.jpg',
		cod: 'public/img/cod.jpg',
		pub: 'public/img/pub.jpg',
		rocketleague: 'public/img/rocketleague.jpg',
	}
	const input = document.getElementById('gameInput')
	const preview = document.getElementById('gamePreviewImg')
	const buttons = document.querySelectorAll('.game-btn')
	function setActive (game) {
		input.value = game
		preview.src = imgMap[game] || imgMap.pub
		buttons.forEach(function (b) {
			if (b.dataset.game === game) { b.classList.add('active') } else { b.classList.remove('active') }
		})
	}
	buttons.forEach(function (b) {
		b.addEventListener('click', function () { setActive(b.dataset.game) })
	})
	setActive(input.value || 'pub')
})()
</script>

