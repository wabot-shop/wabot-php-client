<?php

class WabotApiClient
{
    private $clientId;
    private $clientSecret;
    private $accessToken;
    private $refreshToken;
    private $tokenExpiration;

    private $apiBaseUrl = 'https://api.wabot.shop/v1';

    public function __construct($clientId, $clientSecret)
    {
        if (!function_exists('curl_init')) {
            throw new Exception('cURL extension is required.');
        }

        $this->clientId     = $clientId;
        $this->clientSecret = $clientSecret;
    }

    // Authenticate and obtain access token
    public function authenticate()
    {
        $url = $this->apiBaseUrl . '/authenticate';

        $headers = [
            'clientId: ' . $this->clientId,
            'clientSecret: ' . $this->clientSecret,
        ];

        $response = $this->makeRequest('POST', $url, null, $headers);

        if (isset($response['token']) && isset($response['refreshToken'])) {
            $this->accessToken    = $response['token'];
            $this->refreshToken   = $response['refreshToken'];
            $this->tokenExpiration = $this->getTokenExpiration($this->accessToken);
            return true;
        }

        return false;
    }

    // Refresh access token using refresh token
    public function refreshToken()
    {
        $url = $this->apiBaseUrl . '/refreshToken';

        $headers = [
            'clientId: ' . $this->clientId,
            'clientSecret: ' . $this->clientSecret,
            'Content-Type: application/json',
        ];

        $body = json_encode(['refreshToken' => $this->refreshToken]);

        $response = $this->makeRequest('POST', $url, $body, $headers);

        if (isset($response['token']) && isset($response['refreshToken'])) {
            $this->accessToken    = $response['token'];
            $this->refreshToken   = $response['refreshToken'];
            $this->tokenExpiration = $this->getTokenExpiration($this->accessToken);
            return true;
        }

        return false;
    }

    // Get templates
    public function getTemplates()
    {
        $this->ensureAuthenticated();

        $url = $this->apiBaseUrl . '/get-templates';

        $headers = [
            'Authorization: ' . $this->accessToken,
        ];

        $response = $this->makeRequest('GET', $url, null, $headers);

        return $response['data'] ?? null;
    }

    // Send message
    public function sendMessage($to, $templateId, $params = [])
    {
        $this->ensureAuthenticated();

        $url = $this->apiBaseUrl . '/send-message';

        $headers = [
            'Authorization: ' . $this->accessToken,
            'Content-Type: application/json',
        ];

        $body = json_encode([
            'to'         => $to,
            'templateId' => $templateId,
            'params'     => $params,
        ]);

        $response = $this->makeRequest('POST', $url, $body, $headers);

        return $response;
    }

    // Logout
    public function logout()
    {
        $url = $this->apiBaseUrl . '/logout/' . urlencode($this->refreshToken);

        $headers = [
            'clientId: ' . $this->clientId,
            'clientSecret: ' . $this->clientSecret,
        ];

        $response = $this->makeRequest('DELETE', $url, null, $headers);

        // Clear tokens
        $this->accessToken    = null;
        $this->refreshToken   = null;
        $this->tokenExpiration = null;

        return true;
    }

    // Utility methods

    private function ensureAuthenticated()
    {
        if (!$this->accessToken || $this->isTokenExpired()) {
            if ($this->refreshToken) {
                $this->refreshToken();
            } else {
                $this->authenticate();
            }
        }

        if (!$this->accessToken) {
            throw new Exception('Unable to authenticate.');
        }
    }

    private function isTokenExpired()
    {
        return $this->tokenExpiration && time() >= $this->tokenExpiration;
    }

    private function getTokenExpiration($token)
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        $payload = json_decode(base64_decode($parts[1]), true);

        return $payload['exp'] ?? null;
    }

    private function makeRequest($method, $url, $body = null, $headers = [])
    {
        $curl = curl_init();

        $defaultHeaders = [
            'Accept: application/json',
        ];

        $headers = array_merge($defaultHeaders, $headers);

        curl_setopt_array($curl, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 30,
        ]);

        if ($body) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        }

        $response     = curl_exec($curl);
        $httpCode     = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError    = curl_error($curl);

        curl_close($curl);

        if ($curlError) {
            throw new Exception('Request Error: ' . $curlError);
        }

        $decodedResponse = json_decode($response, true);

        if ($httpCode >= 400) {
            $errorMessage = $decodedResponse['error'] ?? 'Unknown error';
            throw new Exception("API Error ({$httpCode}): {$errorMessage}");
        }

        return $decodedResponse;
    }
}
