<?php
require_once __DIR__ . '/../../src/helpers/session.php';
require_once __DIR__ . '/../../src/helpers/utils.php';
ensureLoggedIn();
?>

<section class="marketplace-section rounded-4 p-3 mb-4">
	<div class="market-header mb-3">
		<h2 class="mb-0">Marketplace Gamer</h2>
		<div class="market-filters">
			<span class="chip">Popular</span>
			<span class="chip">Shooter</span>
			<span class="chip">Sports</span>
			<span class="chip">Adventure</span>
			<span class="chip">RPG</span>
		</div>
	</div>

	<div class="market-layout">
		<div class="market-main">
			<div class="market-grid">
				<!-- COD -->
				<div class="market-card">
					<img class="market-thumb" src="public/img/cod.jpg" alt="COD">
					<div class="market-body">
						<h5 class="market-title">Call of Duty: Warzone</h5>
						<div class="market-meta">
							<div>FPS • Multiplayer</div>
							<div class="badge-green">98% rating</div>
						</div>
						<div class="market-actions">
							<div class="chip">PC • PS • Xbox</div>
							<div class="price">$29.99</div>
						</div>
					</div>
				</div>
				<!-- Rocket League -->
				<div class="market-card">
					<img class="market-thumb" src="public/img/rocketleague.jpg" alt="Rocket League">
					<div class="market-body">
						<h5 class="market-title">Rocket League</h5>
						<div class="market-meta">
							<div>Sports • Cars</div>
							<div class="badge-green">90% rating</div>
						</div>
						<div class="market-actions">
							<div class="chip">PC • PS • Xbox • Switch</div>
							<div class="price">$19.99</div>
						</div>
					</div>
				</div>
				<!-- Apex -->
				<div class="market-card">
					<img class="market-thumb" src="public/img/apex.jpg" alt="Apex">
					<div class="market-body">
						<h5 class="market-title">Apex Legends</h5>
						<div class="market-meta">
							<div>Battle Royale</div>
							<div class="badge-green">92% rating</div>
						</div>
						<div class="market-actions">
							<div class="chip">PC • PS • Xbox • Switch</div>
							<div class="price">Free</div>
						</div>
					</div>
				</div>
				<!-- PUBG -->
				<div class="market-card">
					<img class="market-thumb" src="public/img/pub.jpg" alt="PUBG">
					<div class="market-body">
						<h5 class="market-title">PUBG: Battlegrounds</h5>
						<div class="market-meta">
							<div>Battle Royale</div>
							<div class="badge-green">88% rating</div>
						</div>
						<div class="market-actions">
							<div class="chip">PC • PS • Xbox</div>
							<div class="price">$14.99</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<aside class="market-aside">
			<h5 class="mb-2">Top más vendidos</h5>
			<ol class="top-list mb-3">
				<li class="top-item">
					<img src="public/img/cod.jpg" alt="" class="top-thumb">
					<div class="top-info">
						<div class="title">Call of Duty: Warzone</div>
						<div class="meta">FPS • 98% rating</div>
					</div>
					<div class="price">$29.99</div>
				</li>
				<li class="top-item">
					<img src="public/img/rocketleague.jpg" alt="" class="top-thumb">
					<div class="top-info">
						<div class="title">Rocket League</div>
						<div class="meta">Sports • 90% rating</div>
					</div>
					<div class="price">$19.99</div>
				</li>
				<li class="top-item">
					<img src="public/img/apex.jpg" alt="" class="top-thumb">
					<div class="top-info">
						<div class="title">Apex Legends</div>
						<div class="meta">BR • 92% rating</div>
					</div>
					<div class="price">Free</div>
				</li>
				<li class="top-item">
					<img src="public/img/pub.jpg" alt="" class="top-thumb">
					<div class="top-info">
						<div class="title">PUBG: Battlegrounds</div>
						<div class="meta">BR • 88% rating</div>
					</div>
					<div class="price">$14.99</div>
				</li>
			</ol>

			<div class="market-banner">
				<div class="mb-2">Ofertas de la semana</div>
				<div class="banner-price">$9.99</div>
				<div class="small text-muted">Ahorra hasta 70% en títulos seleccionados.</div>
			</div>
		</aside>
	</div>
</section>


