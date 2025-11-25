<?php
require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../helpers/session.php';
require_once __DIR__ . '/../helpers/utils.php';

ensureLoggedIn();
$db = getDb();
$tournamentId = (int) getParam('id', 0);
if ($tournamentId <= 0) {
	redirect('index.php?page=tournaments');
}

// Load user teams
$stmt = $db->prepare('SELECT id, name FROM teams WHERE owner_user_id = ? ORDER BY created_at DESC');
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$teamsRes = $stmt->get_result();
$userTeams = $teamsRes->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$teamId = (int) postParam('team_id', 0);
	if ($teamId <= 0) {
		$errors[] = 'Please choose one of your teams.';
	} else {
		// Ensure team belongs to user
		$stmt = $db->prepare('SELECT id FROM teams WHERE id = ? AND owner_user_id = ?');
		$stmt->bind_param('ii', $teamId, $_SESSION['user_id']);
		$stmt->execute();
		$ok = $stmt->get_result()->fetch_assoc();
		$stmt->close();
		if (!$ok) {
			$errors[] = 'Invalid team selection.';
		} else {
			// Insert to tournament_teams
			$stmt = $db->prepare('INSERT IGNORE INTO tournament_teams (tournament_id, team_id) VALUES (?, ?)');
			$stmt->bind_param('ii', $tournamentId, $teamId);
			if ($stmt->execute()) {
				$success = 'Team joined the tournament.';
			} else {
				$errors[] = 'Could not join tournament.';
			}
			$stmt->close();
		}
	}
}
?>
<div class="row justify-content-center">
	<div class="col-12 col-md-8 col-lg-6">
		<h2 class="mb-3">Join Tournament</h2>
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
		<form method="post" class="card p-3 shadow-sm">
			<div class="mb-3">
				<label class="form-label">Choose your team</label>
				<select name="team_id" class="form-select" required>
					<option value="">Select...</option>
					<?php foreach ($userTeams as $t): ?>
						<option value="<?php echo (int) $t['id']; ?>"><?php echo sanitize($t['name']); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<button class="btn btn-primary" type="submit">Join</button>
			<a href="index.php?page=tournaments" class="btn btn-outline-secondary">Back</a>
			<a href="index.php?page=teams_create&next=join&tournament_id=<?php echo (int) $tournamentId; ?>" class="btn btn-success">Create a team</a>
		</form>
		<?php if (!$userTeams): ?>
			<div class="alert alert-info mt-3">
				You have no teams yet. <a href="index.php?page=teams_create&next=join&tournament_id=<?php echo (int) $tournamentId; ?>">Create a team</a>.
			</div>
		<?php endif; ?>
	</div>
</div>

