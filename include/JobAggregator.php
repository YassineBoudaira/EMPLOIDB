<?php
/**
 * Enhanced Job Aggregator Class
 * Handles scraping and aggregation of jobs from multiple sources
 * Supports French, Arabic, and English languages
 * Features AI-based deduplication, RSS feeds, and real-time webhooks
 */

class JobAggregator {
    private $db;
    private $sources = [];
    private $config = [];
    private $logPath;
    private $aiService;
    private $rssFeeds = [];
    private $webhookEndpoints = [];
    
    public function __construct($database) {
        $this->db = $database;
        $this->logPath = (defined('LOG_PATH') ? LOG_PATH : 'logs/') . 'job_aggregation.log';
        $this->loadConfig();
        $this->loadSources();
        $this->loadRSSFeeds();
        $this->loadWebhookEndpoints();
        $this->initializeAIService();
    }
    
    /**
     * Load configuration from database
     */
    private function loadConfig() {
        try {
            $settings = $this->db->fetchAll("SELECT setting_key, setting_value FROM system_settings WHERE category = 'aggregation'");
            foreach ($settings as $setting) {
                $this->config[$setting['setting_key']] = $setting['setting_value'];
            }
        } catch (Exception $e) {
            $this->log("Error loading config: " . $e->getMessage(), 'error');
        }
    }
    
    /**
     * Load active job sources
     */
    private function loadSources() {
        try {
            $this->sources = $this->db->fetchAll("SELECT * FROM job_sources WHERE status = 'active' ORDER BY priority ASC");
        } catch (Exception $e) {
            $this->log("Error loading sources: " . $e->getMessage(), 'error');
        }
    }
    
    /**
     * Load RSS feeds configuration
     */
    private function loadRSSFeeds() {
        try {
            $this->rssFeeds = $this->db->fetchAll("SELECT * FROM rss_feeds WHERE status = 'active' ORDER BY priority ASC");
        } catch (Exception $e) {
            $this->log("Error loading RSS feeds: " . $e->getMessage(), 'error');
        }
    }
    
    /**
     * Load webhook endpoints
     */
    private function loadWebhookEndpoints() {
        try {
            $this->webhookEndpoints = $this->db->fetchAll("SELECT * FROM webhook_endpoints WHERE status = 'active'");
        } catch (Exception $e) {
            $this->log("Error loading webhook endpoints: " . $e->getMessage(), 'error');
        }
    }
    
    /**
     * Initialize AI service for deduplication and content processing
     */
    private function initializeAIService() {
        try {
            $this->aiService = new AIService($this->db);
        } catch (Exception $e) {
            $this->log("Error initializing AI service: " . $e->getMessage(), 'error');
        }
    }
    
    /**
     * Run job aggregation for all sources including RSS feeds
     */
    public function runAggregation() {
        $this->log("Starting enhanced job aggregation process", 'info');
        $totalJobs = 0;
        $totalImported = 0;
        $totalDuplicates = 0;
        
        // Process traditional scraping sources
        foreach ($this->sources as $source) {
            try {
                $this->log("Processing source: " . $source['name'], 'info');
                $result = $this->scrapeSource($source);
                $totalJobs += $result['jobs_found'];
                $totalImported += $result['jobs_imported'];
                $totalDuplicates += $result['jobs_duplicates'] ?? 0;
                
                // Log the result
                $this->logScrapingResult($source['id'], $result);
                
            } catch (Exception $e) {
                $this->log("Error processing source {$source['name']}: " . $e->getMessage(), 'error');
                $this->logScrapingResult($source['id'], [
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'jobs_found' => 0,
                    'jobs_imported' => 0,
                    'jobs_duplicates' => 0,
                    'jobs_errors' => 1
                ]);
            }
        }
        
        // Process RSS feeds
        foreach ($this->rssFeeds as $feed) {
            try {
                $this->log("Processing RSS feed: " . $feed['name'], 'info');
                $result = $this->processRSSFeed($feed);
                $totalJobs += $result['jobs_found'];
                $totalImported += $result['jobs_imported'];
                $totalDuplicates += $result['jobs_duplicates'] ?? 0;
                
            } catch (Exception $e) {
                $this->log("Error processing RSS feed {$feed['name']}: " . $e->getMessage(), 'error');
            }
        }
        
        // Process webhook notifications
        $this->processWebhookNotifications();
        
        $this->log("Enhanced aggregation completed. Total jobs found: $totalJobs, Total imported: $totalImported, Duplicates: $totalDuplicates", 'info');
        return [
            'total_jobs' => $totalJobs,
            'total_imported' => $totalImported,
            'total_duplicates' => $totalDuplicates,
            'sources_processed' => count($this->sources),
            'rss_feeds_processed' => count($this->rssFeeds)
        ];
    }
    
