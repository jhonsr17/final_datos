<?php
require_once __DIR__ . '/../../src/helpers/session.php';
require_once __DIR__ . '/../../src/helpers/utils.php';
startSession();

// Si el usuario está logueado, no mostrar la landing salvo que explícitamente la pida
if (isset($_SESSION['user_id']) && !isset($_GET['showLanding'])) {
	redirect('index.php?page=dashboard');
}
?>
<section class="hero-gamer rounded-4 shadow-lg position-relative overflow-hidden">
	<div class="hero-gradient"></div>
	<div class="row align-items-center g-4">
		<div class="col-12 col-lg-6">
			<h1 class="hero-title fw-bold mb-3">Compite. Gana. Domina <span class="brand-glow">Thor‑Nament</span>.</h1>
			<p class="hero-subtitle mb-4">
				Organiza tus torneos de esports, reta a otros equipos y escala posiciones.
				Todo en una sola plataforma, rápida y sencilla.
			</p>
			<div class="d-flex flex-wrap gap-3 hero-actions">
				<a href="index.php?page=tournaments_create" class="btn-hero btn-hero-primary">
					<span class="icon">⚡</span> Create Tournament
				</a>
				<a href="index.php?page=standings" class="btn-hero btn-hero-outline">
					View Leaderboards
				</a>
			</div>
		</div>
		<div class="col-12 col-lg-6">
			<div class="hero-visual mx-auto" id="heroMockup" aria-hidden="true">
				<div class="mockup-screen rounded-4">
					<div class="mockup-header d-flex align-items-center justify-content-between px-3">
						<div class="dots d-flex gap-1">
							<span></span><span></span><span></span>
						</div>
						<div class="mockup-title">Live Tournament</div>
						<div class="badge badge-live">LIVE</div>
					</div>
					<div class="mockup-body p-3">
						<div class="mockup-grid">
							<div class="mock-card">
								<div class="team">VALKYRIE</div>
								<div class="score">73</div>
							</div>
							<div class="mock-card">
								<div class="team">NOVA</div>
								<div class="score">69</div>
							</div>
							<div class="mock-card">
								<div class="team">PHANTOM</div>
								<div class="score">61</div>
							</div>
							<div class="mock-card">
								<div class="team">ONYX</div>
								<div class="score">58</div>
							</div>
						</div>
						<div class="mockup-footer mt-3">
							<div class="pill">Round 3/5</div>
							<div class="pill">Battle Royale</div>
							<div class="pill pill-primary">+20 kills</div>
						</div>
					</div>
				</div>

				<!-- Floating avatars -->
				<div class="avatar-float a1"></div>
				<div class="avatar-float a2"></div>
				<div class="avatar-float a3"></div>
			</div>
		</div>
	</div>
</section>

<!-- MARKETPLACE (GAMER) -->
<section class="marketplace-section rounded-4 mt-5 p-3">
	<div class="market-header mb-3">
		<h3 class="mb-0">Marketplace Gamer</h3>
		<div class="market-filters">
			<span class="chip">Popular</span>
			<span class="chip">Shooter</span>
			<span class="chip">Sports</span>
			<span class="chip">Adventure</span>
		</div>
	</div>
	<div class="market-grid">
		<!-- Card COD -->
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
		<!-- Card Rocket League -->
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
		<!-- Card Apex -->
		<div class="market-card">
			<img class="market-thumb" src="public/img/apex.jpg" alt="Apex Legends">
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
		<!-- Card PUBG -->
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
</section>

