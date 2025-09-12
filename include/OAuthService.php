<?php
/**
 * OAuth Service Class
 * Handles OAuth2/SSO integration for Google, LinkedIn, Microsoft, Apple, X.com
 */

class OAuthService {
    private $db;
    private $config;
    private $providers = [];
    
    public function __construct($database) {
        $this->db = $database;
        $this->loadConfig();
        $this->initializeProviders();
    }
    
    /**
     * Load OAuth configuration
     */
    private function loadConfig() {
        try {
            $settings = $this->db->fetchAll("SELECT setting_key, setting_value FROM system_settings WHERE category = 'oauth'");
            foreach ($settings as $setting) {
                $this->config[$setting['setting_key']] = $setting['setting_value'];
            }
        } catch (Exception $e) {
            error_log("Error loading OAuth config: " . $e->getMessage());
        }
    }
    
    /**
     * Initialize OAuth providers
     */
    private function initializeProviders() {
        $this->providers = [
            'google' => [
                'client_id' => $this->config['google_client_id'] ?? '',
                'client_secret' => $this->config['google_client_secret'] ?? '',
                'redirect_uri' => (defined('SITE_URL') ? SITE_URL : 'http://localhost') . '/oauth/callback/google',
                'scope' => 'openid email profile',
                'auth_url' => 'https://accounts.google.com/o/oauth2/v2/auth',
                'token_url' => 'https://oauth2.googleapis.com/token',
                'user_info_url' => 'https://www.googleapis.com/oauth2/v2/userinfo'
            ],
            'linkedin' => [
                'client_id' => $this->config['linkedin_client_id'] ?? '',
                'client_secret' => $this->config['linkedin_client_secret'] ?? '',
                'redirect_uri' => (defined('SITE_URL') ? SITE_URL : 'http://localhost') . '/oauth/callback/linkedin',
                'scope' => 'r_liteprofile r_emailaddress',
                'auth_url' => 'https://www.linkedin.com/oauth/v2/authorization',
                'token_url' => 'https://www.linkedin.com/oauth/v2/accessToken',
                'user_info_url' => 'https://api.linkedin.com/v2/people/~:(id,firstName,lastName,emailAddress)'
            ],
            'microsoft' => [
                'client_id' => $this->config['microsoft_client_id'] ?? '',
                'client_secret' => $this->config['microsoft_client_secret'] ?? '',
                'redirect_uri' => (defined('SITE_URL') ? SITE_URL : 'http://localhost') . '/oauth/callback/microsoft',
                'scope' => 'openid email profile',
                'auth_url' => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
                'token_url' => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
                'user_info_url' => 'https://graph.microsoft.com/v1.0/me'
            ],
            'apple' => [
                'client_id' => $this->config['apple_client_id'] ?? '',
                'client_secret' => $this->config['apple_client_secret'] ?? '',
                'redirect_uri' => (defined('SITE_URL') ? SITE_URL : 'http://localhost') . '/oauth/callback/apple',
                'scope' => 'name email',
                'auth_url' => 'https://appleid.apple.com/auth/authorize',
                'token_url' => 'https://appleid.apple.com/auth/token',
                'user_info_url' => null // Apple provides user info in ID token
            ],
            'x' => [
                'client_id' => $this->config['x_client_id'] ?? '',
                'client_secret' => $this->config['x_client_secret'] ?? '',
                'redirect_uri' => (defined('SITE_URL') ? SITE_URL : 'http://localhost') . '/oauth/callback/x',
                'scope' => 'tweet.read users.read',
                'auth_url' => 'https://twitter.com/i/oauth2/authorize',
                'token_url' => 'https://api.twitter.com/2/oauth2/token',
                'user_info_url' => 'https://api.twitter.com/2/users/me'
            ]
        ];
    }
    
    /**
     * Get OAuth authorization URL
     */
    public function getAuthUrl($provider) {
        if (!isset($this->providers[$provider])) {
            throw new Exception("Unsupported OAuth provider: $provider");
        }
        
        $config = $this->providers[$provider];
        
        // Generate state parameter for security
        $state = bin2hex(random_bytes(32));
        $_SESSION['oauth_state'] = $state;
        $_SESSION['oauth_provider'] = $provider;
        
        $params = [
            'client_id' => $config['client_id'],
            'redirect_uri' => $config['redirect_uri'],
            'scope' => $config['scope'],
            'response_type' => 'code',
            'state' => $state
        ];
        
        // Add provider-specific parameters
        if ($provider === 'apple') {
            $params['response_mode'] = 'form_post';
        }
        
        return $config['auth_url'] . '?' . http_build_query($params);
    }
    
