<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use League\OAuth2\Client\Provider\Google;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\OAuth;
use PHPMailer\PHPMailer\SMTP;

class GoogleAuthController extends Controller
{
    protected $provider;

    public function __construct()
    {
        // Inicializa el proveedor de Google OAuth2
        $this->provider = new Google([
            'clientId'     => env('GOOGLE_CLIENT_ID'),
            'clientSecret' => env('GOOGLE_CLIENT_SECRET'),
            'redirectUri'  => env('GOOGLE_REDIRECT_URI'),
        ]);
    }

    /**
     * Redirige al usuario a la página de autenticación de Google.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToGoogle()
    {
        // Define los alcances (scopes) necesarios. 'email' y 'profile' para datos básicos.
        // 'https://mail.google.com/' es crucial para enviar correos.
        $options = [
            'scope' => [
                'email',
                'profile',
                'https://mail.google.com/' // Permiso para enviar correos
            ],
            'access_type' => 'offline', // Solicita un refresh token
            'prompt' => 'consent'
        ];

        $authUrl = $this->provider->getAuthorizationUrl($options);

        // Guarda el 'state' para verificar la respuesta de Google
        session(['oauth2state' => $this->provider->getState()]);

        return redirect($authUrl);
    }

    /**
     * Maneja la respuesta de la autenticación de Google.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function handleGoogleCallback(Request $request)
    {
        // Verifica si hay un error en la respuesta de Google
        if ($request->has('error')) {
            return redirect('/')->with('error', 'Error en la autenticación: ' . $request->input('error'));
        }

        // Verifica el 'state' para prevenir ataques CSRF
        if (empty($request->input('state')) || ($request->input('state') !== session('oauth2state'))) {
            session()->forget('oauth2state');
            exit('Invalid state');
        }

        try {
            // Intenta obtener el token de acceso usando el código de autorización
            $accessToken = $this->provider->getAccessToken('authorization_code', [
                'code' => $request->input('code')
            ]);

            // En este punto, $accessToken contiene:
            // - Access Token (para acceder a los recursos del usuario)
            // - Refresh Token (para obtener un nuevo Access Token cuando expire el actual)
            // - Expires In (cuánto tiempo es válido el Access Token)
            // - User ID (ID del usuario)

            // Generalmente, querrás guardar el Refresh Token de forma segura en tu base de datos
            // asociado al usuario para poder enviar correos en el futuro sin que el usuario
            // tenga que re-autenticarse constantemente.

            // Para este ejemplo, almacenaremos el access token y refresh token en la sesión.
            // En un entorno real, ¡nunca los guardes en sesión para producción!
            // Usa una base de datos o un sistema de almacenamiento seguro.
            session([
                'google_access_token'  => $accessToken->getToken(),
                'google_refresh_token' => $accessToken->getRefreshToken(),
                'google_token_expires' => $accessToken->getExpires(),
            ]);

            // Ahora podemos usar PHPMailer para enviar un correo
            return $this->sendEmailWithPHPMailer();

        } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
            // Falló al obtener el token de acceso
            return redirect('/')->with('error', 'Error al obtener el token de acceso: ' . $e->getMessage());
        }
    }

    /**
     * Envía un correo electrónico usando PHPMailer con las credenciales de OAuth2.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendEmailWithPHPMailer()
    {
        $accessToken = session('google_access_token');
        $refreshToken = session('google_refresh_token');
        Log::info("Entre al envio de correo");
        if (!$accessToken) {
            return redirect('/')->with('error', 'No se encontró el token de acceso. Por favor, autentique con Google primero.');
        }

        $email = new PHPMailer(true);

        try {
            // Configuración del servidor SMTP para Gmail
            $email->isSMTP();
            $email->Host       = env('MAIL_HOST');
            $email->Port       = env('MAIL_PORT');
            $email->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // O PHPMailer::ENCRYPTION_SMTPS para 465

            // Habilitar depuración SMTP (útil para desarrollo)
            $email->SMTPDebug = SMTP::DEBUG_SERVER;

            // Habilitar autenticación SMTP con OAuth2
            $email->SMTPAuth = true;
            $email->AuthType = 'XOAUTH2';

            // Configurar el cliente OAuth2 para PHPMailer
            $email->setOAuth(
                new OAuth([
                    'provider'         => $this->provider,
                    'clientId'         => env('GOOGLE_CLIENT_ID'),
                    'clientSecret'     => env('GOOGLE_CLIENT_SECRET'),
                    'refreshToken'     => $refreshToken,
                    'accessToken'      => $accessToken,
                    'tokenExpires'     => session('google_token_expires'),
                    'userName'         => env('MAIL_FROM_ADDRESS'), // Correo del remitente de tu cuenta de Google
                ])
            );

            // Remitente y Destinatario
            $email->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            $email->addAddress('jvaronbueno@gmail.com', 'Javox Malkavian');
            $email->addReplyTo(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));

            // Contenido del correo
            $email->isHTML(true);
            $email->Subject = 'Asunto de prueba desde Laravel con OAuth2';
            $email->Body    = 'Hola, este es un <b>correo de prueba</b> enviado desde Laravel 10 usando PHPMailer y Google OAuth2.';
            $email->AltBody = 'Hola, este es un correo de prueba enviado desde Laravel 10 usando PHPMailer y Google OAuth2.';

            $email->send();
            return redirect('/')->with('success', 'Correo enviado exitosamente con PHPMailer y OAuth2!');

        } catch (Exception $e) {
            // Captura errores de PHPMailer
            return redirect('/')->with('error', 'Error al enviar el correo: ' . $email->ErrorInfo . ' | PHPMailer Exception: ' . $e->getMessage());
        }
    }
}
