<?php
/**
 * ============================================
 * PRE-CANCHA - API de Gestión de Reservas
 * api/reservas.php
 * ============================================
 */

header('Content-Type: application/json; charset=utf-8');

require_once '../config/database.php';
require_once '../includes/auth.php';

// Verificar autenticación
if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'No autenticado'], 401);
}

$db = getDB();
$user = currentUser();

// Obtener el método y los datos
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

// Routing según la acción
$action = $input['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'crear':
        crearReserva($db, $user, $input);
        break;
    
    case 'aprobar':
        if (!isAdmin()) jsonResponse(['success' => false, 'message' => 'No autorizado'], 403);
        aprobarReserva($db, $user, $input);
        break;
    
    case 'rechazar':
        if (!isAdmin()) jsonResponse(['success' => false, 'message' => 'No autorizado'], 403);
        rechazarReserva($db, $user, $input);
        break;
    
    case 'cancelar':
        cancelarReserva($db, $user, $input);
        break;
    
    case 'verificar_disponibilidad':
        verificarDisponibilidad($db, $input);
        break;
    
    case 'obtener_horarios':
        obtenerHorarios($db, $input);
        break;
    
    case 'mis_reservas':
        obtenerMisReservas($db, $user);
        break;
    
    case 'todas_reservas':
        if (!isAdmin()) jsonResponse(['success' => false, 'message' => 'No autorizado'], 403);
        obtenerTodasReservas($db, $input);
        break;
    
    default:
        jsonResponse(['success' => false, 'message' => 'Acción no válida'], 400);
}

/**
 * Crear nueva reserva
 */
function crearReserva($db, $user, $input) {
    $cancha_id = $input['cancha_id'] ?? null;
    $fecha_reserva = $input['fecha_reserva'] ?? null;
    $hora_inicio = $input['hora_inicio'] ?? null;
    $observaciones = $input['observaciones'] ?? '';
    
    // Validaciones
    if (!$cancha_id || !$fecha_reserva || !$hora_inicio) {
        jsonResponse(['success' => false, 'message' => 'Datos incompletos']);
    }
    
    // Validar fecha (no puede ser en el pasado)
    if (strtotime($fecha_reserva) < strtotime(date('Y-m-d'))) {
        jsonResponse(['success' => false, 'message' => 'No se puede reservar fechas pasadas']);
    }
    
    // Calcular hora de fin (1 hora después)
    $hora_fin = date('H:i:s', strtotime($hora_inicio . ' +1 hour'));
    
    // Verificar disponibilidad
    $sql = "SELECT COUNT(*) as total FROM reservas 
            WHERE cancha_id = ? 
            AND fecha_reserva = ? 
            AND hora_inicio = ?
            AND estado IN ('pendiente', 'confirmada')";
    
    $existe = $db->fetchOne($sql, [$cancha_id, $fecha_reserva, $hora_inicio]);
    
    if ($existe && $existe['total'] > 0) {
        jsonResponse([
            'success' => false, 
            'message' => 'Este horario ya está reservado',
            'sugerir_alternativas' => true
        ]);
    }
    
    // Obtener precio de la cancha
    $sql = "SELECT precio_hora FROM canchas WHERE id = ?";
    $cancha = $db->fetchOne($sql, [$cancha_id]);
    $monto = $cancha['precio_hora'] ?? 0;
    
    // Crear reserva
    $sql = "INSERT INTO reservas (cancha_id, usuario_id, fecha_reserva, hora_inicio, hora_fin, monto, observaciones, estado) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pendiente')";
    
    if ($db->execute($sql, [$cancha_id, $user['id'], $fecha_reserva, $hora_inicio, $hora_fin, $monto, $observaciones])) {
        $reserva_id = $db->lastInsertId();
        
        // Registrar actividad
        registrarActividad($user['id'], 'crear_reserva', 'reservas', $reserva_id, "Cancha $cancha_id - $fecha_reserva $hora_inicio");
        
        jsonResponse([
            'success' => true, 
            'message' => 'Reserva creada exitosamente. Pendiente de aprobación',
            'reserva_id' => $reserva_id
        ]);
    }
    
    jsonResponse(['success' => false, 'message' => 'Error al crear la reserva']);
}

/**
 * Aprobar reserva
 */