    /**
     * Scrape a specific source
     */
    private function scrapeSource($source) {
        $startTime = microtime(true);
        $jobsFound = 0;
        $jobsImported = 0;
        $jobsDuplicates = 0;
        $jobsErrors = 0;
        
        try {
            // Parse scraping configuration
            $config = json_decode($source['scraping_config'], true) ?? [];
            
            // Get jobs based on source type
            switch ($source['name']) {
                case 'Emploi Public':
                    $jobs = $this->scrapeEmploiPublic($config);
                    break;
                case 'Alwadifa Maroc':
                    $jobs = $this->scrapeAlwadifaMaroc($config);
                    break;
                case 'DreamJob.ma':
                    $jobs = $this->scrapeDreamJob($config);
                    break;
                case 'Indeed Maroc':
                    $jobs = $this->scrapeIndeed($config);
                    break;
                case 'LinkedIn Jobs':
                    $jobs = $this->scrapeLinkedIn($config);
                    break;
                case 'Glassdoor Maroc':
                    $jobs = $this->scrapeGlassdoor($config);
                    break;
                default:
                    $jobs = $this->scrapeGeneric($source, $config);
            }
            
            $jobsFound = count($jobs);
            
            // Process each job
            foreach ($jobs as $job) {
                try {
                    $result = $this->processJob($source['id'], $job);
                    if ($result === 'imported') {
                        $jobsImported++;
                    } elseif ($result === 'duplicate') {
                        $jobsDuplicates++;
                    }
                } catch (Exception $e) {
                    $jobsErrors++;
                    $this->log("Error processing job: " . $e->getMessage(), 'error');
                }
            }
            
            // Update last scraped time
            $this->db->update(
                "UPDATE job_sources SET last_scraped = NOW() WHERE id = ?",
                [$source['id']]
            );
            
        } catch (Exception $e) {
            $this->log("Error scraping source {$source['name']}: " . $e->getMessage(), 'error');
            throw $e;
        }
        
        $executionTime = microtime(true) - $startTime;
        $memoryUsage = memory_get_peak_usage(true);
        
        return [
            'status' => 'success',
            'message' => "Successfully scraped {$source['name']}",
            'jobs_found' => $jobsFound,
            'jobs_imported' => $jobsImported,
            'jobs_duplicates' => $jobsDuplicates,
            'jobs_errors' => $jobsErrors,
            'execution_time' => $executionTime,
            'memory_usage' => $memoryUsage
        ];
    }
    
