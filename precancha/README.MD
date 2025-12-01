# ⚽ PRE-CANCHA - Sistema de Gestión de Reservas

Sistema digital centralizado para la gestión y reserva de canchas de fútbol, diseñado para optimizar la organización de reservas, horarios disponibles y la comunicación entre usuarios y administradores.

## 🎯 Características Principales

### Para Usuarios
- ✅ Registro e inicio de sesión
- 📅 Visualización de canchas disponibles
- ⏰ Calendario interactivo de reservas
- 🎫 Gestión de mis reservas
- 💳 Consulta de precios en tiempo real

### Para Administradores
- 📊 Panel de administración completo
- ✅ Aprobar/Rechazar solicitudes de reserva
- 🔄 Sugerencias automáticas de horarios alternativos
- 📈 Historial de actividades
- 👥 Gestión de usuarios

## 🚀 Instalación

### Requisitos Previos
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Servidor web (Apache/Nginx)
- Extensiones PHP: PDO, pdo_mysql

### Paso 1: Clonar/Descargar el Proyecto
```bash
# Si usas Git
git clone [URL_DEL_REPOSITORIO]

# O descarga el ZIP y extrae en tu servidor web
```

### Paso 2: Configurar la Base de Datos

1. Crear la base de datos:
```sql
CREATE DATABASE precancha CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Importar el esquema:
```bash
mysql -u root -p precancha < Pagina/sql/schema.sql
```

O desde phpMyAdmin:
- Selecciona la base de datos `precancha`
- Ve a "Importar"
- Selecciona el archivo `Pagina/sql/schema.sql`
- Haz clic en "Continuar"

### Paso 3: Configurar la Conexión

Edita el archivo `Pagina/config/database.php`:

```php
private $host = 'localhost';      // Tu host de MySQL
private $dbname = 'precancha';    // Nombre de la base de datos
private $username = 'root';        // Tu usuario de MySQL
private $password = '';            // Tu contraseña de MySQL
```

### Paso 4: Configurar el Servidor Web

#### Apache (.htaccess)
```apache
RewriteEngine On
RewriteBase /

# Redirigir todo a index.php si el archivo no existe
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

#### Nginx
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### Paso 5: Permisos (Linux/Mac)
```bash
chmod -R 755 Pagina/
chmod -R 777 Pagina/uploads/  # Si tienes carpeta de uploads
```

## 👤 Usuarios por Defecto

Después de importar la base de datos, tendrás un usuario administrador:

**Administrador:**
- Email: `admin@precancha.com`
- Contraseña: `admin123`

⚠️ **IMPORTANTE**: Cambia esta contraseña inmediatamente después del primer inicio de sesión.

## 📁 Estructura del Proyecto

```
Pagina/
├── admin/              # Panel de administración
│   └── index.php
├── api/                # Endpoints de la API
│   └── reservas.php
├── config/             # Configuraciones
│   └── database.php    # Conexión a BD
├── includes/           # Archivos auxiliares
│   └── auth.php        # Sistema de autenticación
├── sql/                # Scripts SQL
│   └── schema.sql      # Esquema de la base de datos
├── usuario/            # Panel de usuario
│   └── mis-reservas.php
├── index.php           # Página principal
├── login.php           # Inicio de sesión
├── registro.php        # Registro de usuarios
├── reservas.php        # Sistema de reservas
├── logout.php          # Cerrar sesión
└── README.md           # Este archivo
```

## 🔧 Configuración Adicional

### Configurar Email (Opcional)
Para habilitar notificaciones por email, edita la tabla `configuracion`:

```sql
UPDATE configuracion 
SET valor = 'true' 
WHERE clave = 'notificaciones_email';
```

### Agregar Nuevas Canchas
Desde phpMyAdmin o tu cliente MySQL:

```sql
INSERT INTO canchas (numero, tipo, jugadores, superficie, precio_hora) 
VALUES (6, 'Fútbol 7', 14, 'Césped Natural', 7000.00);
```

## 📖 Uso del Sistema

### Como Usuario

1. **Registrarse**: Ve a `registro.php` y crea tu cuenta
2. **Iniciar Sesión**: Usa tus credenciales en `login.php`
3. **Reservar**: 
   - Navega a "Canchas" desde el menú
   - Selecciona una cancha
   - Elige fecha y horario
   - Completa tus datos
   - Envía la solicitud

4. **Mis Reservas**: Consulta el estado de tus reservas en tu panel de usuario

### Como Administrador

1. **Acceder al Panel**: Inicia sesión con credenciales de admin
2. **Gestionar Reservas**: 
   - Ve al panel de administración
   - Revisa solicitudes pendientes
   - Aprueba o rechaza con motivo
   - El sistema sugiere horarios alternativos automáticamente

## 🔒 Seguridad

- ✅ Contraseñas hasheadas con `password_hash()`
- ✅ Prepared statements para prevenir SQL injection
- ✅ Validación de sesiones
- ✅ Protección CSRF básica
- ✅ Sanitización de datos de entrada

## 🐛 Solución de Problemas

### Error de Conexión a la Base de Datos
```
Error: Could not connect to database
```
**Solución**: Verifica las credenciales en `config/database.php`

### Página en Blanco
**Solución**: Habilita el display de errores en PHP:
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

### Sesiones no Funcionan
**Solución**: Verifica que `session_start()` se llame al inicio y que la carpeta de sesiones tenga permisos de escritura.

## 📊 Base de Datos

### Tablas Principales

- **usuarios**: Información de usuarios y administradores
- **canchas**: Catálogo de canchas disponibles
- **reservas**: Todas las reservas del sistema
- **horarios_disponibles**: Horarios de operación
- **bloqueos**: Fechas/horarios bloqueados
- **historial_acciones**: Auditoría del sistema
- **configuracion**: Parámetros del sistema

### Backup de la Base de Datos

**Crear backup:**
```bash
mysqldump -u root -p precancha > backup_precancha_$(date +%Y%m%d).sql
```

**Restaurar backup:**
```bash
mysql -u root -p precancha < backup_precancha_20240101.sql
```

## 🔄 Actualizaciones Futuras

Funcionalidades planeadas para futuras versiones:
- [ ] Integración con pagos online (Mercado Pago, Stripe)
- [ ] Notificaciones por WhatsApp
- [ ] App móvil nativa
- [ ] Sistema de puntos y descuentos
- [ ] Reservas recurrentes
- [ ] Reportes y estadísticas avanzadas

## 📞 Soporte

Para consultas o problemas:
- Email: info@precancha.com
- Teléfono: +54 11 1234-5678

## 📄 Licencia

Este proyecto fue desarrollado para [NOMBRE DEL CLIENTE].
Todos los derechos reservados © 2024

## 👨‍💻 Desarrollador

Desarrollado con ❤️ por [Julián]
Contacto: [trollledes6@email.com]

---

**Última actualización**: Diciembre 2024
**Versión**: 1.0.0