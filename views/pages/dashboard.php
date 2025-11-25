<?php
require_once __DIR__ . '/../../src/helpers/session.php';
require_once __DIR__ . '/../../src/helpers/utils.php';
require_once __DIR__ . '/../../src/db/connection.php';
startSession();

if (!isset($_SESSION['user_id'])) {
	redirect('index.php'); // si no está logueado, vuelve a home
}

$db = getDb();
$totalTournaments = 0;
$res = $db->query('SELECT COUNT(*) AS total FROM tournaments');
if ($res) {
	$row = $res->fetch_assoc();
	$totalTournaments = (int) ($row['total'] ?? 0);
}

// Métricas rápidas
$teamsCount = 0;
$matchesCount = 0;
$r = $db->query('SELECT COUNT(*) AS c FROM teams');
if ($r) { $teamsCount = (int) ($r->fetch_assoc()['c'] ?? 0); }
$r = $db->query('SELECT COUNT(*) AS c FROM matches');
if ($r) { $matchesCount = (int) ($r->fetch_assoc()['c'] ?? 0); }

// Obtener torneos más recientes
$tournaments = [];
$q = $db->query('SELECT * FROM tournaments ORDER BY created_at DESC');
if ($q) {
	$tournaments = $q->fetch_all(MYSQLI_ASSOC);
}

// Player performance summary (basic stats)
$userId = (int) $_SESSION['user_id'];
// Torneos jugados por equipos del usuario
$played = 0;
$stmt = $db->prepare('SELECT COUNT(DISTINCT tt.tournament_id) AS c FROM tournament_teams tt JOIN teams t ON t.id = tt.team_id WHERE t.owner_user_id = ?');
$stmt->bind_param('i', $userId);
$stmt->execute();
$resPlayed = $stmt->get_result()->fetch_assoc();
$played = (int) ($resPlayed['c'] ?? 0);
$stmt->close();

