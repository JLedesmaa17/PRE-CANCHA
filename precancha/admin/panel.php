<?php
/**
 * ============================================
 * PRE-CANCHA - Panel de Administración
 * admin/panel.php
 * ============================================
 */

require_once '../config/database.php';
require_once '../includes/auth.php';

// Verificar que sea administrador
requireAdmin();

$db = getDB();
$user = currentUser();

// Obtener estadísticas
$sql = "SELECT COUNT(*) as total FROM reservas WHERE estado = 'pendiente'";
$pendientes = $db->fetchOne($sql);

$sql = "SELECT COUNT(*) as total FROM reservas WHERE estado = 'confirmada' AND fecha_reserva >= CURDATE()";
$confirmadas = $db->fetchOne($sql);

$sql = "SELECT COUNT(*) as total FROM usuarios WHERE rol = 'usuario'";
$usuarios = $db->fetchOne($sql);

$sql = "SELECT COUNT(*) as total FROM canchas WHERE estado = 'disponible'";
$canchas = $db->fetchOne($sql);

// Obtener reservas pendientes
$sql = "SELECT r.*, c.numero as cancha_numero, c.tipo as cancha_tipo, u.nombre as usuario_nombre, u.email, u.telefono
        FROM reservas r
        INNER JOIN canchas c ON r.cancha_id = c.id
        INNER JOIN usuarios u ON r.usuario_id = u.id
        WHERE r.estado = 'pendiente'
        ORDER BY r.fecha_reserva ASC, r.hora_inicio ASC
        LIMIT 10";
$reservas_pendientes = $db->fetchAll($sql);

// Obtener próximas reservas confirmadas
$sql = "SELECT r.*, c.numero as cancha_numero, c.tipo as cancha_tipo, u.nombre as usuario_nombre, u.telefono
        FROM reservas r
        INNER JOIN canchas c ON r.cancha_id = c.id
        INNER JOIN usuarios u ON r.usuario_id = u.id
        WHERE r.estado = 'confirmada' AND r.fecha_reserva >= CURDATE()
        ORDER BY r.fecha_reserva ASC, r.hora_inicio ASC
        LIMIT 10";
