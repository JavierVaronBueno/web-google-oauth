# 📧 Laravel Google OAuth2 Email Sender

[![Laravel](https://img.shields.io/badge/Laravel-10.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Gmail API](https://img.shields.io/badge/Gmail-API-blue.svg)](https://developers.google.com/gmail/api)

> Una aplicación Laravel robusta que implementa autenticación Google OAuth2 para enviar correos electrónicos a través de la Gmail API usando PHPMailer, con almacenamiento seguro de tokens y manejo automático de renovación.

## ✨ Características Principales

- 🔐 **Autenticación de Usuarios**: Registro, inicio de sesión y cierre de sesión usando Laravel Breeze.
- 🔐 **Autenticación OAuth2 de Google**: Autenticación segura con cuentas de Google para acceder a Gmail.
- 🗄️ **Almacenamiento Seguro**: Tokens de acceso y refresco almacenados en la tabla `google_tokens` en la base de datos.
- 🔄 **Renovación Automática**: Manejo automático de tokens expirados mediante el uso de `refresh_token`.
- 📧 **Envío de Correos**: Envío de correos electrónicos usando PHPMailer con autenticación OAuth2.
- 🏗️ **Patrón Service**: Arquitectura modular con servicios dedicados para autenticación y OAuth2.
- 📝 **Logging Completo**: Sistema de logs robusto para facilitar la depuración.
- 🎨 **Interfaz Responsiva**: Vistas Blade para login, registro y página principal con diseño simple y responsivo.
- ⚡ **Manejo de Errores**: Mensajes de error amigables para el usuario.
- 🎯 **PSR-12 Compliant**: Código limpio siguiendo estándares PHP.

## 🛠️ Requisitos del Sistema

| Componente | Versión Mínima |
|------------|----------------|
| PHP | 8.2+ |
| Laravel | 10.x |
| Composer | 2.x |
| MySQL/PostgreSQL | 5.7+ / 13+ |
| Node.js / npm | 16.x+ |

## 🚀 Instalación Rápida

### 1. Clonar el Repositorio
```bash
git clone https://github.com/JavierVaronBueno/web-google-oauth.git
cd web-google-oauth
```

### 2. Instalar Dependencias
```bash
composer install
npm install && npm run build
```

### 3. Configurar Entorno
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configurar Variables de Entorno
Edita el archivo `.env` con tus credenciales:

```env
# Base de datos
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_oauth_mailer
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña

# Google OAuth2
GOOGLE_CLIENT_ID=tu-google-client-id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=tu-google-client-secret
GOOGLE_REDIRECT_URI=http://localhost:8000/google/callback

# Configuración de correo
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=tu-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=tu-email@gmail.com
MAIL_FROM_NAME="Laravel OAuth Mailer"
```

### 5. Ejecutar Migraciones
```bash
php artisan migrate
```

### 6. Instalar Laravel Breeze
Instala Laravel Breeze para el scaffolding de autenticación:

```bash
composer require laravel/breeze:^1.29 --dev
php artisan breeze:install blade
npm install && npm run build
php artisan migrate
```
Selecciona el stack `blade` cuando se te solicite, ya que el proyecto usa vistas Blade para la interfaz.

#### Sobre Laravel Breeze
`laravel/breeze` proporciona un scaffolding de autenticación ligero para Laravel, incluyendo:
- Vistas Blade para login y registro (`resources/views/auth/`).
- Controladores para autenticación (`AuthController`).
- Rutas predefinidas para autenticación (`routes/web.php`).
- Migraciones para la tabla `users`.


## ⚙️ Configuración de Google Cloud Console

### Paso 1: Crear Proyecto
1. Ve a [Google Cloud Console](https://console.cloud.google.com)
2. Crea un nuevo proyecto o selecciona uno existente
3. Habilita la **Gmail API** en "APIs y Servicios" → "Biblioteca"

### Paso 2: Configurar OAuth
1. Ve a "APIs y Servicios" → "Credenciales"
2. Crea credenciales → "ID de cliente de OAuth 2.0"
3. Tipo de aplicación: **Aplicación web**
4. URI de redirección autorizados: `http://localhost:8000/google/callback`

### Paso 3: Pantalla de Consentimiento
```
Alcances requeridos:
- https://www.googleapis.com/auth/userinfo.email
- https://www.googleapis.com/auth/userinfo.profile
- https://mail.google.com/
```

## 🎯 Uso de la Aplicación

### Iniciar el Servidor
```bash
php artisan serve
```

### Flujo de Autenticación
1. Visita `http://localhost:8000`.
2. Regístrate en `/register` o inicia sesión en `/login`.
3. Haz clic en "Autenticar con Google" en la página principal.
4. Autoriza los permisos solicitados en la pantalla de consentimiento de Google.
5. Envía un correo de prueba desde la página principal.

### Estructura del Flujo
- Registro/Inicio de Sesión: Gestionado por Laravel Breeze (`AuthController`).
- Autenticación con Google: Redirige al usuario a Google y guarda los tokens en la tabla google_tokens (`GoogleAuthController` y `GoogleAuthService`).
- Envío de Correos: Usa PHPMailer con OAuth2 para enviar correos a través de Gmail (`GoogleAuthController`).



## 🏗️ Arquitectura del Proyecto

```
app/
├── Http/
│   └── Controllers/
│       ├── AuthController.php       # Controlador para autenticación de usuarios
│       └── GoogleAuthController.php # Controlador para Google OAuth2 y envío de correos
├── Models/
│   ├── User.php                     # Modelo para usuarios
│   └── GoogleToken.php              # Modelo para tokens OAuth2
├── Services/
│   ├── AuthService.php              # Lógica de autenticación de usuarios
│   └── GoogleAuthService.php        # Lógica de Google OAuth2
resources/
├── views/
│   ├── auth/
│   │   ├── login.blade.php          # Vista para inicio de sesión
│   │   └── register.blade.php       # Vista para registro
│   ├── layouts/
│   │   └── app.blade.php            # Plantilla base
│   └── welcome.blade.php            # Página principal
routes/
└── web.php                          # Definición de rutas
```

## 📊 Esquema de Base de Datos
### Tabla users (generada por Laravel Breeze)
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```
### Tabla sessions (para SESSION_DRIVER=database)
```sql
CREATE TABLE sessions (
    id VARCHAR(255) NOT NULL PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload TEXT NOT NULL,
    last_activity INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```
### Tabla `google_tokens`
```sql
CREATE TABLE google_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    access_token TEXT NOT NULL,
    refresh_token TEXT,
    expires_at TIMESTAMP NULL,
    token_type VARCHAR(50) DEFAULT 'Bearer',
    scope TEXT,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at)
);
```

## 🔧 Comandos Útiles

```bash
# Limpiar caché de aplicación
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Ver logs en tiempo real
tail -f storage/logs/laravel.log

# Ejecutar tests
php artisan test

# Generar documentación de API
php artisan route:list
```

## 🐛 Solución de Problemas

### Error: "Invalid state parameter"
```bash
# Limpiar sesiones
php artisan session:table
php artisan migrate
```

### Error: "Access denied"
- Verifica que el scope `https://mail.google.com/` esté configurado
- Confirma que el usuario tenga permisos de Gmail habilitados

### Error: "Redirect URI mismatch"
- Asegúrate de que `GOOGLE_REDIRECT_URI` coincida exactamente con Google Console
- Incluye el protocolo (`http://` o `https://`)

### Error: "SMTP Error: Could not authenticate"
- Causa: Tokens inválidos o configuración incorrecta de OAuth2.
- Solución:
    - Elimina los tokens existentes:
        ```sql
        DELETE FROM google_tokens WHERE user_id = (SELECT id FROM users WHERE email = 'tu-email@gmail.com');
        ```
    - Reautentica en `/auth/google`.
    - Habilita depuración SMTP en `GoogleAuthController`:
        ```php
        $email->SMTPDebug = SMTP::DEBUG_SERVER;
        ``` 
    - Revisa los logs en `storage/logs/laravel.log`.

### Debug de SMTP
```env
# Habilitar debug en .env
MAIL_DEBUG=true
LOG_LEVEL=debug
```

## 🔒 Consideraciones de Seguridad
- ✅ Usa **HTTPS** en producción para proteger los tokens OAuth2.
- ✅ Almacena tokens en la base de datos con cifrado (`access_token` y `refresh_token`).
- ✅ Valida el estado CSRF en el callback de Google OAuth2.
- ✅ Aplica sanitización de inputs en formularios.
- ✅ Configura rate limiting para rutas sensibles (`login`, `register`, `auth/google`).



### Variables de Entorno para Producción
```env
APP_ENV=production
APP_DEBUG=false
GOOGLE_REDIRECT_URI=https://tudominio.com/google/callback
```

## 📦 Dependencias Principales

```json
{
    "laravel/framework": "^10.0",
    "laravel/breeze": "^1.29",
    "league/oauth2-client": "^2.7",
    "phpmailer/phpmailer": "^6.8"
}
```

### Instalar dependencias específicas:
```bash
composer require league/oauth2-client phpmailer/phpmailer
```

## 🚀 Despliegue en Producción

### 1. Optimización
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer install --optimize-autoloader --no-dev
```

### 2. Cola de Trabajos (Recomendado)
```bash
# Configurar supervisor para colas
php artisan queue:work --daemon
```

### 3. Variables de Entorno
```env
QUEUE_CONNECTION=database
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

## 🤝 Contribuir

1. **Fork** el repositorio
2. Crea una rama feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit tus cambios (`git commit -am 'Añadir nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Crea un **Pull Request**

### Estándares de Contribución
- Sigue PSR-12 para el código PHP
- Incluye tests para nuevas funcionalidades
- Actualiza la documentación según sea necesario
- Asegúrate de que todos los tests pasen

## 📄 Licencia

Este proyecto está licenciado bajo la **Licencia MIT**. Ver el archivo [LICENSE](LICENSE) para más detalles.

## 🙏 Agradecimientos

- [Laravel](https://laravel.com) - Framework PHP
- [League OAuth2 Client](https://oauth2-client.thephpleague.com/) - Cliente OAuth2
- [PHPMailer](https://github.com/PHPMailer/PHPMailer) - Librería de email PHP
- [Google Gmail API](https://developers.google.com/gmail/api) - API de Gmail

## 📞 Soporte

¿Necesitas ayuda? 

- 📧 Email: jvaronbueno@gmial.com
- 🐛 Issues: [GitHub Issues](https://github.com/JavierVaronBueno/web-google-oauth/issues)
- 💬 Discusiones: [GitHub Discussions](https://github.com/JavierVaronBueno/web-google-oauth/discussions)

---

<div align="center">

**⭐ Si este proyecto te fue útil, no olvides darle una estrella en GitHub ⭐**

Made with ❤️ by [Javier Varon](https://github.com/JavierVaronBueno)

</div>
