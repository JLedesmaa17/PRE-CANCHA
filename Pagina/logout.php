<?php
/**
 * ============================================
 * PRE-CANCHA - Cerrar Sesión
 * logout.php
 * ============================================
 */

require_once 'config/database.php';
require_once 'includes/auth.php';

// Cerrar sesión
auth()->logout();

// Redirigir al inicio con mensaje
redirect('index.php?logout=success');
?>