function aprobarReserva($db, $user, $input) {
    $reserva_id = $input['reserva_id'] ?? null;
    
    if (!$reserva_id) {
        jsonResponse(['success' => false, 'message' => 'ID de reserva requerido']);
    }
    
    // Verificar que la reserva existe y está pendiente
    $sql = "SELECT * FROM reservas WHERE id = ? AND estado = 'pendiente'";
    $reserva = $db->fetchOne($sql, [$reserva_id]);
    
    if (!$reserva) {
        jsonResponse(['success' => false, 'message' => 'Reserva no encontrada o ya procesada']);
    }
    
    // Verificar nuevamente disponibilidad
    $sql = "SELECT COUNT(*) as total FROM reservas 
            WHERE cancha_id = ? 
            AND fecha_reserva = ? 
            AND hora_inicio = ?
            AND estado = 'confirmada'
            AND id != ?";
    
    $existe = $db->fetchOne($sql, [$reserva['cancha_id'], $reserva['fecha_reserva'], $reserva['hora_inicio'], $reserva_id]);
    
    if ($existe && $existe['total'] > 0) {
        jsonResponse(['success' => false, 'message' => 'El horario ya fue confirmado para otra reserva']);
    }
    
    // Aprobar reserva
    $sql = "UPDATE reservas 
            SET estado = 'confirmada', 
                admin_id = ?,
                fecha_confirmacion = NOW()
            WHERE id = ?";
    
    if ($db->execute($sql, [$user['id'], $reserva_id])) {
        registrarActividad($user['id'], 'aprobar_reserva', 'reservas', $reserva_id);
        
        jsonResponse([
            'success' => true, 
            'message' => 'Reserva aprobada exitosamente'
        ]);
    }
    
    jsonResponse(['success' => false, 'message' => 'Error al aprobar la reserva']);
}

/**
 * Rechazar reserva con sugerencia de horarios alternativos
 */
function rechazarReserva($db, $user, $input) {
    $reserva_id = $input['reserva_id'] ?? null;
    $motivo = $input['motivo'] ?? 'Horario no disponible';
    
    if (!$reserva_id) {
        jsonResponse(['success' => false, 'message' => 'ID de reserva requerido']);
    }
    
    // Obtener datos de la reserva
    $sql = "SELECT * FROM reservas WHERE id = ? AND estado = 'pendiente'";
    $reserva = $db->fetchOne($sql, [$reserva_id]);
    
    if (!$reserva) {
        jsonResponse(['success' => false, 'message' => 'Reserva no encontrada o ya procesada']);
    }
    
    // Buscar horarios alternativos
    $sql = "CALL sp_horarios_alternativos(?, ?, ?)";
    $stmt = $db->query($sql, [$reserva['cancha_id'], $reserva['fecha_reserva'], $reserva['hora_inicio']]);
    $horarios_alternativos = $stmt ? $stmt->fetchAll() : [];
    
    // Formatear horarios alternativos
    $sugerencias = [];
    foreach ($horarios_alternativos as $horario) {
        $sugerencias[] = substr($horario['hora_inicio'], 0, 5);
    }
    $horario_sugerido = implode(', ', $sugerencias);
    
    // Rechazar reserva
    $sql = "UPDATE reservas 
            SET estado = 'rechazada', 
                admin_id = ?,
                motivo_rechazo = ?,
                horario_alternativo_sugerido = ?,
                fecha_rechazo = NOW()
            WHERE id = ?";
    
    if ($db->execute($sql, [$user['id'], $motivo, $horario_sugerido, $reserva_id])) {
        registrarActividad($user['id'], 'rechazar_reserva', 'reservas', $reserva_id, "Motivo: $motivo");
        
        jsonResponse([
            'success' => true, 
            'message' => 'Reserva rechazada',
            'horarios_alternativos' => $sugerencias
        ]);
    }
    
    jsonResponse(['success' => false, 'message' => 'Error al rechazar la reserva']);
}

/**
 * Cancelar reserva (por el usuario)
 */
function cancelarReserva($db, $user, $input) {
    $reserva_id = $input['reserva_id'] ?? null;
    
    if (!$reserva_id) {
        jsonResponse(['success' => false, 'message' => 'ID de reserva requerido']);
    }
    
    // Verificar que la reserva pertenece al usuario o es admin
    $sql = "SELECT * FROM reservas WHERE id = ? AND (usuario_id = ? OR ? = 1)";
    $reserva = $db->fetchOne($sql, [$reserva_id, $user['id'], isAdmin() ? 1 : 0]);
    
    if (!$reserva) {
        jsonResponse(['success' => false, 'message' => 'Reserva no encontrada o no autorizada']);
    }
    
    if ($reserva['estado'] === 'cancelada') {
        jsonResponse(['success' => false, 'message' => 'La reserva ya está cancelada']);
    }
    
    // Cancelar reserva
    $sql = "UPDATE reservas SET estado = 'cancelada' WHERE id = ?";
    
    if ($db->execute($sql, [$reserva_id])) {
        registrarActividad($user['id'], 'cancelar_reserva', 'reservas', $reserva_id);
        
        jsonResponse([
            'success' => true, 
            'message' => 'Reserva cancelada exitosamente'
        ]);
    }
    
    jsonResponse(['success' => false, 'message' => 'Error al cancelar la reserva']);
}

