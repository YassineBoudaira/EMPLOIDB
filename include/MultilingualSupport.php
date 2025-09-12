<?php
/**
 * Multilingual Support Class
 * Handles Arabic, French, and English language support
 */

class MultilingualSupport {
    private $db;
    private $currentLanguage;
    private $defaultLanguage = 'fr';
    private $supportedLanguages = ['ar', 'fr', 'en'];
    private $translations = [];
    private $rtlLanguages = ['ar'];
    
    public function __construct($database) {
        $this->db = $database;
        $this->loadLanguageSettings();
        $this->loadTranslations();
    }
    
    /**
     * Set current language
     */
    public function setLanguage($language) {
        if (in_array($language, $this->supportedLanguages)) {
            $this->currentLanguage = $language;
            $_SESSION['language'] = $language;
            $this->setLocale($language);
            $this->loadTranslations();
        }
    }
    
    /**
     * Get current language
     */
    public function getCurrentLanguage() {
        if (!$this->currentLanguage) {
            if (isset($_SESSION['language'])) {
                $this->currentLanguage = $_SESSION['language'];
            } else {
                $this->currentLanguage = $this->detectBrowserLanguage();
            }
        }
        return $this->currentLanguage ?: $this->defaultLanguage;
    }
    
    /**
     * Detect browser language
     */
    private function detectBrowserLanguage() {
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $languages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            foreach ($languages as $lang) {
                $lang = strtolower(trim(explode(';', $lang)[0]));
                $lang = substr($lang, 0, 2);
                if (in_array($lang, $this->supportedLanguages)) {
                    return $lang;
                }
            }
        }
        return $this->defaultLanguage;
    }
    
    /**
     * Set locale for current language
     */
    private function setLocale($language) {
        $locales = [
            'ar' => 'ar_MA.UTF-8',
            'fr' => 'fr_FR.UTF-8',
            'en' => 'en_US.UTF-8'
        ];
        
        if (isset($locales[$language])) {
            setlocale(LC_ALL, $locales[$language]);
        }
    }
    
    /**
     * Load translations from database
     */
    private function loadTranslations() {
        try {
            $language = $this->getCurrentLanguage();
            $translations = $this->db->fetchAll("
                SELECT translation_key, translation_value 
                FROM translations 
                WHERE language_code = ?
            ", [$language]);
            
            $this->translations = [];
            foreach ($translations as $translation) {
                $this->translations[$translation['translation_key']] = $translation['translation_value'];
            }
        } catch (Exception $e) {
            error_log("Error loading translations: " . $e->getMessage());
            $this->translations = [];
        }
    }
    
    /**
     * Translate text
     */
    public function translate($key, $params = []) {
        $translation = $this->translations[$key] ?? $key;
        
        if (!empty($params)) {
            foreach ($params as $param => $value) {
                $translation = str_replace("{{$param}}", $value, $translation);
            }
        }
        
        return $translation;
    }
    
    /**
     * Get translation (alias for translate)
     */
    public function t($key, $params = []) {
        return $this->translate($key, $params);
    }
    
    /**
     * Check if current language is RTL
     */
    public function isRTL() {
        return in_array($this->getCurrentLanguage(), $this->rtlLanguages);
    }
    
    /**
     * Get language direction
     */
    public function getDirection() {
        return $this->isRTL() ? 'rtl' : 'ltr';
    }
    
    /**
     * Get supported languages
     */
    public function getSupportedLanguages() {
        return $this->supportedLanguages;
    }
    
    /**
     * Load language settings from database
     */
    private function loadLanguageSettings() {
        try {
            $settings = $this->db->fetchAll("SELECT setting_key, setting_value FROM system_settings WHERE category = 'language'");
            foreach ($settings as $setting) {
                if ($setting['setting_key'] === 'default_language') {
                    $this->defaultLanguage = $setting['setting_value'];
                }
            }
        } catch (Exception $e) {
            error_log("Error loading language settings: " . $e->getMessage());
        }
    }
}
?>