    /**
     * Scrape Emploi Public (Government jobs)
     */
    private function scrapeEmploiPublic($config) {
        $jobs = [];
        $url = 'https://emploi-public.ma/concours';
        
        try {
            $html = $this->fetchUrl($url);
            $dom = new DOMDocument();
            @$dom->loadHTML($html);
            $xpath = new DOMXPath($dom);
            
            // Extract job listings (this would need to be customized based on actual HTML structure)
            $jobElements = $xpath->query('//div[contains(@class, "job-listing")]');
            
            foreach ($jobElements as $element) {
                $job = [
                    'title' => $this->extractText($xpath, './/h3', $element),
                    'description' => $this->extractText($xpath, './/p', $element),
                    'company_name' => 'Secteur Public',
                    'location' => $this->extractText($xpath, './/span[contains(@class, "location")]', $element),
                    'external_url' => $this->extractAttribute($xpath, './/a/@href', $element),
                    'posted_date' => $this->extractText($xpath, './/span[contains(@class, "date")]', $element),
                    'job_type' => 'CDI',
                    'experience_level' => 'entry',
                    'education_level' => 'bachelor'
                ];
                
                if (!empty($job['title']) && !empty($job['external_url'])) {
                    $jobs[] = $job;
                }
            }
            
        } catch (Exception $e) {
            $this->log("Error scraping Emploi Public: " . $e->getMessage(), 'error');
        }
        
        return $jobs;
    }
    
    /**
     * Scrape Alwadifa Maroc
     */
    private function scrapeAlwadifaMaroc($config) {
        $jobs = [];
        $url = 'https://alwadifa-maroc.com/offres-emploi';
        
        try {
            $html = $this->fetchUrl($url);
            $dom = new DOMDocument();
            @$dom->loadHTML($html);
            $xpath = new DOMXPath($dom);
            
            // Extract job listings
            $jobElements = $xpath->query('//div[contains(@class, "job-item")]');
            
            foreach ($jobElements as $element) {
                $job = [
                    'title' => $this->extractText($xpath, './/h2', $element),
                    'description' => $this->extractText($xpath, './/div[contains(@class, "description")]', $element),
                    'company_name' => $this->extractText($xpath, './/span[contains(@class, "company")]', $element),
                    'location' => $this->extractText($xpath, './/span[contains(@class, "location")]', $element),
                    'external_url' => $this->extractAttribute($xpath, './/a/@href', $element),
                    'posted_date' => $this->extractText($xpath, './/span[contains(@class, "date")]', $element),
                    'job_type' => 'CDI',
                    'experience_level' => 'entry',
                    'education_level' => 'bachelor'
                ];
                
                if (!empty($job['title']) && !empty($job['external_url'])) {
                    $jobs[] = $job;
                }
            }
            
        } catch (Exception $e) {
            $this->log("Error scraping Alwadifa Maroc: " . $e->getMessage(), 'error');
        }
        
        return $jobs;
    }
    
    /**
     * Scrape DreamJob.ma
     */
    private function scrapeDreamJob($config) {
        $jobs = [];
        $url = 'https://dreamjob.ma/offres-emploi';
        
        try {
            $html = $this->fetchUrl($url);
            $dom = new DOMDocument();
            @$dom->loadHTML($html);
            $xpath = new DOMXPath($dom);
            
            // Extract job listings
            $jobElements = $xpath->query('//div[contains(@class, "job-card")]');
            
            foreach ($jobElements as $element) {
                $job = [
                    'title' => $this->extractText($xpath, './/h3', $element),
                    'description' => $this->extractText($xpath, './/p', $element),
                    'company_name' => $this->extractText($xpath, './/div[contains(@class, "company-name")]', $element),
                    'location' => $this->extractText($xpath, './/span[contains(@class, "location")]', $element),
                    'external_url' => $this->extractAttribute($xpath, './/a/@href', $element),
                    'posted_date' => $this->extractText($xpath, './/span[contains(@class, "date")]', $element),
                    'job_type' => 'CDI',
                    'experience_level' => 'entry',
                    'education_level' => 'bachelor'
                ];
                
                if (!empty($job['title']) && !empty($job['external_url'])) {
                    $jobs[] = $job;
                }
            }
            
        } catch (Exception $e) {
            $this->log("Error scraping DreamJob.ma: " . $e->getMessage(), 'error');
        }
        
        return $jobs;
    }
    
