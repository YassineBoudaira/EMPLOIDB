<?php
/**
 * AI Service Class
 * Handles AI-powered features for job matching, deduplication, and content enhancement
 * Supports French, Arabic, and English languages
 */

class AIService {
    private $db;
    private $config;
    private $similarityThreshold = 0.85; // 85% similarity threshold for duplicates
    
    public function __construct($database) {
        $this->db = $database;
        $this->loadConfig();
    }
    
    /**
     * Load AI service configuration
     */
    private function loadConfig() {
        try {
            $settings = $this->db->fetchAll("SELECT setting_key, setting_value FROM system_settings WHERE category = 'ai'");
            foreach ($settings as $setting) {
                $this->config[$setting['setting_key']] = $setting['setting_value'];
            }
            
            // Set similarity threshold
            if (isset($this->config['similarity_threshold'])) {
                $this->similarityThreshold = (float)$this->config['similarity_threshold'];
            }
            
        } catch (Exception $e) {
            error_log("Error loading AI config: " . $e->getMessage());
        }
    }
    
    /**
     * Check if job is a duplicate using semantic similarity
     */
    public function checkJobDuplicate($job) {
        try {
            // Get recent jobs for comparison
            $recentJobs = $this->db->fetchAll("
                SELECT id, title, description, company, location 
                FROM aggregated_jobs 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                ORDER BY created_at DESC 
                LIMIT 100
            ");
            
            foreach ($recentJobs as $existingJob) {
                $similarity = $this->calculateJobSimilarity($job, $existingJob);
                
                if ($similarity >= $this->similarityThreshold) {
                    $this->logDuplicateDetection($job, $existingJob, $similarity);
                    return true;
                }
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Error checking job duplicate: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Calculate similarity between two jobs
     */
    private function calculateJobSimilarity($job1, $job2) {
        $similarity = 0;
        $weights = [
            'title' => 0.4,
            'company' => 0.3,
            'location' => 0.2,
            'description' => 0.1
        ];
        
        // Title similarity
        $titleSimilarity = $this->calculateTextSimilarity(
            $job1['title'] ?? '', 
            $job2['title'] ?? ''
        );
        $similarity += $titleSimilarity * $weights['title'];
        
        // Company similarity
        $companySimilarity = $this->calculateTextSimilarity(
            $job1['company'] ?? '', 
            $job2['company'] ?? ''
        );
        $similarity += $companySimilarity * $weights['company'];
        
        // Location similarity
        $locationSimilarity = $this->calculateTextSimilarity(
            $job1['location'] ?? '', 
            $job2['location'] ?? ''
        );
        $similarity += $locationSimilarity * $weights['location'];
        
        // Description similarity (simplified)
        $descSimilarity = $this->calculateTextSimilarity(
            substr($job1['description'] ?? '', 0, 200), 
            substr($job2['description'] ?? '', 0, 200)
        );
        $similarity += $descSimilarity * $weights['description'];
        
        return $similarity;
    }
    
    /**
     * Calculate text similarity using multiple algorithms
     */
    private function calculateTextSimilarity($text1, $text2) {
        if (empty($text1) || empty($text2)) {
            return 0;
        }
        
        // Normalize text
        $text1 = $this->normalizeText($text1);
        $text2 = $this->normalizeText($text2);
        
        // Exact match
        if ($text1 === $text2) {
            return 1.0;
        }
        
        // Levenshtein distance similarity
        $levenshteinSimilarity = 1 - (levenshtein($text1, $text2) / max(strlen($text1), strlen($text2)));
        
        // Jaccard similarity (word-based)
        $words1 = array_unique(explode(' ', $text1));
        $words2 = array_unique(explode(' ', $text2));
        $intersection = count(array_intersect($words1, $words2));
        $union = count(array_unique(array_merge($words1, $words2)));
        $jaccardSimilarity = $union > 0 ? $intersection / $union : 0;
        
        // Combined similarity
        return ($levenshteinSimilarity + $jaccardSimilarity) / 2;
    }
    
    /**
     * Normalize text for comparison
     */
    private function normalizeText($text) {
        // Convert to lowercase
        $text = strtolower($text);
        
        // Remove special characters
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
        
        // Remove extra whitespace
        $text = preg_replace('/\s+/', ' ', trim($text));
        
        return $text;
    }
    
    /**
     * Enhance job content using AI
     */
    public function enhanceJobContent($job) {
        try {
            $enhancedJob = $job;
            
            // Clean and standardize title
            $enhancedJob['title'] = $this->cleanJobTitle($job['title'] ?? '');
            
            // Extract and standardize company name
            $enhancedJob['company'] = $this->extractCompanyName($job);
            
            // Extract and standardize location
            $enhancedJob['location'] = $this->extractLocation($job);
            
            // Extract salary information
            $enhancedJob['salary'] = $this->extractSalary($job['description'] ?? '');
            
            // Extract contract type
            $enhancedJob['contract_type'] = $this->extractContractType($job);
            
            // Extract domain/category
            $enhancedJob['domain'] = $this->extractDomain($job);
            
            // Clean description
            $enhancedJob['description'] = $this->cleanDescription($job['description'] ?? '');
            
            return $enhancedJob;
            
        } catch (Exception $e) {
            error_log("Error enhancing job content: " . $e->getMessage());
            return $job;
        }
    }
    
    /**
     * Clean and standardize job title
     */
    private function cleanJobTitle($title) {
        // Remove common prefixes/suffixes
        $title = preg_replace('/^(emploi|job|poste|offre)\s*/i', '', $title);
        $title = preg_replace('/\s*(emploi|job|poste|offre)$/i', '', $title);
        
        // Capitalize properly
        $title = ucwords(strtolower($title));
        
        return trim($title);
    }
    
    /**
     * Extract company name from job data
     */
    private function extractCompanyName($job) {
        // Try to get company from dedicated field first
        if (!empty($job['company'])) {
            return $this->cleanCompanyName($job['company']);
        }
        
        // Extract from title or description
        $text = ($job['title'] ?? '') . ' ' . ($job['description'] ?? '');
        
        // Common company patterns
        $patterns = [
            '/chez\s+([A-Z][a-zA-Z\s&]+)/i',
            '/at\s+([A-Z][a-zA-Z\s&]+)/i',
            '/company:\s*([A-Z][a-zA-Z\s&]+)/i',
            '/entreprise:\s*([A-Z][a-zA-Z\s&]+)/i'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return $this->cleanCompanyName(trim($matches[1]));
            }
        }
        
        return '';
    }
    
    /**
     * Clean company name
     */
    private function cleanCompanyName($company) {
        $company = trim($company);
        $company = preg_replace('/\s+(sarl|sa|sas|eurl|sci|gmbh|inc|ltd|llc)$/i', '', $company);
        return ucwords(strtolower($company));
    }
    
    /**
     * Extract location from job data
     */
    private function extractLocation($job) {
        if (!empty($job['location'])) {
            return $this->cleanLocation($job['location']);
        }
        
        $text = ($job['title'] ?? '') . ' ' . ($job['description'] ?? '');
        
        // Moroccan cities
        $moroccanCities = [
            'Casablanca', 'Rabat', 'Marrakech', 'Fès', 'Agadir', 'Tanger', 'Meknès', 
            'Oujda', 'Kénitra', 'Tétouan', 'Safi', 'Mohammedia', 'Khouribga', 'Beni Mellal',
            'El Jadida', 'Taza', 'Nador', 'Settat', 'Larache', 'Ksar El Kebir'
        ];
        
        foreach ($moroccanCities as $city) {
            if (stripos($text, $city) !== false) {
                return $city;
            }
        }
        
        return '';
    }
    
    /**
     * Clean location
     */
    private function cleanLocation($location) {
        $location = trim($location);
        $location = preg_replace('/\s*,\s*.*$/', '', $location); // Remove country/region
        return ucwords(strtolower($location));
    }
    
    /**
     * Extract salary information
     */
    private function extractSalary($description) {
        $patterns = [
            '/(\d+[\s,]*\d*)\s*(dh|mad|dirhams?)/i',
            '/(\d+[\s,]*\d*)\s*(€|euros?)/i',
            '/(\d+[\s,]*\d*)\s*(\$|dollars?)/i',
            '/salaire[:\s]*(\d+[\s,]*\d*)/i',
            '/salary[:\s]*(\d+[\s,]*\d*)/i'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $description, $matches)) {
                return trim($matches[1] . ' ' . ($matches[2] ?? 'DH'));
            }
        }
        
        return '';
    }
    
    /**
     * Extract contract type
     */
    private function extractContractType($job) {
        $text = strtolower(($job['title'] ?? '') . ' ' . ($job['description'] ?? ''));
        
        $contractTypes = [
            'cdi' => ['cdi', 'contrat à durée indéterminée', 'permanent'],
            'cdd' => ['cdd', 'contrat à durée déterminée', 'temporary'],
            'stage' => ['stage', 'internship', 'stagiare'],
            'freelance' => ['freelance', 'freelancer', 'indépendant'],
            'temps partiel' => ['temps partiel', 'part time', 'mi-temps'],
            'temps plein' => ['temps plein', 'full time', 'plein temps']
        ];
        
        foreach ($contractTypes as $type => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($text, $keyword) !== false) {
                    return $type;
                }
            }
        }
        
