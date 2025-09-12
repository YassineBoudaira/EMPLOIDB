<?php
/**
 * SEO Enhancer Class
 * Handles advanced SEO features including schema.org, JSON-LD, RDFa, and Microdata
 */

class SEOEnhancer {
    private $db;
    private $currentPage;
    private $pageData;
    private $siteConfig;
    
    public function __construct($database) {
        $this->db = $database;
        $this->loadSiteConfig();
    }
    
    /**
     * Load site configuration for SEO
     */
    private function loadSiteConfig() {
        try {
            $config = $this->db->fetchAll("SELECT setting_key, setting_value FROM system_settings WHERE category = 'seo'");
            $this->siteConfig = [];
            foreach ($config as $setting) {
                $this->siteConfig[$setting['setting_key']] = $setting['setting_value'];
            }
        } catch (Exception $e) {
            error_log("Error loading SEO config: " . $e->getMessage());
            $this->siteConfig = [];
        }
    }
    
    /**
     * Set current page data
     */
    public function setPageData($page, $data = []) {
        $this->currentPage = $page;
        $this->pageData = $data;
    }
    
    /**
     * Generate comprehensive meta tags
     */
    public function generateMetaTags() {
        $meta = [];
        
        // Basic meta tags
        $meta[] = $this->generateBasicMeta();
        
        // Open Graph tags
        $meta[] = $this->generateOpenGraphTags();
        
        // Twitter Card tags
        $meta[] = $this->generateTwitterCardTags();
        
        // Additional SEO tags
        $meta[] = $this->generateAdditionalSEOTags();
        
        return implode("\n", $meta);
    }
    
    /**
     * Generate basic meta tags
     */
    private function generateBasicMeta() {
        $title = $this->getPageTitle();
        $description = $this->getPageDescription();
        $keywords = $this->getPageKeywords();
        $canonical = $this->getCanonicalUrl();
        
        $meta = [];
        $meta[] = '<title>' . htmlspecialchars($title) . '</title>';
        $meta[] = '<meta name="description" content="' . htmlspecialchars($description) . '">';
        $meta[] = '<meta name="keywords" content="' . htmlspecialchars($keywords) . '">';
        $meta[] = '<meta name="author" content="' . htmlspecialchars($this->siteConfig['site_author'] ?? 'EMPLOIDB') . '">';
        $meta[] = '<meta name="robots" content="index, follow">';
        $meta[] = '<link rel="canonical" href="' . htmlspecialchars($canonical) . '">';
        
        return implode("\n", $meta);
    }
    
    /**
     * Generate Open Graph tags
     */
    private function generateOpenGraphTags() {
        $title = $this->getPageTitle();
        $description = $this->getPageDescription();
        $image = $this->getPageImage();
        $url = $this->getCanonicalUrl();
        
        $meta = [];
        $meta[] = '<meta property="og:type" content="' . $this->getOGType() . '">';
        $meta[] = '<meta property="og:title" content="' . htmlspecialchars($title) . '">';
        $meta[] = '<meta property="og:description" content="' . htmlspecialchars($description) . '">';
        $meta[] = '<meta property="og:url" content="' . htmlspecialchars($url) . '">';
        $meta[] = '<meta property="og:site_name" content="' . htmlspecialchars($this->siteConfig['site_name'] ?? 'EMPLOIDB') . '">';
        
        if ($image) {
            $meta[] = '<meta property="og:image" content="' . htmlspecialchars($image) . '">';
            $meta[] = '<meta property="og:image:width" content="1200">';
            $meta[] = '<meta property="og:image:height" content="630">';
        }
        
        // Locale
        $meta[] = '<meta property="og:locale" content="' . $this->getLocale() . '">';
        
        return implode("\n", $meta);
    }
    
    /**
     * Generate Twitter Card tags
     */
    private function generateTwitterCardTags() {
        $title = $this->getPageTitle();
        $description = $this->getPageDescription();
        $image = $this->getPageImage();
        
        $meta = [];
        $meta[] = '<meta name="twitter:card" content="summary_large_image">';
        $meta[] = '<meta name="twitter:title" content="' . htmlspecialchars($title) . '">';
        $meta[] = '<meta name="twitter:description" content="' . htmlspecialchars($description) . '">';
        
        if ($image) {
            $meta[] = '<meta name="twitter:image" content="' . htmlspecialchars($image) . '">';
        }
        
        if (isset($this->siteConfig['twitter_handle'])) {
            $meta[] = '<meta name="twitter:site" content="@' . htmlspecialchars($this->siteConfig['twitter_handle']) . '">';
        }
        
        return implode("\n", $meta);
    }
    