    /**
     * Scrape Indeed Morocco
     */
    private function scrapeIndeed($config) {
        $jobs = [];
        $url = 'https://ma.indeed.com/jobs?q=&l=Morocco';
        
        try {
            $html = $this->fetchUrl($url);
            $dom = new DOMDocument();
            @$dom->loadHTML($html);
            $xpath = new DOMXPath($dom);
            
            // Extract job listings
            $jobElements = $xpath->query('//div[contains(@class, "job_seen_beacon")]');
            
            foreach ($jobElements as $element) {
                $job = [
                    'title' => $this->extractText($xpath, './/h2//a', $element),
                    'description' => $this->extractText($xpath, './/div[contains(@class, "summary")]', $element),
                    'company_name' => $this->extractText($xpath, './/span[contains(@class, "companyName")]', $element),
                    'location' => $this->extractText($xpath, './/div[contains(@class, "companyLocation")]', $element),
                    'external_url' => 'https://ma.indeed.com' . $this->extractAttribute($xpath, './/h2//a/@href', $element),
                    'posted_date' => $this->extractText($xpath, './/span[contains(@class, "date")]', $element),
                    'job_type' => 'CDI',
                    'experience_level' => 'entry',
                    'education_level' => 'bachelor'
                ];
                
                if (!empty($job['title']) && !empty($job['external_url'])) {
                    $jobs[] = $job;
                }
            }
            
        } catch (Exception $e) {
            $this->log("Error scraping Indeed: " . $e->getMessage(), 'error');
        }
        
        return $jobs;
    }
    
    /**
     * Scrape LinkedIn Jobs
     */
    private function scrapeLinkedIn($config) {
        $jobs = [];
        // LinkedIn requires authentication and has anti-bot measures
        // This would need to be implemented with proper authentication
        $this->log("LinkedIn scraping requires authentication - skipping for now", 'warning');
        return $jobs;
    }
    
    /**
     * Scrape Glassdoor
     */
    private function scrapeGlassdoor($config) {
        $jobs = [];
        // Glassdoor has strong anti-bot measures
        // This would need to be implemented with proper authentication
        $this->log("Glassdoor scraping requires authentication - skipping for now", 'warning');
        return $jobs;
    }
    
    /**
     * Generic scraper for other sources
     */
    private function scrapeGeneric($source, $config) {
        $jobs = [];
        // Implement generic scraping logic based on configuration
        return $jobs;
    }
    
    /**
     * Process a single job
     */
    private function processJob($sourceId, $job) {
        // Check if job already exists
        $existing = $this->db->fetch(
            "SELECT id FROM aggregated_jobs WHERE source_id = ? AND external_id = ?",
            [$sourceId, $this->generateExternalId($job)]
        );
        
        if ($existing) {
            return 'duplicate';
        }
        
        // Insert new job
        $jobId = $this->db->insert(
            "INSERT INTO aggregated_jobs (source_id, external_id, title, description, company_name, location, external_url, posted_date, job_type, experience_level, education_level, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')",
            [
                $sourceId,
                $this->generateExternalId($job),
                $job['title'],
                $job['description'],
                $job['company_name'],
                $job['location'],
                $job['external_url'],
                $this->parseDate($job['posted_date']),
                $job['job_type'] ?? 'CDI',
                $job['experience_level'] ?? 'entry',
                $job['education_level'] ?? 'bachelor'
            ]
        );
        
        // Auto-import to annonces if enabled
        if ($this->config['auto_import_jobs'] === 'true') {
            $this->importToAnnonces($jobId);
        }
        
        return 'imported';
    }
    
