<?php
require_once __DIR__ . '/../../src/helpers/session.php';
startSession();
$isLogged = isset($_SESSION['user_id']);
?>
<nav class="navbar navbar-expand-lg gamer-navbar border-0">
	<div class="container">
		<a class="navbar-brand fw-bold" href="index.php">Thor-Nament</a>

		<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>

		<div class="collapse navbar-collapse" id="nav">
			<ul class="navbar-nav me-auto mb-2 mb-lg-0">

				<li class="nav-item">
					<a class="nav-link" href="index.php?page=tournaments">Tournaments</a>
				</li>

				<li class="nav-item">
					<a class="nav-link" href="index.php?page=teams">Teams</a>
				</li>

				<li class="nav-item">
					<a class="nav-link" href="index.php?page=standings">Standings</a>
				</li>
			</ul>

			<ul class="navbar-nav">
				<?php if ($isLogged): ?>
					<li class="nav-item">
						<span class="nav-link">Hello, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
					</li>

					<li class="nav-item">
						<a class="nav-link" href="index.php?page=logout">Logout</a>
					</li>

				<?php else: ?>
					<li class="nav-item">
						<a class="nav-link" href="index.php?page=login">Login</a>
					</li>

					<li class="nav-item">
						<a class="nav-link" href="index.php?page=register">Register</a>
					</li>
				<?php endif; ?>
			</ul>
		</div>
	</div>
</nav>