    /**
     * Generate additional SEO tags
     */
    private function generateAdditionalSEOTags() {
        $meta = [];
        
        // Language
        $meta[] = '<meta name="language" content="' . $this->getLanguage() . '">';
        
        // Geo tags
        if (isset($this->siteConfig['geo_region'])) {
            $meta[] = '<meta name="geo.region" content="' . htmlspecialchars($this->siteConfig['geo_region']) . '">';
        }
        
        if (isset($this->siteConfig['geo_placename'])) {
            $meta[] = '<meta name="geo.placename" content="' . htmlspecialchars($this->siteConfig['geo_placename']) . '">';
        }
        
        // Mobile optimization
        $meta[] = '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        $meta[] = '<meta name="mobile-web-app-capable" content="yes">';
        
        // Theme color
        $meta[] = '<meta name="theme-color" content="' . ($this->siteConfig['theme_color'] ?? '#2563eb') . '">';
        
        return implode("\n", $meta);
    }
    
    /**
     * Generate JSON-LD structured data
     */
    public function generateJSONLD() {
        $jsonld = [];
        
        // Organization schema
        $jsonld[] = $this->generateOrganizationSchema();
        
        // Website schema
        $jsonld[] = $this->generateWebsiteSchema();
        
        // Page-specific schemas
        $pageSchema = $this->generatePageSpecificSchema();
        if ($pageSchema) {
            $jsonld[] = $pageSchema;
        }
        
        if (empty($jsonld)) {
            return '';
        }
        
        return '<script type="application/ld+json">' . json_encode($jsonld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
    }
    
    /**
     * Generate Organization schema
     */
    private function generateOrganizationSchema() {
        return [
            "@context" => "https://schema.org",
            "@type" => "Organization",
            "name" => $this->siteConfig['site_name'] ?? 'EMPLOIDB',
            "url" => $this->siteConfig['site_url'] ?? (defined('SITE_URL') ? SITE_URL : 'http://localhost'),
            "logo" => $this->siteConfig['site_logo'] ?? (defined('SITE_URL') ? SITE_URL : 'http://localhost') . '/assets/images/logo.png',
            "description" => $this->siteConfig['site_description'] ?? 'Plateforme d\'emploi au Maroc',
            "address" => [
                "@type" => "PostalAddress",
                "addressCountry" => "MA",
                "addressLocality" => $this->siteConfig['site_city'] ?? 'Casablanca'
            ],
            "contactPoint" => [
                "@type" => "ContactPoint",
                "telephone" => $this->siteConfig['site_phone'] ?? '',
                "contactType" => "customer service",
                "availableLanguage" => ["French", "Arabic", "English"]
            ],
            "sameAs" => $this->getSocialMediaLinks()
        ];
    }
    
    /**
     * Generate Website schema
     */
    private function generateWebsiteSchema() {
        return [
            "@context" => "https://schema.org",
            "@type" => "WebSite",
            "name" => $this->siteConfig['site_name'] ?? 'EMPLOIDB',
            "url" => $this->siteConfig['site_url'] ?? (defined('SITE_URL') ? SITE_URL : 'http://localhost'),
            "description" => $this->siteConfig['site_description'] ?? 'Plateforme d\'emploi au Maroc',
            "potentialAction" => [
                "@type" => "SearchAction",
                "target" => [
                    "@type" => "EntryPoint",
                    "urlTemplate" => (defined('SITE_URL') ? SITE_URL : 'http://localhost') . "/search.php?q={search_term_string}"
                ],
                "query-input" => "required name=search_term_string"
            ]
        ];
    }
    
    /**
     * Generate page-specific schema
     */
    private function generatePageSpecificSchema() {
        switch ($this->currentPage) {
            case 'job':
                return $this->generateJobPostingSchema();
            case 'company':
                return $this->generateOrganizationSchema();
            case 'article':
                return $this->generateArticleSchema();
            default:
                return null;
        }
    }
    
    /**
     * Generate JobPosting schema
     */
    private function generateJobPostingSchema() {
        if (!isset($this->pageData['job'])) {
            return null;
        }
        
        $job = $this->pageData['job'];
        
        return [
            "@context" => "https://schema.org",
            "@type" => "JobPosting",
            "title" => $job['titre'] ?? '',
            "description" => $job['description'] ?? '',
            "datePosted" => $job['date_a'] ?? '',
            "validThrough" => $job['date_fin'] ?? '',
            "employmentType" => $this->mapContractType($job['contrat_id'] ?? ''),
            "hiringOrganization" => [
                "@type" => "Organization",
                "name" => $job['entreprise'] ?? '',
                "url" => $job['siteweb'] ?? ''
            ],
            "jobLocation" => [
                "@type" => "Place",
                "address" => [
                    "@type" => "PostalAddress",
                    "addressLocality" => $this->getCityName($job['ville_id'] ?? ''),
                    "addressCountry" => "MA"
                ]
            ],
            "baseSalary" => $this->generateSalarySchema($job),
            "workHours" => "Full-time",
            "skills" => $this->extractSkills($job['description'] ?? '')
        ];
    }
    
    /**
     * Generate Article schema
     */
    private function generateArticleSchema() {
        if (!isset($this->pageData['article'])) {
            return null;
        }
        
        $article = $this->pageData['article'];
        
        return [
            "@context" => "https://schema.org",
            "@type" => "Article",
            "headline" => $article['title'] ?? '',
            "description" => $article['excerpt'] ?? '',
            "image" => $article['featured_image'] ?? '',
            "datePublished" => $article['published_at'] ?? '',
            "dateModified" => $article['updated_at'] ?? $article['published_at'] ?? '',
            "author" => [
                "@type" => "Person",
                "name" => $article['author_name'] ?? ''
            ],
            "publisher" => [
                "@type" => "Organization",
                "name" => $this->siteConfig['site_name'] ?? 'EMPLOIDB',
                "logo" => [
                    "@type" => "ImageObject",
                    "url" => $this->siteConfig['site_logo'] ?? (defined('SITE_URL') ? SITE_URL : 'http://localhost') . '/assets/images/logo.png'
                ]
            ]
        ];
    }
    
    /**
     * Generate RDFa attributes
     */
    public function generateRDFaAttributes($type, $data = []) {
        $attributes = [];
        
        switch ($type) {
            case 'job':
                $attributes['typeof'] = 'schema:JobPosting';
                $attributes['property'] = 'schema:name';
                $attributes['content'] = $data['title'] ?? '';
                break;
                
            case 'organization':
                $attributes['typeof'] = 'schema:Organization';
                $attributes['property'] = 'schema:name';
                $attributes['content'] = $data['name'] ?? '';
                break;
                
            case 'article':
                $attributes['typeof'] = 'schema:Article';
                $attributes['property'] = 'schema:headline';
                $attributes['content'] = $data['title'] ?? '';
                break;
        }
        
        return $attributes;
    }
    
    /**
     * Generate Microdata attributes
     */
    public function generateMicrodataAttributes($type, $data = []) {
        $attributes = [];
        
        switch ($type) {
            case 'job':
                $attributes['itemscope'] = '';
                $attributes['itemtype'] = 'https://schema.org/JobPosting';
                break;
                
            case 'organization':
                $attributes['itemscope'] = '';
                $attributes['itemtype'] = 'https://schema.org/Organization';
                break;
                
            case 'article':
                $attributes['itemscope'] = '';
                $attributes['itemtype'] = 'https://schema.org/Article';
                break;
        }
        
        return $attributes;
    }
    
    /**
     * Generate sitemap XML
     */
    public function generateSitemap() {
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>';
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        
        // Static pages
        $staticPages = [
            ['url' => (defined('SITE_URL') ? SITE_URL : 'http://localhost'), 'priority' => '1.0', 'changefreq' => 'daily'],
            ['url' => (defined('SITE_URL') ? SITE_URL : 'http://localhost') . '/search.php', 'priority' => '0.9', 'changefreq' => 'daily'],
            ['url' => (defined('SITE_URL') ? SITE_URL : 'http://localhost') . '/news.php', 'priority' => '0.8', 'changefreq' => 'daily'],
            ['url' => (defined('SITE_URL') ? SITE_URL : 'http://localhost') . '/about.php', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['url' => (defined('SITE_URL') ? SITE_URL : 'http://localhost') . '/contact.php', 'priority' => '0.7', 'changefreq' => 'monthly']
        ];
        
        foreach ($staticPages as $page) {
            $sitemap .= $this->generateSitemapUrl($page);
        }
        
        // Job pages
        try {
            $jobs = $this->db->fetchAll("SELECT id, date_a FROM annonces WHERE status = 'active' ORDER BY date_a DESC LIMIT 1000");
            foreach ($jobs as $job) {
                $sitemap .= $this->generateSitemapUrl([
                    'url' => (defined('SITE_URL') ? SITE_URL : 'http://localhost') . '/annoncedetaile.php?id=' . $job['id'],
                    'priority' => '0.8',
                    'changefreq' => 'weekly',
                    'lastmod' => $job['date_a']
                ]);
            }
        } catch (Exception $e) {
            error_log("Error generating job sitemap: " . $e->getMessage());
        }
        
        // News articles
        try {
            $articles = $this->db->fetchAll("SELECT id, published_at FROM news_articles WHERE status = 'published' ORDER BY published_at DESC LIMIT 500");
            foreach ($articles as $article) {
                $sitemap .= $this->generateSitemapUrl([
                    'url' => (defined('SITE_URL') ? SITE_URL : 'http://localhost') . '/news/article.php?id=' . $article['id'],
                    'priority' => '0.7',
                    'changefreq' => 'monthly',
                    'lastmod' => $article['published_at']
                ]);
            }
        } catch (Exception $e) {
            error_log("Error generating news sitemap: " . $e->getMessage());
        }
        
        $sitemap .= '</urlset>';
        
        return $sitemap;
    }
    
    /**
     * Generate sitemap URL entry
     */
    private function generateSitemapUrl($data) {
        $url = '<url>';
        $url .= '<loc>' . htmlspecialchars($data['url']) . '</loc>';
        $url .= '<priority>' . $data['priority'] . '</priority>';
        $url .= '<changefreq>' . $data['changefreq'] . '</changefreq>';
        
        if (isset($data['lastmod'])) {
            $url .= '<lastmod>' . date('Y-m-d', strtotime($data['lastmod'])) . '</lastmod>';
        }
        
        $url .= '</url>';
        
        return $url;
    }
    
    /**
     * Helper methods
     */
    private function getPageTitle() {
        return $this->pageData['title'] ?? $this->siteConfig['site_name'] ?? 'EMPLOIDB';
    }
    
    private function getPageDescription() {
        return $this->pageData['description'] ?? $this->siteConfig['site_description'] ?? 'Plateforme d\'emploi au Maroc';
    }
    
    private function getPageKeywords() {
        return $this->pageData['keywords'] ?? $this->siteConfig['site_keywords'] ?? 'emploi, maroc, travail, recrutement';
    }
    
    private function getPageImage() {
        return $this->pageData['image'] ?? $this->siteConfig['site_image'] ?? '';
    }
    
    private function getCanonicalUrl() {
        return $this->pageData['canonical'] ?? (defined('SITE_URL') ? SITE_URL : 'http://localhost') . $_SERVER['REQUEST_URI'];
    }
    
    private function getOGType() {
        switch ($this->currentPage) {
            case 'job':
                return 'article';
            case 'article':
                return 'article';
            default:
                return 'website';
        }
    }
    
    private function getLocale() {
        return $this->pageData['locale'] ?? 'fr_FR';
    }
    
    private function getLanguage() {
        return $this->pageData['language'] ?? 'fr';
    }
    
    private function getSocialMediaLinks() {
        $links = [];
        if (isset($this->siteConfig['facebook_url'])) {
            $links[] = $this->siteConfig['facebook_url'];
        }
        if (isset($this->siteConfig['twitter_url'])) {
            $links[] = $this->siteConfig['twitter_url'];
        }
        if (isset($this->siteConfig['linkedin_url'])) {
            $links[] = $this->siteConfig['linkedin_url'];
        }
        return $links;
    }
    
    private function mapContractType($contractId) {
        $types = [
            1 => 'CONTRACTOR',
            2 => 'FULL_TIME',
            3 => 'PART_TIME',
            4 => 'INTERN',
            5 => 'TEMPORARY'
        ];
        
        return $types[$contractId] ?? 'FULL_TIME';
    }
    
    private function getCityName($cityId) {
        try {
            $city = $this->db->fetch("SELECT nom FROM villes WHERE id = ?", [$cityId]);
            return $city['nom'] ?? '';
        } catch (Exception $e) {
            return '';
        }
    }
    
    private function generateSalarySchema($job) {
        if (empty($job['salaire'])) {
            return null;
        }
        
        return [
            "@type" => "MonetaryAmount",
            "currency" => "MAD",
            "value" => [
                "@type" => "QuantitativeValue",
                "value" => $job['salaire']
            ]
        ];
    }
    
    private function extractSkills($description) {
        // Simple skill extraction - in production, use more sophisticated NLP
        $commonSkills = ['PHP', 'JavaScript', 'Python', 'Java', 'React', 'Vue', 'Angular', 'MySQL', 'MongoDB'];
        $skills = [];
        
        foreach ($commonSkills as $skill) {
            if (stripos($description, $skill) !== false) {
                $skills[] = $skill;
            }
        }
        
        return $skills;
    }
}
?>
