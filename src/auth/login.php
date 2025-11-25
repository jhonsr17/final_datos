<?php
require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../helpers/session.php';
require_once __DIR__ . '/../helpers/utils.php';

startSession();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$identifier = postParam('identifier'); // username or email
	$password = postParam('password');
	if ($identifier === '' || $password === '') {
		$errors[] = 'All fields are required.';
	} else {
		$db = getDb();
		$stmt = $db->prepare('SELECT id, username, password_hash FROM users WHERE username = ? OR email = ? LIMIT 1');
		$stmt->bind_param('ss', $identifier, $identifier);
		$stmt->execute();
		$result = $stmt->get_result();
		$user = $result->fetch_assoc();
		$stmt->close();
		if ($user && password_verify($password, $user['password_hash'])) {
			$_SESSION['user_id'] = $user['id'];
			$_SESSION['username'] = $user['username'];
			redirect('index.php?page=dashboard');
		} else {
			$errors[] = 'Invalid credentials.';
		}
	}
}
?>
<div class="row justify-content-center">
	<div class="col-12 col-md-6 col-lg-5">
		<h2 class="mb-3">Login</h2>
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
				<label class="form-label">Username or Email</label>
				<input type="text" name="identifier" class="form-control" required>
			</div>
			<div class="mb-3">
				<label class="form-label">Password</label>
				<input type="password" name="password" class="form-control" required>
			</div>
			<button type="submit" class="btn btn-primary">Login</button>
			<a class="btn btn-link" href="/index.php?page=register">Create an account</a>
		</form>
	</div>
</div>

