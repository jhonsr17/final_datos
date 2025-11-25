<?php
// Session helpers

function startSession() {
	if (session_status() === PHP_SESSION_NONE) {
		// Secure-ish defaults for local dev
		session_set_cookie_params([
			'lifetime' => 0,
			'path' => '/',
			'httponly' => true,
			'samesite' => 'Lax',
		]);
		session_start();
	}
}

function ensureLoggedIn() {
	startSession();
	if (!isset($_SESSION['user_id'])) {
		header('Location: /index.php?page=login');
		exit;
	}
}

function logout() {
	startSession();
	$_SESSION = [];
	if (ini_get('session.use_cookies')) {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', $params['secure'] ?? false, $params['httponly'] ?? true);
	}
	session_destroy();
}


