<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel Google OAuth2 PHPMailer</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; flex-direction: column; }
        .alert { padding: 10px; margin-bottom: 10px; border-radius: 5px; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        a { padding: 10px 20px; background-color: #4285F4; color: white; text-decoration: none; border-radius: 5px; }
        a:hover { background-color: #357ae8; }
    </style>
</head>
<body>
    <h1>Laravel Google OAuth2 PHPMailer</h1>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

    @if (!session('google_access_token'))
        <p>Haz clic para autenticarte con Google y enviar un correo de prueba:</p>
        <a href="{{ route('auth.google') }}">Autenticar con Google</a>
    @else
        <p>¡Ya estás autenticado con Google! Un correo de prueba debería haberse enviado.</p>
        <p>Si no se envió, revisa tu consola de depuración y las configuraciones.</p>
        <p>Los tokens de acceso y refresco están en la sesión (solo para demostración).</p>
        {{-- <a href="/send-email">Enviar otro correo de prueba</a> --}}
    @endif
</body>
</html>
