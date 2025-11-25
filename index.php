<?php
// Thor-Nament - Simple front controller
// Routes requests via ?page=... to simple feature modules
// Uses Bootstrap for styling and plain PHP for logic

require_once __DIR__ . '/src/helpers/session.php';
require_once __DIR__ . '/src/helpers/utils.php';

startSession();

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Whitelist of pages
$routes = [
	'home' => __DIR__ . '/views/pages/home.php',
	'login' => __DIR__ . '/src/auth/login.php',
	'register' => __DIR__ . '/src/auth/register.php',
	'logout' => __DIR__ . '/src/auth/logout.php',
	'dashboard' => __DIR__ . '/views/pages/dashboard.php',
	'tournaments' => __DIR__ . '/src/tournaments/index.php',
	'tournaments_create' => __DIR__ . '/src/tournaments/create.php',
	'tournaments_edit' => __DIR__ . '/src/tournaments/edit.php',
	'tournaments_delete' => __DIR__ . '/src/tournaments/delete.php',
	'tournaments_join' => __DIR__ . '/src/tournaments/join.php',
	'teams' => __DIR__ . '/src/teams/index.php',
	'teams_create' => __DIR__ . '/src/teams/create.php',
	'teams_add_player' => __DIR__ . '/src/teams/add_player.php',
	'matches_create' => __DIR__ . '/src/matches/create.php',
	'matches_results' => __DIR__ . '/src/matches/results.php',
	'standings' => __DIR__ . '/src/matches/standings_view.php',
	'tournament_view' => __DIR__ . '/views/pages/tournament_view.php',
	'games' => __DIR__ . '/views/pages/games.php',
	'players' => __DIR__ . '/views/pages/players.php',
];

// Default to home if route missing
$target = isset($routes[$page]) ? $routes[$page] : $routes['home'];

// Render with appropriate layout
$isLogged = isset($_SESSION['user_id']);

$isDashboardPage = in_array($page, [
	'dashboard',
	'teams',
	'teams_create',
	'teams_add_player',
	'tournaments',
	'tournaments_create',
	'tournaments_edit',
	'tournaments_delete',
	'tournaments_join',
	'matches_create',
	'matches_results',
	'standings',
	'tournament_view'
]);

if ($isLogged && $isDashboardPage) {
	include __DIR__ . '/views/layout/header_dashboard.php';
	include $target;
	include __DIR__ . '/views/layout/footer_dashboard.php';
} else {
	include __DIR__ . '/views/layout/header.php';
	include $target;
	include __DIR__ . '/views/layout/footer.php';
}

