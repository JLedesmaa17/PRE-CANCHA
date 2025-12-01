<?php
/**
 * ============================================
 * PRE-CANCHA - Mis Reservas (Usuario)
 * usuario/mis-reservas.php
 * ============================================
 */

require_once '../config/database.php';
require_once '../includes/auth.php';

// Verificar autenticación
requireLogin();

$db = getDB();
$user = currentUser();

// Obtener reservas del usuario
$sql = "SELECT r.*, c.numero as cancha_numero, c.tipo as cancha_tipo, c.superficie
        FROM reservas r
        INNER JOIN canchas c ON r.cancha_id = c.id
        WHERE r.usuario_id = ?
        ORDER BY r.fecha_reserva DESC, r.hora_inicio DESC";

$reservas = $db->fetchAll($sql, [$user['id']]);

// Filtrar por estado
$pendientes = array_filter($reservas, fn($r) => $r['estado'] === 'pendiente');
$confirmadas = array_filter($reservas, fn($r) => $r['estado'] === 'confirmada');
$rechazadas = array_filter($reservas, fn($r) => $r['estado'] === 'rechazada');
$canceladas = array_filter($reservas, fn($r) => $r['estado'] === 'cancelada');

// Mensaje de bienvenida si es registro reciente
$showWelcomeMessage = isset($_GET['registered']) && $_GET['registered'] === 'true';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Reservas - PRE-CANCHA</title>
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
            --color-warning: #ffaa00;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--color-bg-dark) 0%, var(--color-bg-light) 100%);
            color: var(--color-text);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .header {
            background: linear-gradient(145deg, var(--color-bg-light), #0d0d0d);
            border: 2px solid var(--color-primary);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .header h1 {
            font-size: 32px;
            color: var(--color-secondary);
        }

        .header-actions {
            display: flex;
            gap: 15px;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 10px;
            border: none;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
            color: var(--color-bg-dark);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: var(--color-text);
            border: 2px solid var(--color-primary);
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(218, 165, 32, 0.5);
        }

        .alert {
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
        }

        .alert-success {
            background: rgba(0, 255, 0, 0.2);
            border: 2px solid var(--color-success);
            color: var(--color-success);
        }

        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .tab {
            padding: 15px 25px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(218, 165, 32, 0.3);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
        }

        .tab.active {
            background: rgba(218, 165, 32, 0.2);
            border-color: var(--color-secondary);
            color: var(--color-secondary);
        }

        .tab:hover {
            border-color: var(--color-secondary);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .reservas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }

        .reserva-card {
            background: linear-gradient(145deg, var(--color-bg-light), #0d0d0d);
            border: 2px solid var(--color-primary);
            border-radius: 15px;
            padding: 25px;
            transition: all 0.3s;
        }

        .reserva-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(218, 165, 32, 0.3);
        }

        .reserva-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(218, 165, 32, 0.2);
        }

        .reserva-cancha {
            font-size: 20px;
            font-weight: bold;
            color: var(--color-secondary);
        }

        .badge {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
        }

        .badge-pendiente {
            background: rgba(255, 170, 0, 0.2);
            color: var(--color-warning);
            border: 1px solid var(--color-warning);
        }

        .badge-confirmada {
            background: rgba(0, 255, 0, 0.2);
            color: var(--color-success);
            border: 1px solid var(--color-success);
        }

        .badge-rechazada {
            background: rgba(255, 68, 68, 0.2);
            color: var(--color-danger);
            border: 1px solid var(--color-danger);
        }

        .badge-cancelada {
            background: rgba(255, 255, 255, 0.1);
            color: var(--color-text-muted);
            border: 1px solid var(--color-text-muted);
        }

        .reserva-info {
            margin: 12px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .reserva-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid rgba(218, 165, 32, 0.2);
        }

        .btn-sm {
            padding: 8px 16px;
            font-size: 14px;
        }

        .btn-danger {
            background: var(--color-danger);
            color: white;
        }

        .btn-info {
            background: rgba(77, 166, 255, 0.3);
            color: #4da6ff;
            border: 1px solid #4da6ff;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: linear-gradient(145deg, var(--color-bg-light), #0d0d0d);
            border: 2px solid rgba(218, 165, 32, 0.3);
            border-radius: 15px;
        }

        .empty-state-icon {
            font-size: 80px;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .empty-state p {
            color: var(--color-text-muted);
            font-size: 18px;
        }

        .motivo-rechazo {
            background: rgba(255, 68, 68, 0.1);
            padding: 12px;
            border-radius: 8px;
            margin: 12px 0;
            border-left: 3px solid var(--color-danger);
        }

        .horarios-sugeridos {
            background: rgba(0, 255, 0, 0.1);
            padding: 12px;
            border-radius: 8px;
            margin: 12px 0;
            border-left: 3px solid var(--color-success);
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                text-align: center;
            }

            .reservas-grid {
                grid-template-columns: 1fr;
            }

            .tabs {
                flex-direction: column;
            }

            .tab {
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>📅 Mis Reservas</h1>
                <p style="color: var(--color-text-muted); margin-top: 10px;">
                    Bienvenido, <strong><?= htmlspecialchars($user['nombre']) ?></strong>
                </p>
            </div>
            <div class="header-actions">
                <a href="../reservas.php" class="btn btn-primary">
                    ➕ Nueva Reserva
                </a>
                <a href="../index.php" class="btn btn-secondary">
                    🏠 Inicio
                </a>
            </div>
        </div>

        <?php if ($showWelcomeMessage): ?>
            <div class="alert alert-success">
                🎉 ¡Bienvenido a PRE-CANCHA! Tu cuenta ha sido creada exitosamente. 
                Ya puedes empezar a reservar tus canchas.
            </div>
        <?php endif; ?>

        <!-- Tabs -->
        <div class="tabs">
            <div class="tab active" onclick="showTab('todas')">
                📋 Todas (<?= count($reservas) ?>)
            </div>
            <div class="tab" onclick="showTab('pendientes')">
                ⏳ Pendientes (<?= count($pendientes) ?>)
            </div>
            <div class="tab" onclick="showTab('confirmadas')">
                ✅ Confirmadas (<?= count($confirmadas) ?>)
            </div>
            <div class="tab" onclick="showTab('rechazadas')">
                ❌ Rechazadas (<?= count($rechazadas) ?>)
            </div>
            <div class="tab" onclick="showTab('canceladas')">
                🚫 Canceladas (<?= count($canceladas) ?>)
            </div>
        </div>

        <!-- Tab Content: Todas -->
        <div class="tab-content active" id="tab-todas">
            <?php if (empty($reservas)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <p>No tienes reservas todavía</p>
                    <p style="margin-top: 20px;">
                        <a href="../reservas.php" class="btn btn-primary">
                            Hacer mi primera reserva
                        </a>
                    </p>
                </div>
            <?php else: ?>
                <div class="reservas-grid">
                    <?php foreach ($reservas as $reserva): ?>
                        <?php include 'reserva_card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tab Content: Pendientes -->
        <div class="tab-content" id="tab-pendientes">
            <?php if (empty($pendientes)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">✅</div>
                    <p>No tienes reservas pendientes</p>
                </div>
            <?php else: ?>
                <div class="reservas-grid">
                    <?php foreach ($pendientes as $reserva): ?>
                        <?php include 'reserva_card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tab Content: Confirmadas -->
        <div class="tab-content" id="tab-confirmadas">
            <?php if (empty($confirmadas)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📅</div>
                    <p>No tienes reservas confirmadas</p>
                </div>
            <?php else: ?>
                <div class="reservas-grid">
                    <?php foreach ($confirmadas as $reserva): ?>
                        <?php include 'reserva_card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tab Content: Rechazadas -->
        <div class="tab-content" id="tab-rechazadas">
            <?php if (empty($rechazadas)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">👍</div>
                    <p>No tienes reservas rechazadas</p>
                </div>
            <?php else: ?>
                <div class="reservas-grid">
                    <?php foreach ($rechazadas as $reserva): ?>
                        <?php include 'reserva_card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tab Content: Canceladas -->
        <div class="tab-content" id="tab-canceladas">
            <?php if (empty($canceladas)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">🎉</div>
                    <p>No tienes reservas canceladas</p>
                </div>
            <?php else: ?>
                <div class="reservas-grid">
                    <?php foreach ($canceladas as $reserva): ?>
                        <?php include 'reserva_card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Ocultar todos los tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });

            // Mostrar el tab seleccionado
            document.getElementById('tab-' + tabName).classList.add('active');
            event.target.classList.add('active');
        }

        function cancelarReserva(id) {
            if (confirm('¿Estás seguro de que deseas cancelar esta reserva?')) {
                fetch('../api/reservas.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({action: 'cancelar', reserva_id: id})
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert('Reserva cancelada exitosamente');
                        location.reload();
                    } else {
                        alert(data.message || 'Error al cancelar la reserva');
                    }
                });
            }
        }

        function verDetalles(id) {
            alert('Detalles de la reserva #' + id);
            // Aquí puedes implementar un modal con más información
        }
    </script>

    <?php
    // Template de tarjeta de reserva (incluido en el archivo)
    function getReservaCard($reserva) {
        ob_start();
        ?>
        <!-- Este es el template que se incluye arriba -->
        <?php
        return ob_get_clean();
    }
    ?>
</body>
</html>

<?php
/**
 * Template de tarjeta de reserva
 * Este código se incluye dentro del archivo para renderizar cada reserva
 * En un entorno real, podrías crear un archivo separado reserva_card.php
 */
?>
<!-- TEMPLATE: reserva_card.php -->
<?php if (false): // Este bloque nunca se ejecuta, es solo para mostrar el template ?>
<div class="reserva-card">
    <div class="reserva-header">
        <div class="reserva-cancha">
            Cancha <?= $reserva['cancha_numero'] ?> - <?= htmlspecialchars($reserva['cancha_tipo']) ?>
        </div>
        <span class="badge badge-<?= $reserva['estado'] ?>">
            <?php
            $estados = [
                'pendiente' => '⏳ Pendiente',
                'confirmada' => '✅ Confirmada',
                'rechazada' => '❌ Rechazada',
                'cancelada' => '🚫 Cancelada'
            ];
            echo $estados[$reserva['estado']] ?? $reserva['estado'];
            ?>
        </span>
    </div>

    <div class="reserva-info">
        📅 <strong>Fecha:</strong> <?= formatearFecha($reserva['fecha_reserva'], 'd/m/Y') ?>
    </div>

    <div class="reserva-info">
        ⏰ <strong>Horario:</strong> <?= substr($reserva['hora_inicio'], 0, 5) ?> - <?= substr($reserva['hora_fin'], 0, 5) ?>
    </div>

    <div class="reserva-info">
        💰 <strong>Monto:</strong> $<?= number_format($reserva['monto'], 0, ',', '.') ?>
    </div>

    <div class="reserva-info">
        🌱 <?= htmlspecialchars($reserva['superficie']) ?>
    </div>

    <?php if (!empty($reserva['observaciones'])): ?>
        <div class="reserva-info" style="color: var(--color-text-muted); font-size: 14px;">
            📝 <?= htmlspecialchars($reserva['observaciones']) ?>
        </div>
    <?php endif; ?>

    <?php if ($reserva['estado'] === 'rechazada'): ?>
        <?php if (!empty($reserva['motivo_rechazo'])): ?>
            <div class="motivo-rechazo">
                <strong>Motivo del rechazo:</strong><br>
                <?= htmlspecialchars($reserva['motivo_rechazo']) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($reserva['horario_alternativo_sugerido'])): ?>
            <div class="horarios-sugeridos">
                <strong>⏰ Horarios alternativos sugeridos:</strong><br>
                <?= htmlspecialchars($reserva['horario_alternativo_sugerido']) ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="reserva-actions">
        <?php if (in_array($reserva['estado'], ['pendiente', 'confirmada'])): ?>
            <button class="btn btn-sm btn-danger" onclick="cancelarReserva(<?= $reserva['id'] ?>)">
                🚫 Cancelar
            </button>
        <?php endif; ?>
        <button class="btn btn-sm btn-info" onclick="verDetalles(<?= $reserva['id'] ?>)">
            👁️ Ver Detalles
        </button>
    </div>
</div>
<?php endif; ?>