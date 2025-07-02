# 📧 Laravel Google OAuth2 Email Sender

[![Laravel](https://img.shields.io/badge/Laravel-10.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Gmail API](https://img.shields.io/badge/Gmail-API-blue.svg)](https://developers.google.com/gmail/api)

> Una aplicación Laravel robusta que implementa autenticación Google OAuth2 para enviar correos electrónicos a través de la Gmail API usando PHPMailer, con almacenamiento seguro de tokens y manejo automático de renovación.

## ✨ Características Principales

- 🔐 **Autenticación OAuth2 de Google** - Autenticación segura con cuentas de Google
- 🗄️ **Almacenamiento Seguro** - Tokens almacenados en base de datos con cifrado
- 🔄 **Renovación Automática** - Manejo automático de tokens expirados
- 🏗️ **Patrón Service** - Arquitectura modular y mantenible
- 📝 **Logging Completo** - Sistema de logs robusto para debugging
- 🎯 **PSR-12 Compliant** - Código limpio siguiendo estándares PHP
- ⚡ **Manejo de Errores** - Mensajes de error amigables para el usuario

## 🛠️ Requisitos del Sistema

| Componente | Versión Mínima |
|------------|----------------|
| PHP | 8.2+ |
| Laravel | 10.x |
| Composer | 2.x |
| MySQL/PostgreSQL | 5.7+ / 13+ |

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
1. Visita `http://localhost:8000`
2. Inicia sesión con tu cuenta Laravel
3. Haz clic en "Autenticar con Google"
4. Autoriza los permisos solicitados
5. ¡Envía emails desde la interfaz!

## 🏗️ Arquitectura del Proyecto

```
app/
├── Http/Controllers/
│   └── GoogleAuthController.php     # Controlador principal OAuth2
├── Services/
│   └── GoogleAuthService.php       # Lógica de negocio OAuth2
├── Models/
│   └── GoogleToken.php             # Modelo Eloquent para tokens
└── Mail/
    └── TestEmail.php               # Clase de correo de prueba

resources/views/
├── welcome.blade.php               # Vista principal
└── emails/
    └── test.blade.php             # Plantilla de email

routes/
└── web.php                        # Definición de rutas
```

## 📊 Esquema de Base de Datos

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

### Debug de SMTP
```env
# Habilitar debug en .env
MAIL_DEBUG=true
LOG_LEVEL=debug
```

## 🔒 Consideraciones de Seguridad

- ✅ Usa **HTTPS** en producción
- ✅ Tokens almacenados de forma segura en BD
- ✅ Validación de estado CSRF
- ✅ Sanitización de inputs
- ✅ Rate limiting en rutas sensibles

### Variables de Entorno para Producción
```env
APP_ENV=production
APP_DEBUG=false
GOOGLE_REDIRECT_URI=https://tudominio.com/google/callback
```

## 📦 Dependencias Principales

```json
{
    "league/oauth2-client": "^2.7",
    "phpmailer/phpmailer": "^6.8",
    "laravel/framework": "^10.0"
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
