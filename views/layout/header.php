<?php
require_once __DIR__ . '/../../src/helpers/session.php';
require_once __DIR__ . '/../../src/helpers/utils.php';
startSession();
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Thor-Nament</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
	<?php $cssVer = @filemtime(__DIR__ . '/../../public/css/styles.css') ?: time(); ?>
	<link href="public/css/styles.css?v=<?php echo $cssVer; ?>" rel="stylesheet">
	<?php $dashCssVer = @filemtime(__DIR__ . '/../../public/css/dashboard.css') ?: time(); ?>
	<link href="public/css/dashboard.css?v=<?php echo $dashCssVer; ?>" rel="stylesheet">
</head>
<body class="landing-body">
	<?php include __DIR__ . '/navbar.php'; ?>
	<main class="container py-4">