        return '';
    }
    
    /**
     * Extract domain/category
     */
    private function extractDomain($job) {
        $text = strtolower(($job['title'] ?? '') . ' ' . ($job['description'] ?? ''));
        
        $domains = [
            'informatique' => ['développeur', 'programmeur', 'informaticien', 'it', 'software', 'developer'],
            'marketing' => ['marketing', 'communication', 'publicité', 'promotion'],
            'finance' => ['comptable', 'finance', 'comptabilité', 'audit'],
            'ressources humaines' => ['rh', 'ressources humaines', 'hr', 'recrutement'],
            'vente' => ['vendeur', 'commercial', 'vente', 'sales'],
            'santé' => ['médecin', 'infirmier', 'santé', 'médical'],
            'éducation' => ['professeur', 'enseignant', 'éducation', 'formation']
        ];
        
        foreach ($domains as $domain => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($text, $keyword) !== false) {
                    return $domain;
                }
            }
        }
        
        return '';
    }
    
    /**
     * Clean job description
     */
    private function cleanDescription($description) {
        // Remove HTML tags
        $description = strip_tags($description);
        
        // Remove extra whitespace
        $description = preg_replace('/\s+/', ' ', $description);
        
        // Remove common unwanted text
        $description = preg_replace('/^(emploi|job|poste|offre)\s*/i', '', $description);
        
        return trim($description);
    }
    
    /**
     * Match candidates to jobs using AI
     */
    public function matchCandidatesToJob($jobId, $limit = 10) {
        try {
            $job = $this->db->fetch("SELECT * FROM annonces WHERE id = ?", [$jobId]);
            if (!$job) {
                return [];
            }
            
            // Get candidate profiles
            $candidates = $this->db->fetchAll("
                SELECT p.*, u.email, u.id as user_id
                FROM profiles p
                JOIN users u ON p.user_id = u.id
                WHERE u.role = 'user'
                ORDER BY p.created_at DESC
                LIMIT 100
            ");
            
            $matches = [];
            
            foreach ($candidates as $candidate) {
                $score = $this->calculateCandidateJobMatch($candidate, $job);
                
                if ($score > 0.3) { // 30% minimum match
                    $matches[] = [
                        'candidate' => $candidate,
                        'score' => $score
                    ];
                }
            }
            
            // Sort by score
            usort($matches, function($a, $b) {
                return $b['score'] <=> $a['score'];
            });
            
            return array_slice($matches, 0, $limit);
            
        } catch (Exception $e) {
            error_log("Error matching candidates to job: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Calculate candidate-job match score
     */
    private function calculateCandidateJobMatch($candidate, $job) {
        $score = 0;
        
        // Location match
        if (!empty($candidate['ville_id']) && !empty($job['ville_id'])) {
            if ($candidate['ville_id'] == $job['ville_id']) {
                $score += 0.3;
            }
        }
        
        // Domain match (simplified)
        $candidateText = strtolower($candidate['nom'] . ' ' . $candidate['prenom']);
        $jobText = strtolower($job['titre'] . ' ' . $job['description']);
        
        $domainScore = $this->calculateTextSimilarity($candidateText, $jobText);
        $score += $domainScore * 0.7;
        
        return min($score, 1.0);
    }
    
    /**
     * Predict salary for job
     */
    public function predictSalary($job) {
        try {
            // Get similar jobs for salary prediction
            $similarJobs = $this->db->fetchAll("
                SELECT salaire, ville_id, domaine_id
                FROM annonces 
                WHERE domaine_id = ? AND ville_id = ? AND salaire IS NOT NULL AND salaire != ''
                ORDER BY created_at DESC
                LIMIT 20
            ", [$job['domaine_id'] ?? 0, $job['ville_id'] ?? 0]);
            
            if (empty($similarJobs)) {
                return null;
            }
            
            $salaries = [];
            foreach ($similarJobs as $similarJob) {
                $salary = $this->extractNumericSalary($similarJob['salaire']);
                if ($salary > 0) {
                    $salaries[] = $salary;
                }
            }
            
            if (empty($salaries)) {
                return null;
            }
            
            // Calculate average salary
            $averageSalary = array_sum($salaries) / count($salaries);
            
            return [
                'predicted_salary' => round($averageSalary),
                'salary_range' => [
                    'min' => round(min($salaries)),
                    'max' => round(max($salaries))
                ],
                'sample_size' => count($salaries)
            ];
            
        } catch (Exception $e) {
            error_log("Error predicting salary: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Extract numeric salary from text
     */
    private function extractNumericSalary($salaryText) {
        if (preg_match('/(\d+[\s,]*\d*)/', $salaryText, $matches)) {
            return (float)str_replace(',', '', $matches[1]);
        }
        return 0;
    }
    
    /**
     * Log duplicate detection
     */
    private function logDuplicateDetection($newJob, $existingJob, $similarity) {
        try {
            $this->db->insert("
                INSERT INTO duplicate_detection_logs (new_job_title, existing_job_id, similarity_score, created_at) 
                VALUES (?, ?, ?, NOW())
            ", [$newJob['title'], $existingJob['id'], $similarity]);
        } catch (Exception $e) {
            error_log("Error logging duplicate detection: " . $e->getMessage());
        }
    }
}
?>
