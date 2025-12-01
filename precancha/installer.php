<?php
/**
 * ============================================
 * PRE-CANCHA - Instalador Automático
 * install.php
 * ============================================
 * IMPORTANTE: Eliminar este archivo después de la instalación
 */

$errors = [];
$success = false;
$step = $_GET['step'] ?? 1;

// Verificar que no esté ya instalado
if (file_exists('config/installed.lock') && $step == 1) {
    die('⚠️ El sistema ya está instalado. Si deseas reinstalar, elimina el archivo config/installed.lock');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step == 2) {
        // Guardar configuración de base de datos
        $host = $_POST['db_host'] ?? 'localhost';
        $dbname = $_POST['db_name'] ?? 'precancha';
        $username = $_POST['db_user'] ?? 'root';
        $password = $_POST['db_pass'] ?? '';
        
        // Probar conexión
        try {
            $pdo = new PDO("mysql:host=$host", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Crear base de datos si no existe
            $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE $dbname");
            
            // Leer y ejecutar schema.sql
            $sql = file_get_contents('sql/schema.sql');
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            
            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    $pdo->exec($statement);
                }
            }
            
            // Actualizar archivo de configuración
            $configContent = file_get_contents('config/database.php');
            $configContent = str_replace("private \$host = 'localhost';", "private \$host = '$host';", $configContent);
            $configContent = str_replace("private \$dbname = 'precancha';", "private \$dbname = '$dbname';", $configContent);
            $configContent = str_replace("private \$username = 'root';", "private \$username = '$username';", $configContent);
            $configContent = str_replace("private \$password = '';", "private \$password = '$password';", $configContent);
            file_put_contents('config/database.php', $configContent);
            
            // Crear archivo de bloqueo
            file_put_contents('config/installed.lock', date('Y-m-d H:i:s'));
            
            $success = true;
            header('Location: install.php?step=3');
            exit;
            
        } catch (PDOException $e) {
            $errors[] = "Error de conexión: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación - PRE-CANCHA</title>
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
            --color-danger: #ff4444;
            --color-success: #00ff00;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--color-bg-dark) 0%, var(--color-bg-light) 100%);
            color: var(--color-text);
            min-height: 100vh;
            padding: 40px 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo {
            font-size: 72px;
            margin-bottom: 20px;
        }

        .title {
            font-size: 42px;
            font-weight: bold;
            background: linear-gradient(90deg, var(--color-primary), var(--color-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .subtitle {
            color: var(--color-text-muted);
            margin-top: 10px;
        }

        .card {
            background: linear-gradient(145deg, var(--color-bg-light), #0d0d0d);
            border: 2px solid var(--color-primary);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
        }

        .steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            gap: 20px;
        }

        .step {
            flex: 1;
            text-align: center;
            padding: 20px;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 10px;
            border: 2px solid rgba(218, 165, 32, 0.2);
        }

        .step.active {
            border-color: var(--color-secondary);
            background: rgba(218, 165, 32, 0.1);
        }

        .step.completed {
            border-color: var(--color-success);
            background: rgba(0, 255, 0, 0.05);
        }

        .step-number {
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

        .step.completed .step-number {
            background: var(--color-success);
        }

        h2 {
            color: var(--color-secondary);
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            color: var(--color-secondary);
            font-weight: 600;
            margin-bottom: 10px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 15px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(218, 165, 32, 0.3);
            border-radius: 10px;
            color: var(--color-text);
            font-size: 16px;
        }

        input:focus {
            outline: none;
            border-color: var(--color-secondary);
            background: rgba(218, 165, 32, 0.1);
        }

        .btn {
            padding: 15px 40px;
            background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
            color: var(--color-bg-dark);
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(218, 165, 32, 0.5);
        }

        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .alert-danger {
            background: rgba(255, 68, 68, 0.2);
            border: 1px solid var(--color-danger);
            color: var(--color-danger);
        }

        .alert-success {
            background: rgba(0, 255, 0, 0.2);
            border: 1px solid var(--color-success);
            color: var(--color-success);
        }

        .info-box {
            background: rgba(218, 165, 32, 0.1);
            border: 1px solid var(--color-primary);
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }

        .info-box ul {
            margin-left: 20px;
            margin-top: 10px;
        }

        .info-box li {
            margin: 8px 0;
        }

        @media (max-width: 768px) {
            .steps {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">⚽</div>
            <div class="title">PRE-CANCHA</div>
            <div class="subtitle">Instalación del Sistema</div>
        </div>

        <div class="steps">
            <div class="step <?= $step == 1 ? 'active' : ($step > 1 ? 'completed' : '') ?>">
                <div class="step-number"><?= $step > 1 ? '✓' : '1' ?></div>
                <div>Bienvenida</div>
            </div>
            <div class="step <?= $step == 2 ? 'active' : ($step > 2 ? 'completed' : '') ?>">
                <div class="step-number"><?= $step > 2 ? '✓' : '2' ?></div>
                <div>Base de Datos</div>
            </div>
            <div class="step <?= $step == 3 ? 'active' : '' ?>">
                <div class="step-number">3</div>
                <div>Finalizar</div>
            </div>
        </div>

        <?php if ($step == 1): ?>
            <div class="card">
                <h2>🎉 Bienvenido al Instalador de PRE-CANCHA</h2>
                <p style="margin-bottom: 20px;">
                    Este asistente te ayudará a instalar y configurar el sistema de gestión de reservas.
                </p>

                <div class="info-box">
                    <strong>📋 Requisitos del Sistema:</strong>
                    <ul>
                        <li>✅ PHP 7.4 o superior</li>
                        <li>✅ MySQL 5.7 o superior</li>
                        <li>✅ Extensión PDO habilitada</li>
                        <li>✅ Permisos de escritura en carpetas</li>
                    </ul>
                </div>

                <div class="info-box">
                    <strong>📝 Información que necesitarás:</strong>
                    <ul>
                        <li>Host de la base de datos (generalmente "localhost")</li>
                        <li>Nombre de la base de datos</li>
                        <li>Usuario de MySQL</li>
                        <li>Contraseña de MySQL</li>
                    </ul>
                </div>

                <a href="install.php?step=2" class="btn">Comenzar Instalación →</a>
            </div>
        <?php endif; ?>

        <?php if ($step == 2): ?>
            <div class="card">
                <h2>🗄️ Configuración de Base de Datos</h2>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            ⚠️ <?= htmlspecialchars($error) ?><br>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label>Host de Base de Datos</label>
                        <input type="text" name="db_host" value="localhost" required>
                    </div>

                    <div class="form-group">
                        <label>Nombre de Base de Datos</label>
                        <input type="text" name="db_name" value="precancha" required>
                        <small style="color: var(--color-text-muted);">
                            Si no existe, se creará automáticamente
                        </small>
                    </div>

                    <div class="form-group">
                        <label>Usuario de MySQL</label>
                        <input type="text" name="db_user" value="root" required>
                    </div>

                    <div class="form-group">
                        <label>Contraseña de MySQL</label>
                        <input type="password" name="db_pass">
                        <small style="color: var(--color-text-muted);">
                            Déjalo vacío si no tienes contraseña
                        </small>
                    </div>

                    <button type="submit" class="btn">Instalar Sistema</button>
                </form>
            </div>
        <?php endif; ?>

        <?php if ($step == 3): ?>
            <div class="card">
                <h2>✅ ¡Instalación Completada!</h2>

                <div class="alert alert-success">
                    🎉 El sistema PRE-CANCHA ha sido instalado exitosamente.
                </div>

                <div class="info-box">
                    <strong>👤 Credenciales de Administrador:</strong>
                    <ul>
                        <li><strong>Email:</strong> admin@precancha.com</li>
                        <li><strong>Contraseña:</strong> admin123</li>
                    </ul>
                    <p style="color: var(--color-danger); margin-top: 15px;">
                        ⚠️ <strong>IMPORTANTE:</strong> Cambia esta contraseña inmediatamente después del primer inicio de sesión.
                    </p>
                </div>

                <div class="info-box">
                    <strong>🔒 Recomendaciones de Seguridad:</strong>
                    <ul>
                        <li>🗑️ Elimina el archivo <code>install.php</code></li>
                        <li>🔑 Cambia la contraseña del administrador</li>
                        <li>🔐 Configura HTTPS si tienes certificado SSL</li>
                        <li>📝 Revisa los permisos de archivos</li>
                    </ul>
                </div>

                <div style="margin-top: 30px; text-align: center;">
                    <a href="index.php" class="btn">Ir al Sistema →</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>