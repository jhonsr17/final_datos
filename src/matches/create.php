<?php
require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../helpers/session.php';
require_once __DIR__ . '/../helpers/utils.php';

ensureLoggedIn();
$db = getDb();
$tournamentId = (int) getParam('tournament_id', 0);

// Load tournaments for dropdown (created by user or all? show all for simplicity)
$tournaments = $db->query('SELECT id, name, game_type FROM tournaments ORDER BY created_at DESC')->fetch_all(MYSQLI_ASSOC);
$selectedTournament = null;
$teamsInTournament = [];

if ($tournamentId > 0) {
	$stmt = $db->prepare('SELECT id, name, game_type FROM tournaments WHERE id = ?');
	$stmt->bind_param('i', $tournamentId);
	$stmt->execute();
	$selectedTournament = $stmt->get_result()->fetch_assoc();
	$stmt->close();
	if ($selectedTournament) {
		$stmt = $db->prepare('SELECT tt.team_id AS id, t.name FROM tournament_teams tt JOIN teams t ON t.id = tt.team_id WHERE tt.tournament_id = ? ORDER BY t.name');
		$stmt->bind_param('i', $tournamentId);
		$stmt->execute();
		$teamsInTournament = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
		$stmt->close();
	}
}

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$tournamentId = (int) postParam('tournament_id', 0);
	if ($tournamentId <= 0) {
		$errors[] = 'Please select a tournament.';
	} else {
		// Load type
		$stmt = $db->prepare('SELECT game_type FROM tournaments WHERE id = ?');
		$stmt->bind_param('i', $tournamentId);
		$stmt->execute();
		$type = $stmt->get_result()->fetch_assoc()['game_type'] ?? null;
		$stmt->close();
		if (!$type) {
			$errors[] = 'Tournament not found.';
		} else if ($type === 'VS') {
			$teamA = (int) postParam('team_a_id', 0);
			$teamB = (int) postParam('team_b_id', 0);
			$scoreA = (int) postParam('score_a', 0);
			$scoreB = (int) postParam('score_b', 0);
			if ($teamA <= 0 || $teamB <= 0 || $teamA === $teamB) {
				$errors[] = 'Select two different teams.';
			} else {
				$stmt = $db->prepare('INSERT INTO matches (tournament_id, team_a_id, team_b_id, score_a, score_b) VALUES (?, ?, ?, ?, ?)');
				$stmt->bind_param('iiiii', $tournamentId, $teamA, $teamB, $scoreA, $scoreB);
				if ($stmt->execute()) {
					$success = 'Match created.';
				} else {
					$errors[] = 'Could not create match.';
				}
				$stmt->close();
			}
		} else {
			// BR: create empty match then go to results entry
			// Ensure at least 1 team is enrolled
			$chk = $db->prepare('SELECT COUNT(*) AS c FROM tournament_teams WHERE tournament_id=?');
			$chk->bind_param('i', $tournamentId);
			$chk->execute();
			$cnt = (int) ($chk->get_result()->fetch_assoc()['c'] ?? 0);
			$chk->close();
			if ($cnt <= 0) {
				$errors[] = 'There must be at least one team enrolled to create a BR match.';
			} else {
				$stmt = $db->prepare('INSERT INTO matches (tournament_id) VALUES (?)');
			$stmt->bind_param('i', $tournamentId);
			if ($stmt->execute()) {
				$matchId = $stmt->insert_id;
				redirect('index.php?page=matches_results&match_id=' . (int) $matchId);
			} else {
				$errors[] = 'Could not create match.';
			}
			$stmt->close();
			}
		}
	}
}
?>
<div class="row justify-content-center">
	<div class="col-12 col-lg-8">
		<h2 class="mb-3">Create Match</h2>
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
		<form method="get" class="card p-3 shadow-sm mb-3">
			<input type="hidden" name="page" value="matches_create">
			<div class="mb-3">
				<label class="form-label">Tournament</label>
				<select name="tournament_id" class="form-select" onchange="this.form.submit()">
					<option value="">Select...</option>
					<?php foreach ($tournaments as $t): ?>
						<option value="<?php echo (int) $t['id']; ?>" <?php echo $tournamentId === (int) $t['id'] ? 'selected' : ''; ?>>
							<?php echo sanitize($t['name']); ?> (<?php echo $t['game_type'] === 'BR' ? 'BR' : 'VS'; ?>)
						</option>
					<?php endforeach; ?>
				</select>
			</div>
		</form>

		<?php if ($selectedTournament): ?>
			<?php if ($selectedTournament['game_type'] === 'VS'): ?>
				<form method="post" class="card p-3 shadow-sm">
					<input type="hidden" name="tournament_id" value="<?php echo (int) $selectedTournament['id']; ?>">
					<div class="row g-3 align-items-end">
						<div class="col-12 col-md-4">
							<label class="form-label">Team A</label>
							<select name="team_a_id" class="form-select" required>
								<option value="">Select...</option>
								<?php foreach ($teamsInTournament as $team): ?>
									<option value="<?php echo (int) $team['id']; ?>"><?php echo sanitize($team['name']); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="col-12 col-md-4">
							<label class="form-label">Team B</label>
							<select name="team_b_id" class="form-select" required>
								<option value="">Select...</option>
								<?php foreach ($teamsInTournament as $team): ?>
									<option value="<?php echo (int) $team['id']; ?>"><?php echo sanitize($team['name']); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="col-6 col-md-2">
							<label class="form-label">Score A</label>
							<input type="number" min="0" name="score_a" class="form-control" value="0">
						</div>
						<div class="col-6 col-md-2">
							<label class="form-label">Score B</label>
							<input type="number" min="0" name="score_b" class="form-control" value="0">
						</div>
					</div>
					<div class="mt-3">
						<button class="btn btn-primary" type="submit">Create VS Match</button>
						<a href="index.php?page=tournaments" class="btn btn-outline-secondary">Back</a>
					</div>
				</form>
			<?php else: ?>
				<div class="card p-3 shadow-sm">
					<p class="mb-2">Battle Royale selected. Click the button to create a match and enter results for each team.</p>
					<form method="post">
						<input type="hidden" name="tournament_id" value="<?php echo (int) $selectedTournament['id']; ?>">
						<button class="btn btn-success" type="submit">Create BR Match & Enter Results</button>
						<a href="index.php?page=tournaments" class="btn btn-outline-secondary">Back</a>
					</form>
				</div>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</div>