$proximas_reservas = $db->fetchAll($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - PRE-CANCHA</title>
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
            max-width: 1600px;
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: linear-gradient(145deg, var(--color-bg-light), #0d0d0d);
            border: 2px solid var(--color-primary);
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(218, 165, 32, 0.3);
        }

        .stat-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .stat-number {
            font-size: 42px;
            font-weight: bold;
            color: var(--color-secondary);
            margin-bottom: 10px;
        }

        .stat-label {
            font-size: 16px;
            color: var(--color-text-muted);
            text-transform: uppercase;
        }

        .section {
            background: linear-gradient(145deg, var(--color-bg-light), #0d0d0d);
            border: 2px solid var(--color-primary);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 24px;
            color: var(--color-secondary);
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(218, 165, 32, 0.2);
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid rgba(218, 165, 32, 0.2);
        }

        th {
            background: rgba(218, 165, 32, 0.1);
            color: var(--color-secondary);
            font-weight: 600;
        }

        tr:hover {
            background: rgba(218, 165, 32, 0.05);
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

        .btn-sm {
            padding: 8px 16px;
            font-size: 14px;
            margin: 0 5px;
        }

        .btn-success {
            background: var(--color-success);
            color: var(--color-bg-dark);
        }

        .btn-danger {
            background: var(--color-danger);
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: var(--color-text-muted);
        }

        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.95);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-content {
            background: linear-gradient(145deg, var(--color-bg-light), #0d0d0d);
            border: 2px solid var(--color-primary);
            border-radius: 20px;
            padding: 40px;
            max-width: 500px;
            width: 90%;
        }

        .modal-content h2 {
            color: var(--color-secondary);
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: var(--color-secondary);
            margin-bottom: 10px;
        }

        .form-group textarea {
            width: 100%;
            padding: 15px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(218, 165, 32, 0.3);
            border-radius: 10px;
            color: var(--color-text);
            resize: vertical;
            min-height: 100px;
        }

        .modal-actions {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .header {
                flex-direction: column;
                text-align: center;
            }

            table {
                font-size: 14px;
            }

            th, td {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>📊 Panel de Administración</h1>
                <p style="color: var(--color-text-muted); margin-top: 10px;">
                    Bienvenido, <strong><?= htmlspecialchars($user['nombre']) ?></strong>
                </p>
            </div>
            <div class="header-actions">
                <a href="../index.php" class="btn btn-secondary">
                    🏠 Inicio
                </a>
                <a href="../logout.php" class="btn btn-primary">
                    🚪 Cerrar Sesión
                </a>
            </div>
        </div>

        <!-- ESTADÍSTICAS -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">⏳</div>
                <div class="stat-number"><?= $pendientes['total'] ?></div>
                <div class="stat-label">Reservas Pendientes</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-number"><?= $confirmadas['total'] ?></div>
                <div class="stat-label">Próximas Reservas</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-number"><?= $usuarios['total'] ?></div>
                <div class="stat-label">Usuarios Registrados</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">⚽</div>
                <div class="stat-number"><?= $canchas['total'] ?></div>
                <div class="stat-label">Canchas Activas</div>
            </div>
        </div>

        <!-- RESERVAS PENDIENTES -->
        <div class="section">
            <h2 class="section-title">⏳ Reservas Pendientes de Aprobación</h2>
            
            <?php if (empty($reservas_pendientes)): ?>
                <div class="empty-state">
                    <p>✅ No hay reservas pendientes en este momento</p>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Contacto</th>
                                <th>Cancha</th>
                                <th>Fecha</th>
                                <th>Horario</th>
                                <th>Monto</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservas_pendientes as $reserva): ?>
                                <tr>
                                    <td>#<?= $reserva['id'] ?></td>
                                    <td><?= htmlspecialchars($reserva['usuario_nombre']) ?></td>
                                    <td>
                                        📧 <?= htmlspecialchars($reserva['email']) ?><br>
                                        📱 <?= htmlspecialchars($reserva['telefono']) ?>
                                    </td>
                                    <td>Cancha <?= $reserva['cancha_numero'] ?> - <?= $reserva['cancha_tipo'] ?></td>
                                    <td><?= date('d/m/Y', strtotime($reserva['fecha_reserva'])) ?></td>
                                    <td><?= substr($reserva['hora_inicio'], 0, 5) ?> - <?= substr($reserva['hora_fin'], 0, 5) ?></td>
                                    <td>$<?= number_format($reserva['monto'], 0, ',', '.') ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-success" onclick="aprobarReserva(<?= $reserva['id'] ?>)">
                                            ✅ Aprobar
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="mostrarModalRechazo(<?= $reserva['id'] ?>)">
                                            ❌ Rechazar
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- PRÓXIMAS RESERVAS -->
        <div class="section">
            <h2 class="section-title">📅 Próximas Reservas Confirmadas</h2>
            
            <?php if (empty($proximas_reservas)): ?>
                <div class="empty-state">
                    <p>📭 No hay reservas confirmadas próximamente</p>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Teléfono</th>
                                <th>Cancha</th>
                                <th>Fecha</th>
                                <th>Horario</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($proximas_reservas as $reserva): ?>
                                <tr>
                                    <td>#<?= $reserva['id'] ?></td>
                                    <td><?= htmlspecialchars($reserva['usuario_nombre']) ?></td>
                                    <td><?= htmlspecialchars($reserva['telefono']) ?></td>
                                    <td>Cancha <?= $reserva['cancha_numero'] ?> - <?= $reserva['cancha_tipo'] ?></td>
                                    <td><?= date('d/m/Y', strtotime($reserva['fecha_reserva'])) ?></td>
                                    <td><?= substr($reserva['hora_inicio'], 0, 5) ?> - <?= substr($reserva['hora_fin'], 0, 5) ?></td>
                                    <td><span class="badge badge-confirmada">✅ Confirmada</span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- MODAL DE RECHAZO -->
    <div class="modal-overlay" id="modalRechazo">
        <div class="modal-content">
            <h2>❌ Rechazar Reserva</h2>
            <form id="formRechazo" onsubmit="rechazarReserva(event)">
                <input type="hidden" id="reservaIdRechazo">
                <div class="form-group">
                    <label>Motivo del rechazo:</label>
                    <textarea id="motivoRechazo" required placeholder="Explica el motivo del rechazo..."></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="cerrarModal()">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-danger">
                        Rechazar Reserva
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function aprobarReserva(id) {
            if (!confirm('¿Confirmar aprobación de esta reserva?')) return;

            fetch('../api/reservas.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({action: 'aprobar', reserva_id: id})
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Reserva aprobada exitosamente');
                    location.reload();
                } else {
                    alert('❌ ' + (data.message || 'Error al aprobar la reserva'));
                }
            })
            .catch(err => {
                alert('❌ Error de conexión');
                console.error(err);
            });
        }

        function mostrarModalRechazo(id) {
            document.getElementById('reservaIdRechazo').value = id;
            document.getElementById('motivoRechazo').value = '';
            document.getElementById('modalRechazo').classList.add('active');
        }

        function cerrarModal() {
            document.getElementById('modalRechazo').classList.remove('active');
        }

        function rechazarReserva(event) {
            event.preventDefault();
            
            const id = document.getElementById('reservaIdRechazo').value;
            const motivo = document.getElementById('motivoRechazo').value;

            fetch('../api/reservas.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    action: 'rechazar',
                    reserva_id: id,
                    motivo: motivo
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Reserva rechazada. Se sugirieron horarios alternativos al usuario.');
                    location.reload();
                } else {
                    alert('❌ ' + (data.message || 'Error al rechazar la reserva'));
                }
            })
            .catch(err => {
                alert('❌ Error de conexión');
                console.error(err);
            });
        }
    </script>
</body>
</html>