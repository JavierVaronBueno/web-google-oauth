<?php

namespace App\Http\Controllers;

use App\Models\GoogleToken;
use App\Services\GoogleAuthService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\OAuth;
use PHPMailer\PHPMailer\SMTP;

class GoogleAuthController extends Controller
{
    /**
     * The Google authentication service.
     *
     * @var GoogleAuthService
     */
    protected $googleAuthService;

    /**
     * Create a new controller instance.
     *
     * @param GoogleAuthService $googleAuthService
     */
    public function __construct(GoogleAuthService $googleAuthService)
    {
        $this->googleAuthService = $googleAuthService;
    }

    /**
     * Redirect the user to the Google authentication page.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToGoogle()
    {
        try {
            return redirect($this->googleAuthService->getAuthorizationUrl());
        } catch (\Exception $e) {
            Log::error('Failed to generate Google authorization URL: ' . $e->getMessage());
            return redirect('/')->with('error', 'Error al iniciar la autenticación con Google.');
        }
    }

    /**
     * Handle the Google OAuth2 callback.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                throw new \Exception('User not authenticated');
            }

            $token = $this->googleAuthService->handleCallback(
                $request->input('code'),
                $request->input('state'),
                $user
            );

            return $this->sendEmailWithPHPMailer($user, $token);
        } catch (\Exception $e) {
            Log::error('Google callback error: ' . $e->getMessage());
            return redirect('/')->with('error', $e->getMessage());
        }
    }

    /**
     * Send an email using PHPMailer with Google OAuth2 credentials.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendEmailWithPHPMailer()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                throw new \Exception('User not authenticated');
            }

            $token = $user->googleToken;
            if (!$token) {
                throw new \Exception('No Google token found for the user');
            }

            if ($token->expires_at->isPast() && $token->refresh_token) {
                Log::info('Access token expired, attempting to refresh for user: ' . $user->email);
                $token = $this->googleAuthService->refreshAccessToken($token);
            }

            $email = new PHPMailer(true);

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
            if (!$this->googleAuthService->getProvider()) {
                throw new \Exception('OAuth provider not configured');
            }
            $email->setOAuth(
                new OAuth([
                    'provider'         => $this->googleAuthService->getProvider(),
                    'clientId'         => env('GOOGLE_CLIENT_ID'),
                    'clientSecret'     => env('GOOGLE_CLIENT_SECRET'),
                    'refreshToken'     => $token->refresh_token,
                    'accessToken'      => $token->access_token,
                    'tokenExpires'     => $token->expires_at->timestamp,
                    'userName'         => env('MAIL_FROM_ADDRESS'), // Correo del remitente de tu cuenta de Google
                ])
            );

            // Remitente y Destinatario
            $email->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            $email->addAddress($user->email, $user->name);
            $email->addReplyTo(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));

            // Contenido del correo
            $email->isHTML(true);
            $email->Subject = 'Prueba de correo desde Laravel con OAuth2';
            $email->Body = 'Hola, este es un <b>correo de prueba</b> enviado desde Laravel usando PHPMailer y Google OAuth2.';
            $email->AltBody = 'Hola, este es un correo de prueba enviado desde Laravel usando PHPMailer y Google OAuth2.';

            Log::info('Attempting to send email for user: ' . $user->email);
            $email->send();
            Log::info('Email sent successfully for user: ' . $user->email);

            return redirect()->route('home')->with('success', 'Correo enviado exitosamente con PHPMailer y OAuth2!');
        } catch (Exception $e) {
            Log::error('Failed to send email: ' . $e->getMessage() . ' | PHPMailer ErrorInfo: ' . $email->ErrorInfo);
            return redirect()->route('home')->with('error', 'Error al enviar el correo: ' . $e->getMessage() . ' | PHPMailer ErrorInfo: ' . $email->ErrorInfo);
        }
    }
}
