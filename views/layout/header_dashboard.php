<?php
require_once __DIR__ . '/../../src/helpers/session.php';
require_once __DIR__ . '/../../src/helpers/utils.php';
startSession();

if (!isset($_SESSION['user_id'])) {
	redirect('index.php');
}
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Thor-Nament Dashboard</title>

	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="public/css/styles.css" rel="stylesheet">
</head>

<body class="dashboard-body bg-dark text-light">

<div class="d-flex">

	<!-- SIDEBAR -->
	<aside class="sidebar bg-black p-3">
		<h4 class="text-light fw-bold mb-4">Menu</h4>
		<nav class="nav flex-column gap-3">
			<a class="nav-link text-light" href="index.php?page=dashboard">Newsfeed</a>
			<a class="nav-link text-light" href="index.php?page=tournaments">Tournaments</a>
			<a class="nav-link text-light" href="index.php?page=standings">Leaderboards</a>
			<a class="nav-link text-light" href="index.php?page=games">Games</a>
			<a class="nav-link text-light" href="index.php?page=players">Players</a>
		</nav>
	</aside>

	<!-- MAIN CONTENT -->
	<main class="flex-grow-1 p-4">


