<?php
require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../helpers/session.php';
require_once __DIR__ . '/../helpers/utils.php';

startSession();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$username = postParam('username');
	$email = postParam('email');
	$password = postParam('password');
	$confirm = postParam('confirm');

	if ($username === '' || $email === '' || $password === '' || $confirm === '') {
		$errors[] = 'All fields are required.';
	}
	if ($password !== $confirm) {
		$errors[] = 'Passwords do not match.';
	}
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$errors[] = 'Invalid email address.';
	}

	if (!$errors) {
		$db = getDb();
		$stmt = $db->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
		$stmt->bind_param('ss', $username, $email);
		$stmt->execute();
		$stmt->store_result();
		if ($stmt->num_rows > 0) {
			$errors[] = 'Username or email already exists.';
		}
		$stmt->close();

		if (!$errors) {
			$hash = password_hash($password, PASSWORD_DEFAULT);
			$stmt = $db->prepare('INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)');
			$stmt->bind_param('sss', $username, $email, $hash);
			if ($stmt->execute()) {
				$_SESSION['user_id'] = $stmt->insert_id;
				$_SESSION['username'] = $username;
				redirect('index.php');
			} else {
				$errors[] = 'Registration failed. Please try again.';
			}
			$stmt->close();
		}
	}
}
?>
<div class="row justify-content-center">
	<div class="col-12 col-md-6 col-lg-5">
		<h2 class="mb-3">Create Account</h2>
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
				<label class="form-label">Username</label>
				<input type="text" name="username" class="form-control" required>
			</div>
			<div class="mb-3">
				<label class="form-label">Email</label>
				<input type="email" name="email" class="form-control" required>
			</div>
			<div class="mb-3">
				<label class="form-label">Password</label>
				<input type="password" name="password" class="form-control" required>
			</div>
			<div class="mb-3">
				<label class="form-label">Confirm Password</label>
				<input type="password" name="confirm" class="form-control" required>
			</div>
			<button type="submit" class="btn btn-primary">Register</button>
			<a class="btn btn-link" href="/index.php?page=login">Already have an account? Login</a>
		</form>
	</div>
</div>