    /**
     * Import aggregated job to annonces table
     */
    public function importToAnnonces($aggregatedJobId) {
        try {
            $job = $this->db->fetch(
                "SELECT * FROM aggregated_jobs WHERE id = ? AND imported_to_annonces = 0",
                [$aggregatedJobId]
            );
            
            if (!$job) {
                return false;
            }
            
            // Find matching domaine and ville
            $domaineId = $this->findMatchingDomaine($job['title'], $job['description']);
            $villeId = $this->findMatchingVille($job['location']);
            
            // Insert into annonces table
            $annonceId = $this->db->insert(
                "INSERT INTO annonces (titre, description, domaine_id, ville_id, company_name, company_logo, salary_min, salary_max, job_type, experience_level, education_level, status, date_publication, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW(), NOW())",
                [
                    $job['title'],
                    $job['description'],
                    $domaineId,
                    $villeId,
                    $job['company_name'],
                    null,
                    $job['salary_min'],
                    $job['salary_max'],
                    $job['job_type'],
                    $job['experience_level'],
                    $job['education_level']
                ]
            );
            
            // Update aggregated job
            $this->db->update(
                "UPDATE aggregated_jobs SET imported_to_annonces = 1, imported_annonce_id = ? WHERE id = ?",
                [$annonceId, $aggregatedJobId]
            );
            
            return $annonceId;
            
        } catch (Exception $e) {
            $this->log("Error importing job to annonces: " . $e->getMessage(), 'error');
            return false;
        }
    }
    
    /**
     * Find matching domaine based on job content
     */
    private function findMatchingDomaine($title, $description) {
        $text = strtolower($title . ' ' . $description);
        
        $domaines = $this->db->fetchAll("SELECT id, nom FROM domaines WHERE status = 'active'");
        
        foreach ($domaines as $domaine) {
            $keywords = $this->getDomaineKeywords($domaine['nom']);
            foreach ($keywords as $keyword) {
                if (strpos($text, strtolower($keyword)) !== false) {
                    return $domaine['id'];
                }
            }
        }
        
        return 1; // Default to first domaine
    }
    
    /**
     * Find matching ville based on location
     */
    private function findMatchingVille($location) {
        if (empty($location)) {
            return 1; // Default to first ville
        }
        
        $villes = $this->db->fetchAll("SELECT id, nom FROM villes WHERE status = 'active'");
        
        foreach ($villes as $ville) {
            if (stripos($location, $ville['nom']) !== false) {
                return $ville['id'];
            }
        }
        
        return 1; // Default to first ville
    }
    
    /**
     * Get keywords for a domaine
     */
    private function getDomaineKeywords($domaineName) {
        $keywords = [
            'Informatique & Technologies' => ['informatique', 'développeur', 'programmeur', 'IT', 'technologie', 'software', 'web', 'mobile'],
            'Finance & Comptabilité' => ['finance', 'comptabilité', 'comptable', 'financier', 'banque', 'assurance'],
            'Marketing & Communication' => ['marketing', 'communication', 'publicité', 'média', 'digital', 'social'],
            'Ressources Humaines' => ['ressources humaines', 'RH', 'recrutement', 'formation', 'personnel'],
            'Vente & Commerce' => ['vente', 'commercial', 'commerce', 'client', 'business', 'négociation'],
            'Santé & Médical' => ['santé', 'médical', 'médecin', 'infirmier', 'pharmacie', 'hôpital'],
            'Éducation & Formation' => ['éducation', 'enseignement', 'professeur', 'formateur', 'école', 'université'],
            'Ingénierie' => ['ingénieur', 'ingénierie', 'technique', 'mécanique', 'électrique', 'civil'],
            'Juridique' => ['juridique', 'avocat', 'droit', 'légal', 'justice', 'notaire'],
            'Tourisme & Hôtellerie' => ['tourisme', 'hôtellerie', 'restaurant', 'hôtel', 'voyage', 'accueil']
        ];
        
        return $keywords[$domaineName] ?? [];
    }
    
    /**
     * Generate external ID for job
     */
    private function generateExternalId($job) {
        return md5($job['title'] . $job['company_name'] . $job['external_url']);
    }
    
    /**
     * Parse date string
     */
    private function parseDate($dateString) {
        if (empty($dateString)) {
            return date('Y-m-d H:i:s');
        }
        
        // Try to parse various date formats
        $formats = [
            'Y-m-d H:i:s',
            'Y-m-d',
            'd/m/Y',
            'd-m-Y',
            'M d, Y',
            'F j, Y'
        ];
        
        foreach ($formats as $format) {
            $date = DateTime::createFromFormat($format, $dateString);
            if ($date !== false) {
                return $date->format('Y-m-d H:i:s');
            }
        }
        
        return date('Y-m-d H:i:s');
    }
    
