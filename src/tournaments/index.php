<?php
require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../helpers/session.php';
require_once __DIR__ . '/../helpers/utils.php';

startSession();
$db = getDb();
$res = $db->query('SELECT t.*, u.username AS owner FROM tournaments t JOIN users u ON u.id = t.created_by ORDER BY t.created_at DESC');
$tournaments = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
	<h2 class="mb-0">Tournaments</h2>
	<a href="index.php?page=tournaments_create" class="btn btn-primary">Create Tournament</a>
</div>
<div class="row g-3">
	<?php if (!$tournaments): ?>
		<div class="col-12"><div class="alert alert-info">No tournaments yet.</div></div>
	<?php endif; ?>
	<?php foreach ($tournaments as $t): ?>
		<div class="col-12 col-md-6 col-lg-4">
			<div class="tournament-card card h-100 shadow-sm">
				<?php
				$gameSlug = strtolower($t['game'] ?? '');
				$map = [
					'apex' => 'public/img/apex.jpg',
					'cod' => 'public/img/cod.jpg',
					'pubg' => 'public/img/pubg.jpg',
					'pub' => 'public/img/pub.jpg',
					'rocketleague' => 'public/img/rocketleague.jpg',
				];
				$img = isset($map[$gameSlug]) ? $map[$gameSlug] : 'public/img/pub.jpg';
				?>
				<img src="<?php echo $img; ?>" alt="Game">
				<div class="card-body">
					<h5 class="card-title mb-1"><?php echo sanitize($t['name']); ?></h5>
					<div class="text-muted small mb-2">Type: <?php echo $t['game_type'] === 'BR' ? 'Battle Royale' : 'Versus'; ?></div>
					<p class="mb-2"><?php echo nl2br(sanitize($t['description'])); ?></p>
					<div class="text-muted small">By <?php echo sanitize($t['owner']); ?> · <?php echo sanitize($t['created_at']); ?></div>
				</div>
				<div class="card-footer d-flex gap-2">
					<a class="btn btn-sm btn-outline-primary" href="index.php?page=tournament_view&id=<?php echo (int) $t['id']; ?>">View</a>
					<a class="btn btn-sm btn-outline-secondary" href="index.php?page=tournaments_edit&id=<?php echo (int) $t['id']; ?>">Edit</a>
					<a class="btn btn-sm btn-outline-danger" href="index.php?page=tournaments_delete&id=<?php echo (int) $t['id']; ?>" onclick="return confirm('Delete this tournament?')">Delete</a>
					<a class="btn btn-sm btn-primary ms-auto" href="index.php?page=tournaments_join&id=<?php echo (int) $t['id']; ?>">Join</a>
					<a class="btn btn-sm btn-success" href="index.php?page=matches_create&tournament_id=<?php echo (int) $t['id']; ?>">Create Match</a>
				</div>
			</div>
		</div>
	<?php endforeach; ?>
</div>