// Torneos ganados (primer lugar por puntos)
$wins = 0;
$stmt = $db->prepare('
	SELECT COUNT(DISTINCT s.tournament_id) AS c
	FROM standings s
	JOIN teams t ON t.id = s.team_id
	WHERE t.owner_user_id = ?
	AND NOT EXISTS (
		SELECT 1 FROM standings s2
		WHERE s2.tournament_id = s.tournament_id AND s2.points > s.points
	)
');
$stmt->bind_param('i', $userId);
$stmt->execute();
$resWins = $stmt->get_result()->fetch_assoc();
$wins = (int) ($resWins['c'] ?? 0);
$stmt->close();

$winRate = $played > 0 ? round(($wins / $played) * 100) : 0;

// Juego más jugado
$mostGame = 'N/A';
$stmt = $db->prepare('
	SELECT COALESCE(tr.game, "pub") AS g, COUNT(*) AS c
	FROM tournament_teams tt
	JOIN teams t ON t.id = tt.team_id
	JOIN tournaments tr ON tr.id = tt.tournament_id
	WHERE t.owner_user_id = ?
	GROUP BY g
	ORDER BY c DESC
	LIMIT 1
');
$stmt->bind_param('i', $userId);
$stmt->execute();
$rg = $stmt->get_result()->fetch_assoc();
if ($rg && isset($rg['g'])) { $mostGame = strtoupper($rg['g']); }
$stmt->close();

// Ranking global basado en suma de puntos
$totalPoints = 0;
$stmt = $db->prepare('SELECT COALESCE(SUM(s.points),0) AS total FROM standings s JOIN teams t ON t.id = s.team_id WHERE t.owner_user_id = ?');
$stmt->bind_param('i', $userId);
$stmt->execute();
$tp = $stmt->get_result()->fetch_assoc();
$totalPoints = (int) ($tp['total'] ?? 0);
$stmt->close();

$pos = 1;
$totalUsers = 1;
// posición: usuarios con total mayor + 1
$stmt = $db->prepare('
	SELECT COUNT(*) + 1 AS pos FROM (
		SELECT t.owner_user_id, COALESCE(SUM(s.points),0) AS total
		FROM teams t
		LEFT JOIN standings s ON s.team_id = t.id
		GROUP BY t.owner_user_id
	) x WHERE x.total > ?
');
$stmt->bind_param('i', $totalPoints);
$stmt->execute();
$rp = $stmt->get_result()->fetch_assoc();
$pos = (int) ($rp['pos'] ?? 1);
$stmt->close();

$ru = $db->query('SELECT COUNT(*) AS n FROM (SELECT owner_user_id FROM teams GROUP BY owner_user_id) u');
if ($ru) { $totalUsers = (int) ($ru->fetch_assoc()['n'] ?? 1); }

// Sparkline: puntos por semana (últimas 8)
$spark = [];
$stmt = $db->prepare('
	SELECT YEARWEEK(s.updated_at, 3) AS yw, COALESCE(SUM(s.points),0) AS total
	FROM standings s
	JOIN teams t ON t.id = s.team_id
	WHERE t.owner_user_id = ?
	GROUP BY YEARWEEK(s.updated_at, 3)
	ORDER BY yw DESC
	LIMIT 8
');
$stmt->bind_param('i', $userId);
$stmt->execute();
$rs = $stmt->get_result();
while ($row = $rs->fetch_assoc()) { $spark[] = (int) $row['total']; }
$stmt->close();
$spark = array_reverse($spark); // cronológico
if (count($spark) === 0) { $spark = [0,0,0,0,0,0,0,0]; }
// Normalizar a 0..100
$maxVal = max(1, max($spark));
$sparkY = array_map(function($v) use ($maxVal) { return 100 - round(($v / $maxVal) * 100); }, $spark);
$pointsAttr = '';
for ($i = 0; $i < count($sparkY); $i++) {
	$x = (int) round(($i / (max(1,count($sparkY)-1))) * 160); // ancho 160
	$y = $sparkY[$i];
	$pointsAttr .= $x . ',' . $y . ' ';
}
?>

<div class="dash-surface rounded-4 p-3 mb-4 position-relative overflow-hidden">
	<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
		<div class="d-flex align-items-center gap-3">
			<span class="pill-tag">Tu panel</span>
			<div class="avatar-stack d-flex">
				<div class="avatar-bubble a1"></div>
				<div class="avatar-bubble a2"></div>
				<div class="avatar-bubble a3"></div>
			</div>
		</div>
		<div class="ms-auto d-flex gap-2">
			<a href="index.php?page=home&showLanding=1" class="btn-hero btn-hero-outline">Volver a página</a>
		</div>
	</div>
	<div class="row g-3 align-items-stretch">
		<div class="col-12 col-lg-8">
			<div class="player-summary">
				<div class="ps-header d-flex align-items-center justify-content-between mb-2">
					<h2 class="text-light fw-bold mb-0">Resumen de rendimiento</h2>
					<span class="pill-tag">Player Performance</span>
				</div>
				<div class="ps-meta text-muted mb-2">
					Juegos más jugados: <strong><?php echo htmlspecialchars($mostGame); ?></strong> · Ranking global:
					<strong>#<?php echo (int) $pos; ?>/<?php echo (int) $totalUsers; ?></strong>
				</div>
				<div class="ps-stats">
					<div class="ps-stat">
						<div class="num"><?php echo (int) $played; ?></div>
						<div class="label">Torneos jugados</div>
					</div>
					<div class="ps-stat">
						<div class="num"><?php echo (int) $wins; ?></div>
						<div class="label">Torneos ganados</div>
					</div>
					<div class="ps-stat">
						<div class="num"><?php echo (int) $winRate; ?>%</div>
						<div class="label">Tasa de victoria</div>
					</div>
					<div class="ps-stat">
						<div class="num"><?php echo (int) $totalPoints; ?></div>
						<div class="label">Puntos totales</div>
					</div>
				</div>
				<div class="sparkline-wrap mt-2">
					<svg class="sparkline" width="180" height="48" viewBox="0 0 160 100" preserveAspectRatio="none" aria-hidden="true">
						<polyline fill="none" stroke="url(#gradLine)" stroke-width="3" points="<?php echo trim($pointsAttr); ?>" />
						<defs>
							<linearGradient id="gradLine" x1="0" y1="0" x2="1" y2="0">
								<stop offset="0%" stop-color="#7a3cff"/>
								<stop offset="100%" stop-color="#00d0ff"/>
							</linearGradient>
						</defs>
					</svg>
					<div class="small text-muted">Evolución semanal de puntos</div>
				</div>
			</div>
		</div>
		<div class="col-12 col-lg-4">
			<div class="dash-stats h-100">
				<div class="stat-box">
					<div class="num"><?php echo (int) $totalTournaments; ?></div>
					<div class="label">Torneos</div>
				</div>
				<div class="stat-box">
					<div class="num"><?php echo (int) $teamsCount; ?></div>
					<div class="label">Equipos</div>
				</div>
				<div class="stat-box">
					<div class="num"><?php echo (int) $matchesCount; ?></div>
					<div class="label">Partidas</div>
				</div>
			</div>
			<div class="d-flex gap-2 mt-3">
				<a href="index.php?page=tournaments" class="btn-hero btn-hero-primary flex-fill text-center">Torneos</a>
				<a href="index.php?page=standings" class="btn-hero btn-hero-outline flex-fill text-center">Leaderboards</a>
			</div>
		</div>
	</div>
</div>

<h3 class="text-light fw-semibold mb-2">Torneos organizados</h3>
<div class="dashboard-grid">
<?php if (!$tournaments): ?>
	<div class="alert alert-info">No tournaments yet.</div>
<?php else: ?>
	<?php foreach ($tournaments as $t): ?>
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
		?>
		<div class="tournament-card card">
			<img src="<?php echo $img; ?>" alt="Game">
			<div class="card-body">
				<h5 class="card-title"><?php echo sanitize($t['name'] ?? 'Tournament'); ?></h5>
				<p class="card-date mb-2">
					<?php echo sanitize($t['created_at'] ?? ''); ?>
				</p>
				<div class="d-flex gap-2">
					<a href="index.php?page=tournament_view&id=<?php echo (int) ($t['id'] ?? 0); ?>" class="btn btn-outline-primary btn-sm">Ver</a>
					<a href="index.php?page=tournaments_join&id=<?php echo (int) ($t['id'] ?? 0); ?>" class="btn btn-primary btn-sm">Unirse</a>
				</div>
			</div>
		</div>
	<?php endforeach; ?>
<?php endif; ?>
</div>


