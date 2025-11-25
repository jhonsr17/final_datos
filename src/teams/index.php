<?php
require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../helpers/session.php';
require_once __DIR__ . '/../helpers/utils.php';

ensureLoggedIn();
$db = getDb();

// Load teams owned by current user
$stmt = $db->prepare('SELECT * FROM teams WHERE owner_user_id = ? ORDER BY created_at DESC');
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$teams = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
	<h2 class="mb-0">My Teams</h2>
	<a href="index.php?page=teams_create" class="btn btn-primary">Create Team</a>
</div>
<?php if (!$teams): ?>
	<div class="alert alert-info">You don't have any teams yet.</div>
<?php endif; ?>
<div class="row g-3">
	<?php foreach ($teams as $team): ?>
		<?php
			$stmt = $db->prepare('SELECT nickname FROM players WHERE team_id = ? ORDER BY id DESC');
			$stmt->bind_param('i', $team['id']);
			$stmt->execute();
			$players = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
			$stmt->close();
		?>
		<div class="col-12 col-md-6 col-lg-4">
			<div class="card h-100 shadow-sm">
				<div class="card-body">
					<h5 class="card-title"><?php echo sanitize($team['name']); ?></h5>
					<div class="mb-2 text-muted small">Created: <?php echo sanitize($team['created_at']); ?></div>
					<div>
						<strong>Players:</strong>
						<?php if (!$players): ?>
							<span class="text-muted">None</span>
						<?php else: ?>
							<ul class="mb-0">
								<?php foreach ($players as $p): ?>
									<li><?php echo sanitize($p['nickname']); ?></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</div>
				</div>
				<div class="card-footer d-flex gap-2">
					<a href="index.php?page=teams_add_player&team_id=<?php echo (int) $team['id']; ?>" class="btn btn-sm btn-outline-primary">Add Player</a>
				</div>
			</div>
		</div>
	<?php endforeach; ?>
</div>

