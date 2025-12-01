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
        📅 <strong>Fecha:</strong> <?= date('d/m/Y', strtotime($reserva['fecha_reserva'])) ?>
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