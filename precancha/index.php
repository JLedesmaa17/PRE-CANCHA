<?php
/**
 * ============================================
 * PRE-CANCHA - Página Principal (Landing)
 * index.php
 * ============================================
 */

require_once 'config/database.php';
require_once 'includes/auth.php';

$db = getDB();

// Verificar si hay mensaje de logout
$showLogoutMessage = isset($_GET['logout']) && $_GET['logout'] === 'success';

// Obtener canchas disponibles
$sql = "SELECT * FROM canchas WHERE estado = 'disponible' ORDER BY numero ASC";
$canchas = $db->fetchAll($sql);

// Usuario logueado
$user = currentUser();
$isLoggedIn = isLoggedIn();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PRE-CANCHA - Sistema de Reservas de Canchas de Fútbol</title>
    <meta name="description" content="Reserva tu cancha de fútbol online. Sistema digital de gestión y reservas en tiempo real.">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --color-primary: #DAA520;
            --color-secondary: #FFD700;
            --color-bg-dark: #000000;
            --color-bg-light: #1a1a1a;
            --color-text: #ffffff;
            --color-text-muted: #cccccc;
            --color-success: #00ff00;
            --color-danger: #ff4444;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--color-bg-dark) 0%, var(--color-bg-light) 100%);
            color: var(--color-text);
            overflow-x: hidden;
            min-height: 100vh;
        }

        header {
            background: rgba(0, 0, 0, 0.95);
            padding: 20px 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(218, 165, 32, 0.3);
            backdrop-filter: blur(10px);
        }

        nav {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }

        .logo {
            font-size: 32px;
            font-weight: bold;
            background: linear-gradient(90deg, var(--color-primary), var(--color-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            cursor: pointer;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 30px;
            align-items: center;
        }

        .nav-links a {
            color: var(--color-primary);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .nav-links a:hover {
            color: var(--color-secondary);
        }

        .btn-header {
            padding: 10px 20px;
            border-radius: 8px;
            border: 2px solid var(--color-primary);
            background: transparent;
            color: var(--color-primary);
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-header:hover {
            background: var(--color-primary);
            color: var(--color-bg-dark);
        }

        .btn-header.primary {
            background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
            color: var(--color-bg-dark);
            border: none;
        }

        .alert {
            max-width: 600px;
            margin: 100px auto 20px;
            padding: 15px 25px;
            border-radius: 10px;
            text-align: center;
            animation: slideDown 0.5s ease-out;
        }

        .alert-success {
            background: rgba(0, 255, 0, 0.2);
            border: 1px solid var(--color-success);
            color: var(--color-success);
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero {
            margin-top: 80px;
            padding: 120px 20px 80px;
            text-align: center;
            background: radial-gradient(ellipse at center, rgba(218, 165, 32, 0.1) 0%, transparent 70%);
        }

        .hero h1 {
            font-size: 72px;
            margin-bottom: 25px;
            background: linear-gradient(90deg, var(--color-primary), var(--color-secondary));
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: shimmer 3s infinite linear;
        }

        @keyframes shimmer {
            0% { background-position: 0% 50%; }
            100% { background-position: 200% 50%; }
        }

        .hero .subtitle {
            font-size: 28px;
            color: var(--color-text-muted);
            margin-bottom: 20px;
        }

        .hero p {
            font-size: 18px;
            color: var(--color-text-muted);
            margin-bottom: 50px;
            line-height: 1.8;
        }

        .cta-container {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .cta-button {
            background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
            color: var(--color-bg-dark);
            padding: 20px 50px;
            border: none;
            border-radius: 50px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 10px 30px rgba(218, 165, 32, 0.4);
            text-decoration: none;
            display: inline-block;
        }

        .cta-button:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(255, 215, 0, 0.6);
        }

        .stats-section {
            padding: 60px 20px;
            background: rgba(218, 165, 32, 0.05);
            border-top: 1px solid rgba(218, 165, 32, 0.2);
        }

        .stats-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            text-align: center;
        }

        .stat-number {
            font-size: 48px;
            font-weight: bold;
            background: linear-gradient(90deg, var(--color-primary), var(--color-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-label {
            font-size: 16px;
            color: var(--color-text-muted);
            text-transform: uppercase;
            margin-top: 10px;
        }

        .canchas-section {
            padding: 100px 20px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .section-title {
            text-align: center;
            font-size: 48px;
            margin-bottom: 20px;
            background: linear-gradient(90deg, var(--color-primary), var(--color-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .section-description {
            text-align: center;
            font-size: 18px;
            color: var(--color-text-muted);
            margin-bottom: 60px;
        }

        .canchas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 30px;
            padding: 20px;
        }

        .cancha-card {
            background: linear-gradient(145deg, var(--color-bg-light), #0d0d0d);
            border: 2px solid var(--color-primary);
            border-radius: 20px;
            padding: 35px;
            transition: all 0.4s;
            cursor: pointer;
            position: relative;
        }

        .cancha-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 60px rgba(218, 165, 32, 0.5);
            border-color: var(--color-secondary);
        }

        .cancha-number {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
            color: var(--color-bg-dark);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: bold;
        }

        .cancha-icon {
            font-size: 80px;
            text-align: center;
            margin: 20px 0;
        }

        .cancha-type {
            font-size: 28px;
            font-weight: bold;
            color: var(--color-secondary);
            text-align: center;
            margin-bottom: 15px;
        }

        .cancha-info {
            color: var(--color-text-muted);
            margin: 12px 0;
            font-size: 16px;
        }

        .btn-reservar {
            width: 100%;
            margin-top: 20px;
            padding: 15px;
            background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
            color: var(--color-bg-dark);
            border: none;
            border-radius: 15px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: block;
        }

        .btn-reservar:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(218, 165, 32, 0.5);
        }

        footer {
            background: var(--color-bg-dark);
            padding: 40px 20px;
            text-align: center;
            border-top: 2px solid var(--color-primary);
            margin-top: 80px;
        }

        footer p {
            color: var(--color-primary);
            margin: 10px 0;
        }

        @media (max-width: 768px) {
            .hero h1 {
                font-size: 48px;
            }

            .nav-links {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">⚽ PRE-CANCHA</div>
            <ul class="nav-links">
                <li><a href="#inicio">Inicio</a></li>
                <li><a href="#canchas">Canchas</a></li>
                <?php if ($isLoggedIn): ?>
                    <?php if (isAdmin()): ?>
                        <li><a href="admin/panel.php" class="btn-header">📊 Panel Admin</a></li>
                    <?php else: ?>
                        <li><a href="usuario/mis-reservas.php" class="btn-header">📅 Mis Reservas</a></li>
                    <?php endif; ?>
                    <li>
                        <form action="logout.php" method="POST" style="display:inline;">
                            <button type="submit" class="btn-header">Cerrar Sesión</button>
                        </form>
                    </li>
                <?php else: ?>
                    <li><a href="login.php" class="btn-header">Iniciar Sesión</a></li>
                    <li><a href="registro.php" class="btn-header primary">Registrarse</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <?php if ($showLogoutMessage): ?>
        <div class="alert alert-success">
            ✅ Sesión cerrada exitosamente. ¡Hasta pronto!
        </div>
    <?php endif; ?>

    <section class="hero" id="inicio">
        <h1>PRE-CANCHA</h1>
        <p class="subtitle">Sistema Digital de Gestión y Reserva</p>
        <p>
            Reserva tu cancha de fútbol de forma rápida, fácil y segura.<br>
            Disponibilidad en tiempo real, confirmación instantánea y la mejor experiencia para tu partido.
        </p>
        <div class="cta-container">
            <?php if ($isLoggedIn): ?>
                <a href="reservas.php" class="cta-button">
                    📅 Hacer una Reserva
                </a>
                <a href="usuario/mis-reservas.php" class="cta-button" style="background: transparent; border: 2px solid var(--color-primary); color: var(--color-primary);">
                    📋 Ver Mis Reservas
                </a>
            <?php else: ?>
                <a href="registro.php" class="cta-button">
                    🎯 Reservar Ahora
                </a>
                <a href="login.php" class="cta-button" style="background: transparent; border: 2px solid var(--color-primary); color: var(--color-primary);">
                    🔐 Iniciar Sesión
                </a>
            <?php endif; ?>
        </div>
    </section>

    <section class="stats-section">
        <div class="stats-container">
            <div class="stat-item">
                <div class="stat-number"><?= count($canchas) ?></div>
                <div class="stat-label">Canchas Disponibles</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">24/7</div>
                <div class="stat-label">Atención Online</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">100%</div>
                <div class="stat-label">Reserva Garantizada</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">⚡</div>
                <div class="stat-label">Confirmación Instantánea</div>
            </div>
        </div>
    </section>

    <section class="canchas-section" id="canchas">
        <h2 class="section-title">Nuestras Canchas</h2>
        <p class="section-description">
            Contamos con canchas de última generación con césped sintético de alta calidad,
            iluminación LED y vestuarios completos para tu comodidad.
        </p>
        <div class="canchas-grid">
            <?php foreach ($canchas as $cancha): ?>
                <div class="cancha-card">
                    <div class="cancha-number"><?= $cancha['numero'] ?></div>
                    <div class="cancha-icon">⚽</div>
                    <div class="cancha-type"><?= htmlspecialchars($cancha['tipo']) ?></div>
                    <div class="cancha-info">
                        👥 <?= $cancha['jugadores'] ?> jugadores (<?= $cancha['jugadores']/2 ?> vs <?= $cancha['jugadores']/2 ?>)
                    </div>
                    <div class="cancha-info">
                        🌱 <?= htmlspecialchars($cancha['superficie']) ?>
                    </div>
                    <?php if ($cancha['iluminacion']): ?>
                        <div class="cancha-info">💡 Iluminación LED Profesional</div>
                    <?php endif; ?>
                    <?php if ($cancha['vestuarios']): ?>
                        <div class="cancha-info">🚿 Vestuarios con duchas</div>
                    <?php endif; ?>
                    <div class="cancha-info" style="margin-top: 15px; font-size: 24px; font-weight: bold; color: var(--color-secondary);">
                        $<?= number_format($cancha['precio_hora'], 0, ',', '.') ?> /hora
                    </div>
                    <?php if ($isLoggedIn): ?>
                        <a href="reservas.php?cancha=<?= $cancha['id'] ?>" class="btn-reservar">
                            📅 Reservar Turno
                        </a>
                    <?php else: ?>
                        <a href="registro.php" class="btn-reservar">
                            🔐 Regístrate para Reservar
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <footer>
        <p><strong>⚽ PRE-CANCHA</strong></p>
        <p>Sistema de Gestión de Reservas de Canchas de Fútbol</p>
        <p style="margin-top: 20px;">📧 info@precancha.com | 📞 +54 11 1234-5678</p>
        <p style="margin-top: 10px;">© 2024 PRE-CANCHA - Todos los derechos reservados</p>
    </footer>
</body>
</html>