    /**
     * Fetch URL content
     */
    private function fetchUrl($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("HTTP Error: $httpCode for URL: $url");
        }
        
        return $html;
    }
    
    /**
     * Extract text from DOM element
     */
    private function extractText($xpath, $query, $context = null) {
        $nodes = $xpath->query($query, $context);
        return $nodes->length > 0 ? trim($nodes->item(0)->textContent) : '';
    }
    
    /**
     * Extract attribute from DOM element
     */
    private function extractAttribute($xpath, $query, $context = null) {
        $nodes = $xpath->query($query, $context);
        return $nodes->length > 0 ? trim($nodes->item(0)->nodeValue) : '';
    }
    
    /**
     * Log scraping result
     */
    private function logScrapingResult($sourceId, $result) {
        $this->db->insert(
            "INSERT INTO scraping_logs (source_id, status, message, jobs_found, jobs_imported, jobs_duplicates, jobs_errors, execution_time, memory_usage) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $sourceId,
                $result['status'],
                $result['message'],
                $result['jobs_found'],
                $result['jobs_imported'],
                $result['jobs_duplicates'],
                $result['jobs_errors'],
                $result['execution_time'] ?? null,
                $result['memory_usage'] ?? null
            ]
        );
    }
    
    /**
     * Log message
     */
    private function log($message, $level = 'info') {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] [$level] $message" . PHP_EOL;
        file_put_contents($this->logPath, $logMessage, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Get aggregation statistics
     */
    public function getStatistics() {
        try {
            $stats = [];
            
            // Total aggregated jobs
            $stats['total_aggregated_jobs'] = $this->db->fetch("SELECT COUNT(*) as count FROM aggregated_jobs")['count'];
            
            // Jobs by status
            $stats['jobs_by_status'] = $this->db->fetchAll("SELECT status, COUNT(*) as count FROM aggregated_jobs GROUP BY status");
            
            // Jobs by source
            $stats['jobs_by_source'] = $this->db->fetchAll("SELECT js.name, COUNT(aj.id) as count FROM job_sources js LEFT JOIN aggregated_jobs aj ON js.id = aj.source_id GROUP BY js.id, js.name");
            
            // Recent scraping activity
            $stats['recent_activity'] = $this->db->fetchAll("SELECT sl.*, js.name as source_name FROM scraping_logs sl JOIN job_sources js ON sl.source_id = js.id ORDER BY sl.created_at DESC LIMIT 10");
            
            return $stats;
            
        } catch (Exception $e) {
            $this->log("Error getting statistics: " . $e->getMessage(), 'error');
            return [];
        }
    }
    
    /**
     * Process RSS feed for job aggregation
     */
    private function processRSSFeed($feed) {
        $jobsFound = 0;
        $jobsImported = 0;
        $jobsDuplicates = 0;
        
        try {
            $rssContent = $this->fetchUrl($feed['url']);
            $xml = simplexml_load_string($rssContent);
            
            if (!$xml) {
                throw new Exception("Invalid RSS feed format");
            }
            
            $items = $xml->channel->item ?? $xml->item ?? [];
            
            foreach ($items as $item) {
                $jobsFound++;
                
                $job = [
                    'title' => (string)$item->title,
                    'description' => (string)$item->description,
                    'link' => (string)$item->link,
                    'pubDate' => (string)$item->pubDate,
                    'source' => $feed['name'],
                    'source_id' => $feed['id']
                ];
                
                // Process job with AI deduplication
                $result = $this->processJobWithAI($job);
                if ($result === 'imported') {
                    $jobsImported++;
                } elseif ($result === 'duplicate') {
                    $jobsDuplicates++;
                }
            }
            
        } catch (Exception $e) {
            $this->log("Error processing RSS feed {$feed['name']}: " . $e->getMessage(), 'error');
        }
        
        return [
            'jobs_found' => $jobsFound,
            'jobs_imported' => $jobsImported,
            'jobs_duplicates' => $jobsDuplicates
        ];
    }
    
    /**
     * Process webhook notifications
     */
    private function processWebhookNotifications() {
        try {
            $notifications = $this->db->fetchAll("
                SELECT * FROM webhook_notifications 
                WHERE status = 'pending' AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                ORDER BY created_at ASC
            ");
            
            foreach ($notifications as $notification) {
                $this->processWebhookNotification($notification);
            }
            
        } catch (Exception $e) {
            $this->log("Error processing webhook notifications: " . $e->getMessage(), 'error');
        }
    }
    
    /**
     * Process individual webhook notification
     */
    private function processWebhookNotification($notification) {
        try {
            $jobData = json_decode($notification['job_data'], true);
            
            if ($jobData) {
                $result = $this->processJobWithAI($jobData);
                
                // Update notification status
                $this->db->update("
                    UPDATE webhook_notifications 
                    SET status = ?, processed_at = NOW() 
                    WHERE id = ?
                ", ['processed', $notification['id']]);
                
                $this->log("Webhook notification processed: " . $notification['id'], 'info');
            }
            
        } catch (Exception $e) {
            $this->log("Error processing webhook notification {$notification['id']}: " . $e->getMessage(), 'error');
        }
    }
    
    /**
     * Process job with AI-based deduplication
     */
    private function processJobWithAI($job) {
        try {
            // Check for duplicates using AI semantic similarity
            $isDuplicate = $this->aiService ? $this->aiService->checkJobDuplicate($job) : false;
            
            if ($isDuplicate) {
                return 'duplicate';
            }
            
            // AI-enhanced job processing
            $enhancedJob = $this->aiService ? $this->aiService->enhanceJobContent($job) : $job;
            
            // Save to database
            $this->saveJobToDatabase($enhancedJob);
            
            return 'imported';
            
        } catch (Exception $e) {
            $this->log("Error processing job with AI: " . $e->getMessage(), 'error');
            return 'error';
        }
    }
    
    /**
     * Save job to database
     */
    private function saveJobToDatabase($job) {
        try {
            $this->db->insert("
                INSERT INTO aggregated_jobs (
                    title, description, company, location, salary, 
                    contract_type, domain, source, source_url, 
                    published_date, created_at, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'active')
            ", [
                $job['title'],
                $job['description'],
                $job['company'] ?? '',
                $job['location'] ?? '',
                $job['salary'] ?? '',
                $job['contract_type'] ?? '',
                $job['domain'] ?? '',
                $job['source'] ?? '',
                $job['source_url'] ?? '',
                $job['published_date'] ?? date('Y-m-d H:i:s')
            ]);
            
        } catch (Exception $e) {
            $this->log("Error saving job to database: " . $e->getMessage(), 'error');
            throw $e;
        }
    }
    
    /**
     * Add new RSS feed
     */
    public function addRSSFeed($name, $url, $priority = 1) {
        try {
            $this->db->insert("
                INSERT INTO rss_feeds (name, url, priority, status, created_at) 
                VALUES (?, ?, ?, 'active', NOW())
            ", [$name, $url, $priority]);
            
            $this->log("RSS feed added: $name", 'info');
            return true;
            
        } catch (Exception $e) {
            $this->log("Error adding RSS feed: " . $e->getMessage(), 'error');
            return false;
        }
    }
    
    /**
     * Add webhook endpoint
     */
    public function addWebhookEndpoint($name, $url, $secret) {
        try {
            $this->db->insert("
                INSERT INTO webhook_endpoints (name, url, secret, status, created_at) 
                VALUES (?, ?, ?, 'active', NOW())
            ", [$name, $url, $secret]);
            
            $this->log("Webhook endpoint added: $name", 'info');
            return true;
            
        } catch (Exception $e) {
            $this->log("Error adding webhook endpoint: " . $e->getMessage(), 'error');
            return false;
        }
    }
}
?>


