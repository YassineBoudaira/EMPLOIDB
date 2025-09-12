<?php
/**
 * Language Switcher Component for EMPLOIDB
 * Handles multilingual support with RTL for Arabic
 */

class LanguageSwitcher {
    private $currentLang;
    private $supportedLanguages;
    private $rtlLanguages;
    
    public function __construct() {
        $this->supportedLanguages = ['fr', 'ar', 'en'];
        $this->rtlLanguages = ['ar'];
        $this->currentLang = $this->getCurrentLanguage();
    }
    
    /**
     * Get current language
     */
    public function getCurrentLanguage() {
        // Check URL parameter first
        if (isset($_GET['lang']) && in_array($_GET['lang'], $this->supportedLanguages)) {
            $_SESSION['lang'] = $_GET['lang'];
            return $_GET['lang'];
        }
        
        // Check session
        if (isset($_SESSION['lang']) && in_array($_SESSION['lang'], $this->supportedLanguages)) {
            return $_SESSION['lang'];
        }
        
        // Check browser language
        $browserLang = $this->detectBrowserLanguage();
        if ($browserLang && in_array($browserLang, $this->supportedLanguages)) {
            $_SESSION['lang'] = $browserLang;
            return $browserLang;
        }
        
        // Default to French
        $_SESSION['lang'] = 'fr';
        return 'fr';
    }
    
    /**
     * Detect browser language
     */
    private function detectBrowserLanguage() {
        if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            return null;
        }
        
        $languages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        foreach ($languages as $lang) {
            $lang = substr(trim($lang), 0, 2);
            if (in_array($lang, $this->supportedLanguages)) {
                return $lang;
            }
        }
        
        return null;
    }
    
    /**
     * Check if current language is RTL
     */
    public function isRTL() {
        return in_array($this->currentLang, $this->rtlLanguages);
    }
    
    /**
     * Get language direction
     */
    public function getDirection() {
        return $this->isRTL() ? 'rtl' : 'ltr';
    }
    
    /**
     * Get language name
     */
    public function getLanguageName($lang = null) {
        $lang = $lang ?: $this->currentLang;
        $names = [
            'fr' => 'Français',
            'ar' => 'العربية',
            'en' => 'English'
        ];
        return $names[$lang] ?? $lang;
    }
    
    /**
     * Get language flag
     */
    public function getLanguageFlag($lang = null) {
        $lang = $lang ?: $this->currentLang;
        $flags = [
            'fr' => '🇫🇷',
            'ar' => '🇲🇦',
            'en' => '🇺🇸'
        ];
        return $flags[$lang] ?? '🌐';
    }
    
    /**
     * Generate language switcher HTML
     */
    public function renderSwitcher($currentPage = '') {
        $currentPage = $currentPage ?: basename($_SERVER['PHP_SELF']);
        $currentUrl = $_SERVER['REQUEST_URI'];
        
        // Remove existing lang parameter
        $currentUrl = preg_replace('/[?&]lang=[^&]*/', '', $currentUrl);
        $separator = strpos($currentUrl, '?') !== false ? '&' : '?';
        
        $html = '<div class="language-switcher dropdown">';
        $html .= '<button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">';
        $html .= $this->getLanguageFlag() . ' ' . $this->getLanguageName();
        $html .= '</button>';
        $html .= '<ul class="dropdown-menu dropdown-menu-end">';
        
        foreach ($this->supportedLanguages as $lang) {
            $isActive = $lang === $this->currentLang ? 'active' : '';
            $url = $currentUrl . $separator . 'lang=' . $lang;
            
            $html .= '<li>';
            $html .= '<a class="dropdown-item ' . $isActive . '" href="' . htmlspecialchars($url) . '">';
            $html .= $this->getLanguageFlag($lang) . ' ' . $this->getLanguageName($lang);
            if ($isActive) {
                $html .= ' <i class="fas fa-check text-success"></i>';
            }
            $html .= '</a>';
            $html .= '</li>';
        }
        
        $html .= '</ul>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Get HTML attributes for current language
     */
    public function getHtmlAttributes() {
        return 'lang="' . $this->currentLang . '" dir="' . $this->getDirection() . '"';
    }
    
    /**
     * Get CSS class for RTL support
     */
    public function getRTLClass() {
        return $this->isRTL() ? 'rtl-mode' : 'ltr-mode';
    }
    
    /**
     * Translate text (simple implementation)
     */
    public function translate($key, $default = '') {
        $translations = $this->getTranslations();
        return $translations[$key] ?? $default;
    }
    
    /**
     * Get translations for current language
     */
    private function getTranslations() {
        $translations = [
            'fr' => [
                'home' => 'Accueil',
                'jobs' => 'Emplois',
                'search' => 'Rechercher',
                'login' => 'Connexion',
                'register' => 'S\'inscrire',
                'profile' => 'Profil',
                'logout' => 'Déconnexion',
                'welcome' => 'Bienvenue',
                'find_job' => 'Trouver un emploi',
                'post_job' => 'Publier une offre',
                'about' => 'À propos',
                'contact' => 'Contact',
                'news' => 'Actualités',
                'features' => 'Fonctionnalités',
                'ai_search' => 'Recherche IA',
                'multilingual' => 'Multilingue',
                'salary_insights' => 'Insights Salaires',
                'job_alerts' => 'Alertes Emploi'
            ],
            'ar' => [
                'home' => 'الرئيسية',
                'jobs' => 'الوظائف',
                'search' => 'البحث',
                'login' => 'تسجيل الدخول',
                'register' => 'التسجيل',
                'profile' => 'الملف الشخصي',
                'logout' => 'تسجيل الخروج',
                'welcome' => 'مرحباً',
                'find_job' => 'البحث عن وظيفة',
                'post_job' => 'نشر وظيفة',
                'about' => 'حول',
                'contact' => 'اتصل بنا',
                'news' => 'الأخبار',
                'features' => 'الميزات',
                'ai_search' => 'البحث بالذكاء الاصطناعي',
                'multilingual' => 'متعدد اللغات',
                'salary_insights' => 'رؤى الرواتب',
                'job_alerts' => 'تنبيهات الوظائف'
            ],
            'en' => [
                'home' => 'Home',
                'jobs' => 'Jobs',
                'search' => 'Search',
                'login' => 'Login',
                'register' => 'Register',
                'profile' => 'Profile',
                'logout' => 'Logout',
                'welcome' => 'Welcome',
                'find_job' => 'Find Job',
                'post_job' => 'Post Job',
                'about' => 'About',
                'contact' => 'Contact',
                'news' => 'News',
                'features' => 'Features',
                'ai_search' => 'AI Search',
                'multilingual' => 'Multilingual',
                'salary_insights' => 'Salary Insights',
                'job_alerts' => 'Job Alerts'
            ]
        ];
        
        return $translations[$this->currentLang] ?? [];
    }
    
    /**
     * Get current language code
     */
    public function getCurrentLang() {
        return $this->currentLang;
    }
    
    /**
     * Get supported languages
     */
    public function getSupportedLanguages() {
        return $this->supportedLanguages;
    }
    
    /**
     * Get RTL languages
     */
    public function getRTLLanguages() {
        return $this->rtlLanguages;
    }
}
?>
