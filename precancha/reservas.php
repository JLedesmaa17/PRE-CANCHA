<?php
/**
 * ============================================
 * PRE-CANCHA - Sistema de Reservas
 * reservas.php
 * ============================================
 */

require_once 'config/database.php';
require_once 'includes/auth.php';

// Verificar autenticación
requireLogin();

$db = getDB();
$user = currentUser();

// Obtener cancha seleccionada
$cancha_id = $_GET['cancha'] ?? null;
$cancha = null;

if ($cancha_id) {
    $sql = "SELECT * FROM canchas WHERE id = ? AND estado = 'disponible'";
    $cancha = $db->fetchOne($sql, [$cancha_id]);
}

// Si no hay cancha seleccionada, redirigir
if (!$cancha) {
    redirect('index.php');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservar Turno - PRE-CANCHA</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
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

        .container { max-width: 1200px; margin: 0 auto; }
        
        .header {
            background: linear-gradient(145deg, var(--color-bg-light), #0d0d0d);
            border: 2px solid var(--color-primary);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 36px;
            color: var(--color-secondary);
            margin-bottom: 15px;
        }

        .cancha-info {
            background: rgba(218, 165, 32, 0.1);
            padding: 20px;
            border-radius: 15px;
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
        }

        .info-item {
            font-size: 18px;
            color: var(--color-text-muted);
        }

        .pasos {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            gap: 20px;
        }

        .paso {
            flex: 1;
            text-align: center;
            padding: 20px;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 15px;
            border: 2px solid rgba(218, 165, 32, 0.2);
        }

        .paso.activo { border-color: var(--color-secondary); background: rgba(218, 165, 32, 0.1); }
        .paso.completado { border-color: var(--color-success); }

        .paso-numero {
            width: 40px;
            height: 40px;
            background: var(--color-primary);
            color: var(--color-bg-dark);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-weight: bold;
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
        }

        .calendario-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
        }

        .dia-celda {
            aspect-ratio: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(218, 165, 32, 0.3);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: bold;
        }

        .dia-celda:hover:not(.disabled) {
            background: rgba(218, 165, 32, 0.2);
            border-color: var(--color-secondary);
        }

        .dia-celda.disabled { opacity: 0.3; cursor: not-allowed; }
        .dia-celda.selected { background: var(--color-primary); color: var(--color-bg-dark); }

        .horarios-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
        }

        .horario-slot {
            padding: 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid var(--color-primary);
            border-radius: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .horario-slot:hover:not(.ocupado) { background: rgba(218, 165, 32, 0.2); }
        .horario-slot.ocupado { opacity: 0.3; cursor: not-allowed; }
        .horario-slot.selected { background: var(--color-primary); color: var(--color-bg-dark); }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: var(--color-secondary);
            margin-bottom: 10px;
            font-weight: bold;
        }

        .form-group input, .form-group textarea {
            width: 100%;
            padding: 15px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(218, 165, 32, 0.3);
            border-radius: 10px;
            color: var(--color-text);
            font-size: 16px;
        }

        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
            color: var(--color-bg-dark);
        }

        .btn:hover { transform: translateY(-3px); }

        .hidden { display: none !important; }

        @media (max-width: 768px) {
            .pasos { flex-direction: column; }
            .calendario-grid { gap: 5px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚽ Reservar Turno</h1>
            <div class="cancha-info">
                <div class="info-item">🏟️ Cancha <?= $cancha['numero'] ?></div>
                <div class="info-item">⚽ <?= htmlspecialchars($cancha['tipo']) ?></div>
                <div class="info-item">👥 <?= $cancha['jugadores'] ?> jugadores</div>
                <div class="info-item">💰 $<?= number_format($cancha['precio_hora'], 0, ',', '.') ?>/hora</div>
            </div>
        </div>

        <div class="pasos">
            <div class="paso activo" id="paso1">
                <div class="paso-numero">1</div>
                <div>Seleccionar Fecha</div>
            </div>
            <div class="paso" id="paso2">
                <div class="paso-numero">2</div>
                <div>Elegir Horario</div>
            </div>
            <div class="paso" id="paso3">
                <div class="paso-numero">3</div>
                <div>Confirmar</div>
            </div>
        </div>

        <div class="section">
            <h3 class="section-title">📅 Selecciona una Fecha</h3>
            <div class="calendario-grid" id="calendario">
                <!-- Se genera dinámicamente -->
            </div>
        </div>

        <div class="section hidden" id="seccionHorarios">
            <h3 class="section-title">⏰ Horarios Disponibles</h3>
            <div class="horarios-grid" id="horariosGrid">
                <!-- Se genera dinámicamente -->
            </div>
        </div>

        <div class="section hidden" id="seccionConfirmar">
            <h3 class="section-title">✅ Confirmar Reserva</h3>
            <form onsubmit="enviarReserva(event)">
                <div class="form-group">
                    <label>📝 Observaciones (opcional)</label>
                    <textarea id="observaciones" rows="4" placeholder="Comentarios adicionales..."></textarea>
                </div>
                <p style="margin-bottom: 20px; color: var(--color-text-muted);">
                    <strong>Resumen:</strong><br>
                    <span id="resumen"></span>
                </p>
                <button type="submit" class="btn btn-primary">✅ Confirmar Reserva</button>
            </form>
        </div>
    </div>

    <script>
        const canchaId = <?= $cancha_id ?>;
        let fechaSeleccionada = null;
        let horarioSeleccionado = null;

        // Generar calendario del mes actual
        function generarCalendario() {
            const calendario = document.getElementById('calendario');
            calendario.innerHTML = '';
            
            const hoy = new Date();
            const primerDia = new Date(hoy.getFullYear(), hoy.getMonth(), 1).getDay();
            const ultimoDia = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0).getDate();

            // Días vacíos al inicio
            for (let i = 0; i < primerDia; i++) {
                const vacio = document.createElement('div');
                calendario.appendChild(vacio);
            }

            // Días del mes
            for (let dia = 1; dia <= ultimoDia; dia++) {
                const fecha = new Date(hoy.getFullYear(), hoy.getMonth(), dia);
                const celda = document.createElement('div');
                celda.className = 'dia-celda';
                celda.textContent = dia;
                
                if (fecha < hoy.setHours(0,0,0,0)) {
                    celda.classList.add('disabled');
                } else {
                    celda.onclick = () => seleccionarFecha(fecha);
                }
                
                calendario.appendChild(celda);
            }
        }

        function seleccionarFecha(fecha) {
            fechaSeleccionada = fecha;
            document.querySelectorAll('.dia-celda').forEach(el => el.classList.remove('selected'));
            event.target.classList.add('selected');
            
            document.getElementById('paso1').classList.add('completado');
            document.getElementById('paso2').classList.add('activo');
            
            cargarHorarios();
        }

        function cargarHorarios() {
            const fecha = fechaSeleccionada.toISOString().split('T')[0];
            
            fetch(`api/reservas.php?action=obtener_horarios&cancha_id=${canchaId}&fecha_reserva=${fecha}`)
                .then(r => r.json())
                .then(data => {
                    const grid = document.getElementById('horariosGrid');
                    grid.innerHTML = '';
                    
                    const todosHorarios = [
                        '09:00:00', '10:00:00', '11:00:00', '12:00:00', '13:00:00',
                        '14:00:00', '15:00:00', '16:00:00', '17:00:00', '18:00:00',
                        '19:00:00', '20:00:00', '21:00:00', '22:00:00', '23:00:00'
                    ];
                    
                    todosHorarios.forEach(hora => {
                        const slot = document.createElement('div');
                        slot.className = 'horario-slot';
                        slot.textContent = hora.substr(0, 5);
                        
                        if (data.horarios_ocupados.includes(hora)) {
                            slot.classList.add('ocupado');
                            slot.textContent += ' (Ocupado)';
                        } else {
                            slot.onclick = () => seleccionarHorario(hora);
                        }
                        
                        grid.appendChild(slot);
                    });
                    
                    document.getElementById('seccionHorarios').classList.remove('hidden');
                });
        }

        function seleccionarHorario(hora) {
            horarioSeleccionado = hora;
            document.querySelectorAll('.horario-slot').forEach(el => el.classList.remove('selected'));
            event.target.classList.add('selected');
            
            document.getElementById('paso2').classList.add('completado');
            document.getElementById('paso3').classList.add('activo');
            
            const resumen = `
                📅 ${fechaSeleccionada.toLocaleDateString('es-AR')}<br>
                ⏰ ${hora.substr(0,5)} a ${calcularHoraFin(hora).substr(0,5)}<br>
                💰 $<?= number_format($cancha['precio_hora'], 0, ',', '.') ?>
            `;
            document.getElementById('resumen').innerHTML = resumen;
            document.getElementById('seccionConfirmar').classList.remove('hidden');
        }

        function calcularHoraFin(hora) {
            const [h, m] = hora.split(':');
            return `${(parseInt(h) + 1).toString().padStart(2, '0')}:${m}:00`;
        }

        function enviarReserva(e) {
            e.preventDefault();
            
            const datos = {
                action: 'crear',
                cancha_id: canchaId,
                fecha_reserva: fechaSeleccionada.toISOString().split('T')[0],
                hora_inicio: horarioSeleccionado,
                observaciones: document.getElementById('observaciones').value
            };

            fetch('api/reservas.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(datos)
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.message);
                    window.location.href = 'usuario/mis-reservas.php';
                } else {
                    alert('❌ ' + data.message);
                }
            });
        }

        // Inicializar
        generarCalendario();
    </script>
</body>
</html>