<?php if (isset($_SESSION['user_id']) && isset($_GET['newsfeed'])): ?>
<?php
require_once __DIR__ . '/../../src/helpers/utils.php';
require_once __DIR__ . '/../../src/db/connection.php';
$db = getDb();
$uid = (int) $_SESSION['user_id'];
$stmt = $db->prepare('
	SELECT t.*,
		(SELECT COUNT(*) FROM tournament_teams tt WHERE tt.tournament_id = t.id) AS team_count,
		EXISTS(SELECT 1 FROM matches m WHERE m.tournament_id = t.id) AS has_matches,
		EXISTS(SELECT 1 FROM standings s WHERE s.tournament_id = t.id AND s.points > 0) AS has_standings
	FROM tournaments t
	WHERE t.created_by = ?
		OR EXISTS (
			SELECT 1 FROM tournament_teams tt
			JOIN teams tm ON tm.id = tt.team_id
			WHERE tt.tournament_id = t.id AND tm.owner_user_id = ?
		)
	ORDER BY t.created_at DESC
');
$stmt->bind_param('ii', $uid, $uid);
$stmt->execute();
$res = $stmt->get_result();
$myTournaments = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();
?>

<section class="dashboard-newsfeed rounded-4 mt-4 p-3">
	<div class="dnf-layout">
		<aside class="dnf-sidebar">
			<div class="dnf-sideitem"><div class="dnf-icon">📰</div> <div>Newsfeed</div></div>
			<div class="dnf-sideitem"><div class="dnf-icon">🏆</div> <div>Torneos</div></div>
			<div class="dnf-sideitem"><div class="dnf-icon">👥</div> <div>Equipos</div></div>
			<div class="dnf-sideitem"><div class="dnf-icon">📈</div> <div>Resultados</div></div>
			<div class="dnf-sideitem"><div class="dnf-icon">👤</div> <div>Perfil</div></div>
		</aside>
		<div class="dnf-content">
			<h3 class="dnf-title fw-bold mb-3">🏆 Mis Torneos Activos</h3>
			<div class="dnf-grid">
				<?php foreach ($myTournaments as $t): ?>
					<?php
					$gameSlug = strtolower($t['game'] ?? '');
					$map = [
						'apex' => 'public/img/apex.jpg',
						'cod' => 'public/img/cod.jpg',
						'pubg' => 'public/img/pubg.jpg',
						'pub' => 'public/img/pub.jpg',
						'rocketleague' => 'public/img/rocketleague.jpg',
					];
					$img = isset($map[$gameSlug]) ? $map[$gameSlug] : 'public/img/pub.jpg';
					$type = $t['game_type'] === 'VS' ? 'Versus' : 'Battle Royale';
					$teamCount = (int) ($t['team_count'] ?? 0);
					$hasMatches = (int) ($t['has_matches'] ?? 0);
					$hasStandings = (int) ($t['has_standings'] ?? 0);
					$status = 'Inscripciones abiertas';
					if ($hasStandings) { $status = 'Finalizado'; }
					else if ($hasMatches) { $status = 'En curso'; }
					?>
					<div class="dnf-card">
						<img src="<?php echo $img; ?>" alt="Game">
						<div class="body">
							<div class="mb-1">
								<span class="badge-type"><?php echo sanitize($type); ?></span>
								<span class="badge-type"><?php echo (int) $teamCount; ?> equipos</span>
							</div>
							<h5 class="mb-1"><?php echo sanitize($t['name']); ?></h5>
							<div class="status mb-2">Estado: <?php echo $status; ?></div>
							<div class="mini-chart mb-2"></div>
							<div class="dnf-actions">
								<a class="btn-gamer-outline" href="index.php?page=standings&tournament_id=<?php echo (int) $t['id']; ?>">Ver standings</a>
								<a class="btn-gamer-outline" href="index.php?page=matches_create&tournament_id=<?php echo (int) $t['id']; ?>">Añadir resultados</a>
								<a class="btn-gamer" href="index.php?page=tournaments_edit&id=<?php echo (int) $t['id']; ?>">Editar torneo</a>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
				<?php if (!$myTournaments): ?>
					<div class="alert alert-info mb-0">Aún no tienes torneos activos. Crea uno ahora desde “Create Tournament”.</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
<?php endif; ?>

<!-- FEATURES -->
<section id="features" class="features-section rounded-4 shadow-lg position-relative overflow-hidden mt-5">
	<div class="features-gradient"></div>

	<div class="text-center mb-4">
		<span class="badge features-badge">FEATURES</span>
		<h2 class="features-title fw-bold mt-2">Thor‑Nament tiene las funciones que te encantarán</h2>
	</div>

	<div class="features-grid">
		<!-- Card 1 -->
		<div class="feature-card is-heart">
			<div class="feature-icon">
				<span class="letter">A</span>
				<span class="suit">♥</span>
			</div>
			<h5 class="feature-heading">Vibras de Esports Auténticas</h5>
			<p class="feature-text">Partidas, rankings y una experiencia competitiva diseñada para jugadores de esports.</p>
		</div>

		<!-- Card 2 -->
		<div class="feature-card is-club">
			<div class="feature-icon">
				<span class="letter">K</span>
				<span class="suit">♣</span>
			</div>
			<h5 class="feature-heading">Seguridad y Transparencia</h5>
			<p class="feature-text">Competencia justa impulsada por tablas transparentes y seguimiento de partidas.</p>
		</div>

		<!-- Card 3 -->
		<div class="feature-card is-spade">
			<div class="feature-icon">
				<span class="letter">Q</span>
				<span class="suit">♠</span>
			</div>
			<h5 class="feature-heading">Plataforma de Torneos Next‑Gen</h5>
			<p class="feature-text">Herramientas modernas para crear, gestionar y dominar torneos fácilmente.</p>
		</div>

		<!-- Card 4 -->
		<div class="feature-card is-diamond">
			<div class="feature-icon">
				<span class="letter">J</span>
				<span class="suit">♦</span>
			</div>
			<h5 class="feature-heading">Juega y Gana</h5>
			<p class="feature-text">Recompensas y futura progresión para jugadores y equipos.</p>
		</div>
	</div>
</section>

<!-- TOURNAMENT PREVIEW -->
<section class="tournament-preview-section rounded-4 position-relative overflow-hidden mt-5" data-reveal>
	<div class="tournament-gradient"></div>

	<div class="row g-4 align-items-center">
		<div class="col-12 col-lg-6">
			<div class="preview-header d-flex align-items-center gap-3 mb-3">
				<span class="pill-tag">Torneos Thor‑Nament</span>
				<div class="avatar-stack d-flex">
					<div class="avatar-bubble a1"></div>
					<div class="avatar-bubble a2"></div>
					<div class="avatar-bubble a3"></div>
					<div class="avatar-bubble a4"></div>
				</div>
			</div>
			<h2 class="preview-title fw-bold mb-2">Juega, Compite y Triunfa: Torneos en Thor‑Nament</h2>
			<p class="preview-subtitle mb-4">Crea torneos en segundos, reta a tus amigos y escala en los rankings.
				Una experiencia rápida, segura y con estilo gamer.</p>
			<div class="preview-buttons d-flex flex-wrap gap-3">
				<a href="<?php echo isset($_SESSION['user_id']) ? 'index.php?page=dashboard' : 'index.php?page=register'; ?>" class="btn-hero btn-hero-primary">
					Unirte y Crear
				</a>
				<a href="#features" class="btn-hero btn-hero-outline">Conocer más</a>
			</div>
		</div>
		<div class="col-12 col-lg-6">
			<div class="dashboard-mockup rounded-4">
				<div class="mockui">
					<div class="mockui-header">
						<div class="mh-dots"><span></span><span></span><span></span></div>
						<div class="mh-title">Panel de control</div>
						<div class="mh-user"></div>
					</div>
					<div class="mockui-body">
						<aside class="mockui-sidebar">
							<div class="mi active" title="Inicio">🏠</div>
							<div class="mi" title="Torneos">🏆</div>
							<div class="mi" title="Equipos">👥</div>
							<div class="mi" title="Estadísticas">📈</div>
						</aside>
						<main class="mockui-main">
							<div class="mockui-cards">
								<div class="mockui-card">
									<div class="m-banner m-pubg">PUBG</div>
									<h6 class="m-title">Open Cup — Red Bull</h6>
									<div class="m-meta"><span>128 jugadores</span><span>Hoy 20:00</span></div>
									<button class="m-btn">Unirse</button>
								</div>
								<div class="mockui-card">
									<div class="m-banner m-apex">APEX</div>
									<h6 class="m-title">Legends Clash</h6>
									<div class="m-meta"><span>64 equipos</span><span>Sábado</span></div>
									<button class="m-btn">Unirse</button>
								</div>
								<div class="mockui-card">
									<div class="m-banner m-cod">COD</div>
									<h6 class="m-title">Warzone Night</h6>
									<div class="m-meta"><span>96 jugadores</span><span>Domingo</span></div>
									<button class="m-btn">Unirse</button>
								</div>
							</div>
							<div class="mockui-stats">
								<div class="stat-box">
									<div class="num">12</div>
									<div class="label">Torneos activos</div>
								</div>
								<div class="stat-box">
									<div class="num">482</div>
									<div class="label">Participantes</div>
								</div>
								<div class="stat-box">
									<div class="num">34</div>
									<div class="label">Equipos</div>
								</div>
							</div>
						</main>
					</div>
			</div>
		</div>
	</div>
</div>
</section>

