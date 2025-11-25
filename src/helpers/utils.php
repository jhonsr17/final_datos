<?php
// Common utility helpers

function postParam($key, $default = null) {
	return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
}

function getParam($key, $default = null) {
	return isset($_GET[$key]) ? trim($_GET[$key]) : $default;
}

function sanitize($value) {
	return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect($path) {
	// Build absolute URL within app base to avoid leaving virtual host path
	$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
	$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
	$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); // e.g. /E-SPORTS
	if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
		$url = $path;
	} else {
		$rel = ltrim($path, '/');
		$url = $scheme . '://' . $host . ($base ? $base : '') . '/' . $rel;
	}
	header('Location: ' . $url);
	exit;
}

function requirePost() {
	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		http_response_code(405);
		echo 'Method Not Allowed';
		exit;
	}
}


