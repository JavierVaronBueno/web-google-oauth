<?php

namespace App\Services;

use App\Models\GoogleToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Exception;

class GoogleAuthService
{
    /**
     * The Google OAuth2 provider.
     *
     * @var Google
     */
    protected $provider;

    /**
     * Create a new service instance.
     */
    public function __construct()
    {
        $this->provider = new Google([
            'clientId'     => config('services.google.client_id'),
            'clientSecret' => config('services.google.client_secret'),
            'redirectUri'  => config('services.google.redirect_uri'),
        ]);
    }

    /**
     * Generate the Google authorization URL.
     *
     * @return string
     */
    public function getAuthorizationUrl(): string
    {
        $options = [
            'scope' => [
                'email',
                'profile',
                'https://mail.google.com/',
            ],
            'access_type' => 'offline',
            'prompt' => 'consent',
        ];

        $authUrl = $this->provider->getAuthorizationUrl($options);
        session(['oauth2state' => $this->provider->getState()]);

        return $authUrl;
    }

    /**
     * Handle the Google OAuth2 callback and store tokens.
     *
     * @param string $code
     * @param string $state
     * @param User $user
     * @return GoogleToken
     * @throws Exception
     */
    public function handleCallback(string $code, string $state, User $user): GoogleToken
    {
        if ($state !== session('oauth2state')) {
            session()->forget('oauth2state');
            throw new Exception('Invalid state parameter');
        }

        try {
            $accessToken = $this->provider->getAccessToken('authorization_code', [
                'code' => $code,
            ]);

            return $this->storeToken($user, $accessToken);
        } catch (IdentityProviderException $e) {
            Log::error('Failed to obtain access token: ' . $e->getMessage());
            throw new Exception('Failed to obtain access token: ' . $e->getMessage());
        }
    }

    /**
     * Store or update the Google OAuth2 token for the user.
     *
     * @param User $user
     * @param mixed $accessToken
     * @return GoogleToken
     */
    protected function storeToken(User $user, $accessToken): GoogleToken
    {
        try {
            // Convertir el timestamp Unix (segundos desde 1970) a una fecha válida
            $expiresAt = Carbon::createFromTimestamp($accessToken->getExpires());
            $refreshToken = $accessToken->getRefreshToken();

            if (!$refreshToken) {
                Log::warning('No refresh token received for user: ' . $user->email);
            }

            Log::info('Storing token for user: ' . $user->id, [
                'expires_at' => $expiresAt,
                'refresh_token' => $refreshToken ? substr($refreshToken, 0, 10) . '...' : null,
            ]);

            // Evitar sobrescribir un refresh_token existente con null
            $existingToken = GoogleToken::where('user_id', $user->id)->first();
            $data = [
                'access_token' => $accessToken->getToken(),
                'expires_at' => $expiresAt,
            ];

            if ($refreshToken) {
                $data['refresh_token'] = $refreshToken;
            } elseif ($existingToken && $existingToken->refresh_token) {
                $data['refresh_token'] = $existingToken->refresh_token; // Preservar refresh_token existente
            }

            return GoogleToken::updateOrCreate(
                ['user_id' => $user->id],
                $data
            );
        } catch (Exception $e) {
            Log::error('Failed to store token: ' . $e->getMessage());
            throw new Exception('Error al almacenar el token: ' . $e->getMessage());
        }
    }

    /**
     * Refresh the access token using the refresh token.
     *
     * @param GoogleToken $token
     * @return GoogleToken
     * @throws Exception
     */
    public function refreshAccessToken(GoogleToken $token): GoogleToken
    {
        try {
            $newAccessToken = $this->provider->getAccessToken('refresh_token', [
                'refresh_token' => $token->refresh_token,
            ]);

            return $this->storeToken($token->user, $newAccessToken);
        } catch (IdentityProviderException $e) {
            Log::error('Failed to refresh access token: ' . $e->getMessage());
            throw new Exception('Failed to refresh access token: ' . $e->getMessage());
        }
    }

    /**
     * Get the Google OAuth2 provider.
     *
     * @return Google
     */
    public function getProvider(): Google
    {
        return $this->provider;
    }
}