/**
 * Verificar disponibilidad de un horario específico
 */
function verificarDisponibilidad($db, $input) {
    $cancha_id = $input['cancha_id'] ?? null;
    $fecha_reserva = $input['fecha_reserva'] ?? null;
    $hora_inicio = $input['hora_inicio'] ?? null;
    
    if (!$cancha_id || !$fecha_reserva || !$hora_inicio) {
        jsonResponse(['success' => false, 'message' => 'Datos incompletos']);
    }
    
    $sql = "SELECT COUNT(*) as total FROM reservas 
            WHERE cancha_id = ? 
            AND fecha_reserva = ? 
            AND hora_inicio = ?
            AND estado IN ('pendiente', 'confirmada')";
    
    $resultado = $db->fetchOne($sql, [$cancha_id, $fecha_reserva, $hora_inicio]);
    
    jsonResponse([
        'success' => true,
        'disponible' => ($resultado['total'] == 0)
    ]);
}

/**
 * Obtener horarios disponibles para una fecha y cancha
 */
function obtenerHorarios($db, $input) {
    $cancha_id = $input['cancha_id'] ?? null;
    $fecha_reserva = $input['fecha_reserva'] ?? null;
    
    if (!$cancha_id || !$fecha_reserva) {
        jsonResponse(['success' => false, 'message' => 'Datos incompletos']);
    }
    
    // Obtener horarios ocupados
    $sql = "SELECT hora_inicio FROM reservas 
            WHERE cancha_id = ? 
            AND fecha_reserva = ?
            AND estado IN ('pendiente', 'confirmada')";
    
    $reservados = $db->fetchAll($sql, [$cancha_id, $fecha_reserva]);
    $horariosOcupados = array_column($reservados, 'hora_inicio');
    
    // Horarios disponibles (9:00 a 23:00)
    $todosHorarios = [
        '09:00:00', '10:00:00', '11:00:00', '12:00:00', '13:00:00', 
        '14:00:00', '15:00:00', '16:00:00', '17:00:00', '18:00:00', 
        '19:00:00', '20:00:00', '21:00:00', '22:00:00', '23:00:00'
    ];
    
    $horariosDisponibles = array_diff($todosHorarios, $horariosOcupados);
    
    jsonResponse([
        'success' => true,
        'horarios_disponibles' => array_values($horariosDisponibles),
        'horarios_ocupados' => $horariosOcupados
    ]);
}

/**
 * Obtener reservas del usuario actual
 */
function obtenerMisReservas($db, $user) {
    $sql = "SELECT r.*, c.numero as cancha_numero, c.tipo as cancha_tipo
            FROM reservas r
            INNER JOIN canchas c ON r.cancha_id = c.id
            WHERE r.usuario_id = ?
            ORDER BY r.fecha_reserva DESC, r.hora_inicio DESC
            LIMIT 50";
    
    $reservas = $db->fetchAll($sql, [$user['id']]);
    
    jsonResponse([
        'success' => true,
        'reservas' => $reservas
    ]);
}

/**
 * Obtener todas las reservas (admin)
 */
function obtenerTodasReservas($db, $input) {
    $estado = $input['estado'] ?? 'todas';
    $fecha_desde = $input['fecha_desde'] ?? date('Y-m-d');
    $fecha_hasta = $input['fecha_hasta'] ?? date('Y-m-d', strtotime('+30 days'));
    
    $sql = "SELECT r.*, 
                   c.numero as cancha_numero, c.tipo as cancha_tipo,
                   u.nombre as usuario_nombre, u.email as usuario_email, u.telefono as usuario_telefono
            FROM reservas r
            INNER JOIN canchas c ON r.cancha_id = c.id
            INNER JOIN usuarios u ON r.usuario_id = u.id
            WHERE r.fecha_reserva BETWEEN ? AND ?";
    
    $params = [$fecha_desde, $fecha_hasta];
    
    if ($estado !== 'todas') {
        $sql .= " AND r.estado = ?";
        $params[] = $estado;
    }
    
    $sql .= " ORDER BY r.fecha_reserva DESC, r.hora_inicio DESC";
    
    $reservas = $db->fetchAll($sql, $params);
    
    jsonResponse([
        'success' => true,
        'reservas' => $reservas,
        'total' => count($reservas)
    ]);
}
?>