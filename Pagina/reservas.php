<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservar Turno - PRE-CANCHA</title>
    <meta name="description" content="Reserva tu turno en nuestra cancha de forma rápida y sencilla">
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
            overflow-x: hidden;
            min-height: 100vh;
        }

        /* HEADER */
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
            transition: all 0.3s;
        }

        .logo:hover {
            transform: scale(1.05);
        }

        nav ul {
            display: flex;
            list-style: none;
            gap: 30px;
        }

        nav a {
            color: var(--color-primary);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            position: relative;
        }

        nav a:hover {
            color: var(--color-secondary);
            text-shadow: 0 0 10px rgba(255, 215, 0, 0.8);
        }

        /* CONTENEDOR PRINCIPAL */
        .reservas-container {
            max-width: 1400px;
            margin: 100px auto 50px;
            padding: 20px;
        }

        /* BREADCRUMB */
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 30px;
            color: var(--color-text-muted);
            flex-wrap: wrap;
        }

        .breadcrumb a {
            color: var(--color-primary);
            text-decoration: none;
            transition: color 0.3s;
        }

        .breadcrumb a:hover {
            color: var(--color-secondary);
        }

        /* INFORMACIÓN DE LA CANCHA */
        .cancha-seleccionada {
            background: linear-gradient(145deg, var(--color-bg-light), #0d0d0d);
            border: 2px solid var(--color-primary);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .cancha-seleccionada::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(218, 165, 32, 0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .cancha-seleccionada-content {
            position: relative;
            z-index: 1;
        }

        .cancha-seleccionada h2 {
            color: var(--color-secondary);
            font-size: 36px;
            margin-bottom: 15px;
        }

        .cancha-detalle {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
            margin-top: 30px;
        }

        .detalle-item {
            background: rgba(218, 165, 32, 0.1);
            padding: 20px 30px;
            border-radius: 15px;
            border: 1px solid var(--color-primary);
            transition: all 0.3s;
        }

        .detalle-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(218, 165, 32, 0.3);
        }

        /* SECCIÓN PASO A PASO */
        .pasos-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            gap: 20px;
            flex-wrap: wrap;
        }

        .paso {
            flex: 1;
            min-width: 200px;
            text-align: center;
            padding: 20px;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 15px;
            border: 2px solid rgba(218, 165, 32, 0.2);
            transition: all 0.3s;
            position: relative;
        }

        .paso.activo {
            border-color: var(--color-secondary);
            background: rgba(218, 165, 32, 0.1);
        }

        .paso.completado {
            border-color: var(--color-success);
            background: rgba(0, 255, 0, 0.05);
        }

        .paso-numero {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
            color: var(--color-bg-dark);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-weight: bold;
            font-size: 18px;
        }

        .paso.completado .paso-numero {
            background: var(--color-success);
        }

        .paso-titulo {
            color: var(--color-secondary);
            font-size: 16px;
            font-weight: 600;
        }

        /* CALENDARIO */
        .calendario-section {
            background: linear-gradient(145deg, var(--color-bg-light), #0d0d0d);
            border: 2px solid var(--color-primary);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 40px;
        }

        .calendario-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .calendario-nav {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .btn-nav {
            background: var(--color-primary);
            color: var(--color-bg-dark);
            border: none;
            padding: 12px 24px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-nav:hover:not(:disabled) {
            background: var(--color-secondary);
            transform: scale(1.05);
        }

        .btn-nav:disabled {
            background: #555;
            cursor: not-allowed;
            opacity: 0.5;
        }

        .mes-actual {
            font-size: 28px;
            color: var(--color-secondary);
            font-weight: bold;
            min-width: 200px;
            text-align: center;
        }

        .calendario-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
            margin-bottom: 30px;
        }

        .dia-header {
            text-align: center;
            font-weight: bold;
            color: var(--color-primary);
            padding: 15px;
            background: rgba(218, 165, 32, 0.1);
            border-radius: 10px;
        }

        .dia-celda {
            aspect-ratio: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(218, 165, 32, 0.3);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
            padding: 10px;
        }

        .dia-celda:hover:not(.disabled):not(.otro-mes) {
            background: rgba(218, 165, 32, 0.2);
            border-color: var(--color-secondary);
            transform: scale(1.05);
        }

        .dia-celda.disabled {
            opacity: 0.3;
            cursor: not-allowed;
            background: rgba(0, 0, 0, 0.3);
        }

        .dia-celda.otro-mes {
            opacity: 0.2;
        }

        .dia-celda.selected {
            background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
            color: var(--color-bg-dark);
            border-color: var(--color-secondary);
            font-weight: bold;
            transform: scale(1.1);
        }

        .dia-celda.hoy {
            border: 2px solid var(--color-secondary);
            box-shadow: 0 0 15px rgba(255, 215, 0, 0.3);
        }

        .numero-dia {
            font-size: 20px;
            font-weight: bold;
        }

        /* HORARIOS */
        .horarios-section {
            margin-top: 30px;
        }

        .horarios-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .horario-slot {
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid var(--color-primary);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
        }

        .horario-slot:hover:not(.ocupado) {
            background: rgba(218, 165, 32, 0.2);
            border-color: var(--color-secondary);
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(218, 165, 32, 0.3);
        }

        .horario-slot.ocupado {
            background: rgba(255, 68, 68, 0.1);
            border-color: var(--color-danger);
            opacity: 0.5;
            cursor: not-allowed;
        }

        .horario-slot.selected {
            background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
            border-color: var(--color-secondary);
            color: var(--color-bg-dark);
            font-weight: bold;
            transform: scale(1.05);
        }

        .horario-time {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .horario-estado {
            font-size: 12px;
            text-transform: uppercase;
        }

        /* FORMULARIO */
        .form-reserva {
            background: linear-gradient(145deg, var(--color-bg-light), #0d0d0d);
            border: 2px solid var(--color-primary);
            border-radius: 20px;
            padding: 40px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            color: var(--color-secondary);
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 16px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 15px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(218, 165, 32, 0.3);
            border-radius: 10px;
            color: var(--color-text);
            font-size: 16px;
            transition: all 0.3s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--color-secondary);
            background: rgba(218, 165, 32, 0.1);
            box-shadow: 0 0 15px rgba(218, 165, 32, 0.2);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .resumen-reserva {
            background: rgba(218, 165, 32, 0.1);
            border: 2px solid var(--color-primary);
            border-radius: 15px;
            padding: 25px;
            margin: 30px 0;
        }

        .resumen-reserva h3 {
            color: var(--color-secondary);
            margin-bottom: 20px;
            font-size: 24px;
        }

        .resumen-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid rgba(218, 165, 32, 0.2);
        }

        .resumen-item:last-child {
            border-bottom: none;
        }

        .resumen-label {
            color: var(--color-text-muted);
        }

        .resumen-value {
            color: var(--color-secondary);
            font-weight: bold;
        }

        .form-actions {
            display: flex;
            gap: 20px;
            margin-top: 30px;
        }

        .btn-submit,
        .btn-cancel {
            flex: 1;
            padding: 18px;
            border: none;
            border-radius: 15px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
            color: var(--color-bg-dark);
        }

        .btn-submit:hover:not(:disabled) {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(218, 165, 32, 0.5);
        }

        .btn-submit:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn-cancel {
            background: rgba(255, 255, 255, 0.1);
            color: var(--color-text);
            border: 2px solid rgba(218, 165, 32, 0.3);
        }

        .btn-cancel:hover {
            background: rgba(255, 68, 68, 0.2);
            border-color: var(--color-danger);
        }

        /* MODAL */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.95);
            z-index: 2000;
            animation: fadeIn 0.3s;
        }

        .modal-overlay.active {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: linear-gradient(145deg, var(--color-bg-light), #0d0d0d);
            border: 2px solid var(--color-primary);
            border-radius: 20px;
            padding: 40px;
            max-width: 500px;
            width: 90%;
            animation: slideUp 0.3s;
        }

        .modal-content h2 {
            color: var(--color-secondary);
            margin-bottom: 20px;
            font-size: 28px;
        }

        .modal-content p {
            color: var(--color-text-muted);
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .modal-actions {
            display: flex;
            gap: 15px;
        }

        .btn-modal {
            flex: 1;
            padding: 15px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-modal.primary {
            background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
            color: var(--color-bg-dark);
        }

        .btn-modal.primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(218, 165, 32, 0.5);
        }

        .btn-modal.secondary {
            background: rgba(255, 255, 255, 0.1);
            color: var(--color-text);
            border: 2px solid rgba(218, 165, 32, 0.3);
        }

        .btn-modal.secondary:hover {
            background: rgba(218, 165, 32, 0.1);
        }

        /* FOOTER */
        footer {
            background: var(--color-bg-dark);
            padding: 40px 20px;
            text-align: center;
            border-top: 2px solid var(--color-primary);
            margin-top: 80px;
        }

        footer p {
            color: var(--color-primary);
            font-size: 16px;
            margin: 10px 0;
        }

        /* ANIMACIONES */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* UTILIDADES */
        .hidden {
            display: none !important;
        }

        .section-title {
            color: var(--color-secondary);
            font-size: 32px;
            margin-bottom: 20px;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .calendario-grid {
                gap: 5px;
            }

            .dia-celda {
                padding: 5px;
            }

            .numero-dia {
                font-size: 16px;
            }

            .horarios-grid {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            }

            .form-actions {
                flex-direction: column;
            }

            .mes-actual {
                font-size: 20px;
                min-width: auto;
            }

            .calendario-header {
                flex-direction: column;
            }

            .cancha-detalle {
                flex-direction: column;
                gap: 15px;
            }

            .pasos-container {
                flex-direction: column;
            }

            nav ul {
                gap: 15px;
            }

            .logo {
                font-size: 24px;
            }
        }

        @media (max-width: 480px) {
            .calendario-section,
            .form-reserva,
            .cancha-seleccionada {
                padding: 20px;
            }

            .horario-time {
                font-size: 18px;
            }

            .modal-content {
                padding: 25px;
            }
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <header>
        <nav>
            <div class="logo" onclick="volverInicio()">⚽ PRE-CANCHA</div>
            <ul>
                <li><a href="index.html">Inicio</a></li>
                <li><a href="index.html#canchas">Canchas</a></li>
                <li><a href="index.html#features">Características</a></li>
                <li><a href="index.html#contacto">Contacto</a></li>
            </ul>
        </nav>
    </header>

    <!-- CONTENEDOR PRINCIPAL -->
    <div class="reservas-container">
        <!-- BREADCRUMB -->
        <div class="breadcrumb">
            <a href="index.html">🏠 Inicio</a>
            <span>›</span>
            <span>Reservar Turno</span>
        </div>

        <!-- INFORMACIÓN DE LA CANCHA SELECCIONADA -->
        <div class="cancha-seleccionada">
            <div class="cancha-seleccionada-content">
                <h2>🏟️ Reserva tu Turno</h2>
                <p style="color: #cccccc; margin-bottom: 20px;">
                    Selecciona la fecha y horario de tu preferencia
                </p>
                <div class="cancha-detalle" id="canchaDetalle">
                    <!-- Se carga dinámicamente -->
                </div>
            </div>
        </div>

        <!-- PASOS DEL PROCESO -->
        <div class="pasos-container">
            <div class="paso activo" id="paso1">
                <div class="paso-numero">1</div>
                <div class="paso-titulo">Seleccionar Fecha</div>
            </div>
            <div class="paso" id="paso2">
                <div class="paso-numero">2</div>
                <div class="paso-titulo">Elegir Horario</div>
            </div>
            <div class="paso" id="paso3">
                <div class="paso-numero">3</div>
                <div class="paso-titulo">Completar Datos</div>
            </div>
        </div>

        <!-- CALENDARIO -->
        <div class="calendario-section">
            <h3 class="section-title">
                📅 Paso 1: Selecciona una Fecha
            </h3>
            
            <div class="calendario-header">
                <div class="calendario-nav">
                    <button class="btn-nav" onclick="cambiarMes(-1)" id="btnMesAnterior">
                        ◀ Anterior
                    </button>
                    <div class="mes-actual" id="mesActual">
                        <!-- Se actualiza dinámicamente -->
                    </div>
                    <button class="btn-nav" onclick="cambiarMes(1)">
                        Siguiente ▶
                    </button>
                </div>
            </div>

            <div class="calendario-grid" id="calendarioGrid">
                <!-- Se genera dinámicamente -->
            </div>

            <!-- SECCIÓN DE HORARIOS -->
            <div class="horarios-section hidden" id="horariosSection">
                <h3 class="section-title">
                    ⏰ Paso 2: Horarios Disponibles
                </h3>
                <p style="color: #cccccc; margin-bottom: 20px;">
                    Selecciona el horario que prefieras para tu reserva
                </p>
                <div class="horarios-grid" id="horariosGrid">
                    <!-- Se genera dinámicamente -->
                </div>
            </div>
        </div>

        <!-- FORMULARIO DE RESERVA -->
        <div class="form-reserva hidden" id="formReserva">
            <h3 class="section-title">
                📝 Paso 3: Completa tu Reserva
            </h3>

            <!-- RESUMEN DE LA RESERVA -->
            <div class="resumen-reserva">
                <h3>📋 Resumen de tu Reserva</h3>
                <div class="resumen-item">
                    <span class="resumen-label">Cancha:</span>
                    <span class="resumen-value" id="resumenCancha">-</span>
                </div>
                <div class="resumen-item">
                    <span class="resumen-label">Fecha:</span>
                    <span class="resumen-value" id="resumenFecha">-</span>
                </div>
                <div class="resumen-item">
                    <span class="resumen-label">Horario:</span>
                    <span class="resumen-value" id="resumenHorario">-</span>
                </div>
                <div class="resumen-item">
                    <span class="resumen-label">Duración:</span>
                    <span class="resumen-value">1 hora</span>
                </div>
            </div>

            <!-- FORMULARIO -->
            <form id="formularioReserva" onsubmit="enviarReserva(event)">
                <div class="form-group">
                    <label for="nombre">👤 Nombre Completo *</label>
                    <input 
                        type="text" 
                        id="nombre" 
                        name="nombre" 
                        required 
                        placeholder="Ej: Juan Pérez"
                        minlength="3"
                    >
                </div>

                <div class="form-group">
                    <label for="telefono">📱 Teléfono de Contacto *</label>
                    <input 
                        type="tel" 
                        id="telefono" 
                        name="telefono" 
                        required 
                        placeholder="Ej: +54 11 1234-5678"
                    >
                </div>

                <div class="form-group">
                    <label for="email">📧 Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="tu@email.com"
                    >
                </div>

                <div class="form-group">
                    <label for="observaciones">💬 Observaciones (opcional)</label>
                    <textarea 
                        id="observaciones" 
                        name="observaciones" 
                        placeholder="Comentarios adicionales sobre tu reserva..."
                    ></textarea>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="cancelarReserva()">
                        ❌ Cancelar
                    </button>
                    <button type="submit" class="btn-submit" id="btnEnviar">
                        ✅ Confirmar Reserva
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL DE CONFIRMACIÓN -->
    <div class="modal-overlay" id="modalConfirmacion">
        <div class="modal-content">
            <h2>🎉 ¡Reserva Confirmada!</h2>
            <p id="mensajeConfirmacion"></p>
            <div class="modal-actions">
                <button class="btn-modal primary" onclick="cerrarModalYVolver()">
                    🏠 Volver al Inicio
                </button>
                <button class="btn-modal secondary" onclick="nuevaReserva()">
                    ➕ Nueva Reserva
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL DE ERROR -->
    <div class="modal-overlay" id="modalError">
        <div class="modal-content">
            <h2>⚠️ Atención</h2>
            <p id="mensajeError"></p>
            <div class="modal-actions">
                <button class="btn-modal primary" onclick="cerrarModal('modalError')">
                    Entendido
                </button>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <footer>
        <p>© 2024 PRE-CANCHA - Sistema de Gestión de Reservas</p>
        <p style="margin-top: 10px;">📧 info@precancha.com | 📞 +54 11 1234-5678</p>
    </footer>

    <script>
        // ============================================
        // VARIABLES GLOBALES
        // ============================================
        let canchaSeleccionada = null;
        let mesActual = new Date().getMonth();
        let anioActual = new Date().getFullYear();
        let fechaSeleccionada = null;
        let horarioSeleccionado = null;
        
        const meses = [
            'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
        ];
        
        const diasSemana = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        
        const horariosDisponibles = [
            '09:00', '10:00', '11