    /**
     * Handle OAuth callback
     */
    public function handleCallback($provider, $code, $state) {
        // Verify state parameter
        if (!isset($_SESSION['oauth_state']) || $_SESSION['oauth_state'] !== $state) {
            throw new Exception("Invalid state parameter");
        }
        
        if (!isset($this->providers[$provider])) {
            throw new Exception("Unsupported OAuth provider: $provider");
        }
        
        $config = $this->providers[$provider];
        
        // Exchange code for access token
        $tokenData = $this->exchangeCodeForToken($provider, $code, $config);
        
        // Get user information
        $userInfo = $this->getUserInfo($provider, $tokenData, $config);
        
        // Process user login/registration
        return $this->processOAuthUser($provider, $userInfo);
    }
    
    /**
     * Exchange authorization code for access token
     */
    private function exchangeCodeForToken($provider, $code, $config) {
        $postData = [
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $config['redirect_uri']
        ];
        
        // Provider-specific adjustments
        if ($provider === 'apple') {
            $postData['client_secret'] = $this->generateAppleClientSecret($config);
        }
        
        $response = $this->makeHttpRequest($config['token_url'], $postData);
        
        if (!$response || !isset($response['access_token'])) {
            throw new Exception("Failed to obtain access token from $provider");
        }
        
        return $response;
    }
    
    /**
     * Get user information from OAuth provider
     */
    private function getUserInfo($provider, $tokenData, $config) {
        if ($provider === 'apple') {
            // Apple provides user info in ID token
            return $this->parseAppleIdToken($tokenData['id_token']);
        }
        
        if (!$config['user_info_url']) {
            throw new Exception("No user info URL configured for $provider");
        }
        
        $headers = [
            'Authorization: Bearer ' . $tokenData['access_token']
        ];
        
        $response = $this->makeHttpRequest($config['user_info_url'], null, $headers);
        
        if (!$response) {
            throw new Exception("Failed to get user info from $provider");
        }
        
        return $this->normalizeUserInfo($provider, $response);
    }
    
    /**
     * Normalize user info from different providers
     */
    private function normalizeUserInfo($provider, $userInfo) {
        $normalized = [
            'provider' => $provider,
            'provider_id' => '',
            'email' => '',
            'first_name' => '',
            'last_name' => '',
            'name' => '',
            'avatar' => ''
        ];
        
        switch ($provider) {
            case 'google':
                $normalized['provider_id'] = $userInfo['id'] ?? '';
                $normalized['email'] = $userInfo['email'] ?? '';
                $normalized['first_name'] = $userInfo['given_name'] ?? '';
                $normalized['last_name'] = $userInfo['family_name'] ?? '';
                $normalized['name'] = $userInfo['name'] ?? '';
                $normalized['avatar'] = $userInfo['picture'] ?? '';
                break;
                
            case 'linkedin':
                $normalized['provider_id'] = $userInfo['id'] ?? '';
                $normalized['email'] = $userInfo['emailAddress'] ?? '';
                $normalized['first_name'] = $userInfo['firstName']['localized']['en_US'] ?? '';
                $normalized['last_name'] = $userInfo['lastName']['localized']['en_US'] ?? '';
                $normalized['name'] = $normalized['first_name'] . ' ' . $normalized['last_name'];
                break;
                
            case 'microsoft':
                $normalized['provider_id'] = $userInfo['id'] ?? '';
                $normalized['email'] = $userInfo['mail'] ?? $userInfo['userPrincipalName'] ?? '';
                $normalized['first_name'] = $userInfo['givenName'] ?? '';
                $normalized['last_name'] = $userInfo['surname'] ?? '';
                $normalized['name'] = $userInfo['displayName'] ?? '';
                break;
                
            case 'x':
                $normalized['provider_id'] = $userInfo['data']['id'] ?? '';
                $normalized['name'] = $userInfo['data']['name'] ?? '';
                $normalized['email'] = $userInfo['data']['email'] ?? '';
                $normalized['avatar'] = $userInfo['data']['profile_image_url'] ?? '';
                break;
        }
        
        return $normalized;
    }
    
    /**
     * Parse Apple ID token
     */
    private function parseAppleIdToken($idToken) {
        // Decode JWT token (simplified - in production, use proper JWT library)
        $parts = explode('.', $idToken);
        if (count($parts) !== 3) {
            throw new Exception("Invalid Apple ID token format");
        }
        
        $payload = json_decode(base64_decode($parts[1]), true);
        
        return [
            'provider' => 'apple',
            'provider_id' => $payload['sub'] ?? '',
            'email' => $payload['email'] ?? '',
            'first_name' => $payload['given_name'] ?? '',
            'last_name' => $payload['family_name'] ?? '',
            'name' => ($payload['given_name'] ?? '') . ' ' . ($payload['family_name'] ?? '')
        ];
    }
    
    /**
     * Generate Apple client secret
     */
    private function generateAppleClientSecret($config) {
        // In production, implement proper JWT generation for Apple
        // This is a simplified version
        return $config['client_secret'];
    }
    
