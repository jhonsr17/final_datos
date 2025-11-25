<?php
// Simple MySQL connection using mysqli
// Update credentials to match your local setup (XAMPP/WAMP/MAMP)

define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'e-sports');
define('DB_USER', 'root'); // default in XAMPP
define('DB_PASS', '');     // default in XAMPP is empty

/**
 * Returns a shared mysqli connection.
 * Exits with error message if connection fails.
 */
function getDb() {
	static $conn = null;
	if ($conn !== null) {
		return $conn;
	}

	$conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, (int) DB_PORT);
	if ($conn->connect_errno) {
		http_response_code(500);
		echo 'Database connection failed: ' . htmlspecialchars($conn->connect_error);
		exit;
	}
	$conn->set_charset('utf8mb4');
	return $conn;
}