    /**
     * Process OAuth user login/registration
     */
    private function processOAuthUser($provider, $userInfo) {
        try {
            // Check if user already exists
            $existingUser = $this->db->fetch("
                SELECT u.*, oa.provider_id 
                FROM users u 
                JOIN oauth_accounts oa ON u.id = oa.user_id 
                WHERE oa.provider = ? AND oa.provider_id = ?
            ", [$provider, $userInfo['provider_id']]);
            
            if ($existingUser) {
                // User exists, log them in
                return $this->loginOAuthUser($existingUser);
            }
            
            // Check if email already exists
            if (!empty($userInfo['email'])) {
                $emailUser = $this->db->fetch("SELECT * FROM users WHERE email = ?", [$userInfo['email']]);
                
                if ($emailUser) {
                    // Link OAuth account to existing user
                    $this->linkOAuthAccount($emailUser['id'], $provider, $userInfo);
                    return $this->loginOAuthUser($emailUser);
                }
            }
            
            // Create new user
            return $this->createOAuthUser($provider, $userInfo);
            
        } catch (Exception $e) {
            error_log("Error processing OAuth user: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Create new user from OAuth data
     */
    private function createOAuthUser($provider, $userInfo) {
        $this->db->beginTransaction();
        
        try {
            // Create user account
            $userId = $this->db->insert("
                INSERT INTO users (email, user, pass, role, email_verified, created_at) 
                VALUES (?, ?, ?, 'user', 1, NOW())
            ", [
                $userInfo['email'],
                $userInfo['email'], // Use email as username
                password_hash(bin2hex(random_bytes(16)), PASSWORD_ARGON2ID) // Random password
            ]);
            
            // Create OAuth account link
            $this->db->insert("
                INSERT INTO oauth_accounts (user_id, provider, provider_id, provider_data, created_at) 
                VALUES (?, ?, ?, ?, NOW())
            ", [
                $userId,
                $provider,
                $userInfo['provider_id'],
                json_encode($userInfo)
            ]);
            
            // Create user profile
            $this->db->insert("
                INSERT INTO profiles (user_id, nom, prenom, email, created_at) 
                VALUES (?, ?, ?, ?, NOW())
            ", [
                $userId,
                $userInfo['last_name'],
                $userInfo['first_name'],
                $userInfo['email']
            ]);
            
            $this->db->commit();
            
            // Get the created user
            $user = $this->db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);
            return $this->loginOAuthUser($user);
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Link OAuth account to existing user
     */
    private function linkOAuthAccount($userId, $provider, $userInfo) {
        $this->db->insert("
            INSERT INTO oauth_accounts (user_id, provider, provider_id, provider_data, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ", [
            $userId,
            $provider,
            $userInfo['provider_id'],
            json_encode($userInfo)
        ]);
    }
    
    /**
     * Login OAuth user
     */
    private function loginOAuthUser($user) {
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['username'] = $user['user'];
        $_SESSION['login_time'] = time();
        $_SESSION['session_id'] = session_id();
        $_SESSION['oauth_login'] = true;
        
        // Update last login
        $this->db->update("UPDATE users SET last_login = NOW() WHERE id = ?", [$user['id']]);
        
        return [
            'success' => true,
            'user' => $user,
            'redirect' => $this->getRedirectUrl($user['role'])
        ];
    }
    
    /**
     * Get redirect URL based on user role
     */
    private function getRedirectUrl($role) {
        switch ($role) {
            case 'admin':
                return 'admin/dashboard.php';
            case 'employer':
                return 'employer/dashboard.php';
            case 'advertiser':
                return 'advertiser/dashboard.php';
            default:
                return 'index.php';
        }
    }
    
    /**
     * Make HTTP request
     */
    private function makeHttpRequest($url, $postData = null, $headers = []) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        if ($postData) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        }
        
        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("HTTP Error: $httpCode for URL: $url");
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Get available OAuth providers
     */
    public function getAvailableProviders() {
        $available = [];
        
        foreach ($this->providers as $provider => $config) {
            if (!empty($config['client_id']) && !empty($config['client_secret'])) {
                $available[] = $provider;
            }
        }
        
        return $available;
    }
    
    /**
     * Unlink OAuth account
     */
    public function unlinkOAuthAccount($userId, $provider) {
        try {
            $this->db->delete("
                DELETE FROM oauth_accounts 
                WHERE user_id = ? AND provider = ?
            ", [$userId, $provider]);
            
            return true;
        } catch (Exception $e) {
            error_log("Error unlinking OAuth account: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user's linked OAuth accounts
     */
    public function getUserOAuthAccounts($userId) {
        try {
            return $this->db->fetchAll("
                SELECT provider, provider_id, created_at 
                FROM oauth_accounts 
                WHERE user_id = ?
            ", [$userId]);
        } catch (Exception $e) {
            error_log("Error getting user OAuth accounts: " . $e->getMessage());
            return [];
        }
    }
}
?>
