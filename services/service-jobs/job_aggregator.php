<?php
/**
 * Job Aggregator Service
 * Handles job aggregation from multiple sources and publishes to message queue
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Exchange\AMQPExchangeType;

class JobAggregator {
    private $connection;
    private $channel;
    private $db;
    private $sources = [];
    
    public function __construct($database) {
        $this->db = $database;
        $this->connectRabbitMQ();
        $this->loadJobSources();
    }
    
    /**
     * Connect to RabbitMQ
     */
    private function connectRabbitMQ() {
        try {
            $this->connection = new AMQPStreamConnection(
                $_ENV['RABBITMQ_HOST'] ?? 'localhost',
                $_ENV['RABBITMQ_PORT'] ?? 5672,
                $_ENV['RABBITMQ_USERNAME'] ?? 'guest',
                $_ENV['RABBITMQ_PASSWORD'] ?? 'guest'
            );
            $this->channel = $this->connection->channel();
            
            // Declare exchanges
            $this->channel->exchange_declare('raw_jobs', AMQPExchangeType::TOPIC, false, true, false);
            $this->channel->exchange_declare('raw_offers', AMQPExchangeType::TOPIC, false, true, false);
            $this->channel->exchange_declare('job_ready', AMQPExchangeType::TOPIC, false, true, false);
            
            echo "Connected to RabbitMQ successfully\n";
        } catch (Exception $e) {
            echo "Failed to connect to RabbitMQ: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
    
    /**
     * Load job sources configuration
     */
    private function loadJobSources() {
        $this->sources = [
            [
                'name' => 'Job Site 1',
                'api_url' => $_ENV['JOB_SITE_1_API_URL'] ?? '',
                'api_key' => $_ENV['JOB_SITE_1_API_KEY'] ?? '',
                'enabled' => $_ENV['JOB_SITE_1_ENABLED'] ?? false,
                'fetch_interval' => 300, // 5 minutes
                'last_fetch' => null
            ],
            [
                'name' => 'Job Site 2',
                'api_url' => $_ENV['JOB_SITE_2_API_URL'] ?? '',
                'api_key' => $_ENV['JOB_SITE_2_API_KEY'] ?? '',
                'enabled' => $_ENV['JOB_SITE_2_ENABLED'] ?? false,
                'fetch_interval' => 300,
                'last_fetch' => null
            ],
            [
                'name' => 'Job Site 3',
                'api_url' => $_ENV['JOB_SITE_3_API_URL'] ?? '',
                'api_key' => $_ENV['JOB_SITE_3_API_KEY'] ?? '',
                'enabled' => $_ENV['JOB_SITE_3_ENABLED'] ?? false,
                'fetch_interval' => 300,
                'last_fetch' => null
            ],
            [
                'name' => 'Job Site 4',
                'api_url' => $_ENV['JOB_SITE_4_API_URL'] ?? '',
                'api_key' => $_ENV['JOB_SITE_4_API_KEY'] ?? '',
                'enabled' => $_ENV['JOB_SITE_4_ENABLED'] ?? false,
                'fetch_interval' => 300,
                'last_fetch' => null
            ],
            [
                'name' => 'Job Site 5',
                'api_url' => $_ENV['JOB_SITE_5_API_URL'] ?? '',
                'api_key' => $_ENV['JOB_SITE_5_API_KEY'] ?? '',
                'enabled' => $_ENV['JOB_SITE_5_ENABLED'] ?? false,
                'fetch_interval' => 300,
                'last_fetch' => null
            ],
            [
                'name' => 'Job Site 6',
                'api_url' => $_ENV['JOB_SITE_6_API_URL'] ?? '',
                'api_key' => $_ENV['JOB_SITE_6_API_KEY'] ?? '',
                'enabled' => $_ENV['JOB_SITE_6_ENABLED'] ?? false,
                'fetch_interval' => 300,
                'last_fetch' => null
            ]
        ];
    }
    
    /**
     * Fetch jobs from all enabled sources
     */
    public function fetchAllJobs() {
        echo "Starting job aggregation process...\n";
        
        foreach ($this->sources as $source) {
            if (!$source['enabled']) {
                echo "Skipping disabled source: {$source['name']}\n";
                continue;
            }
            
            try {
                $this->fetchJobsFromSource($source);
            } catch (Exception $e) {
                echo "Error fetching from {$source['name']}: " . $e->getMessage() . "\n";
                $this->logError($source['name'], $e->getMessage());
            }
        }
        
        echo "Job aggregation process completed.\n";
    }
    
    /**
     * Fetch jobs from a specific source
     */
    private function fetchJobsFromSource($source) {
        echo "Fetching jobs from: {$source['name']}\n";
        
        // Simulate API call (replace with actual API integration)
        $jobs = $this->simulateApiCall($source);
        
        foreach ($jobs as $job) {
            $this->processJob($job, $source);
        }
        
        // Update last fetch time
        $this->updateLastFetchTime($source['name']);
    }
    
    /**
     * Simulate API call (replace with actual implementation)
     */
    private function simulateApiCall($source) {
        // This is a placeholder - replace with actual API calls
        return [
            [
                'external_id' => uniqid(),
                'title' => 'Software Developer - ' . $source['name'],
                'description' => 'Looking for an experienced software developer...',
                'company' => 'Tech Company ' . rand(1, 100),
                'location' => 'Casablanca, Morocco',
                'salary_min' => 8000,
                'salary_max' => 15000,
                'currency' => 'MAD',
                'job_type' => 'full-time',
                'remote_work' => 'hybrid',
                'experience_level' => 'mid',
                'education_level' => 'bachelor',
                'url' => 'https://example.com/job/' . uniqid(),
                'source' => $source['name'],
                'published_at' => date('Y-m-d H:i:s'),
                'expires_at' => date('Y-m-d H:i:s', strtotime('+30 days'))
            ],
            [
                'external_id' => uniqid(),
                'title' => 'Marketing Manager - ' . $source['name'],
                'description' => 'Seeking a creative marketing manager...',
                'company' => 'Marketing Agency ' . rand(1, 50),
                'location' => 'Rabat, Morocco',
                'salary_min' => 6000,
                'salary_max' => 12000,
                'currency' => 'MAD',
                'job_type' => 'full-time',
                'remote_work' => 'on-site',
                'experience_level' => 'senior',
                'education_level' => 'master',
                'url' => 'https://example.com/job/' . uniqid(),
                'source' => $source['name'],
                'published_at' => date('Y-m-d H:i:s'),
                'expires_at' => date('Y-m-d H:i:s', strtotime('+30 days'))
            ]
        ];
    }
    
    /**
     * Process individual job
     */
    private function processJob($job, $source) {
        // Check for duplicates
        if ($this->isDuplicate($job)) {
            echo "Duplicate job found: {$job['title']} from {$source['name']}\n";
            $this->logDuplicate($job, $source);
            return;
        }
        
        // Store raw job data
        $rawJobId = $this->storeRawJob($job, $source);
        
        // Publish to message queue for AI processing
        $this->publishRawJob($job, $source, $rawJobId);
        
        echo "Processed job: {$job['title']} from {$source['name']}\n";
    }
    
    /**
     * Check if job is duplicate
     */
    private function isDuplicate($job) {
        $hash = $this->generateJobHash($job);
        
        $existing = $this->db->fetch(
            "SELECT id FROM raw_jobs WHERE job_hash = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)",
            [$hash]
        );
        
        return $existing !== null;
    }
    
    /**
     * Generate hash for job deduplication
     */
    private function generateJobHash($job) {
        $hashString = $job['title'] . '|' . $job['company'] . '|' . $job['location'];
        return hash('sha256', $hashString);
    }
    
    /**
     * Store raw job data
     */
    private function storeRawJob($job, $source) {
        $hash = $this->generateJobHash($job);
        
        return $this->db->insert(
            "INSERT INTO raw_jobs (
                external_id, title, description, company, location,
                salary_min, salary_max, currency, job_type, remote_work,
                experience_level, education_level, url, source, job_hash,
                published_at, expires_at, raw_data, status, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())",
            [
                $job['external_id'],
                $job['title'],
                $job['description'],
                $job['company'],
                $job['location'],
                $job['salary_min'],
                $job['salary_max'],
                $job['currency'],
                $job['job_type'],
                $job['remote_work'],
                $job['experience_level'],
                $job['education_level'],
                $job['url'],
                $source['name'],
                $hash,
                $job['published_at'],
                $job['expires_at'],
                json_encode($job)
            ]
        );
    }
    
    /**
     * Publish raw job to message queue
     */
    private function publishRawJob($job, $source, $rawJobId) {
        $message = [
            'raw_job_id' => $rawJobId,
            'job_data' => $job,
            'source' => $source,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $msg = new AMQPMessage(
            json_encode($message),
            ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
        );
        
        $this->channel->basic_publish($msg, 'raw_jobs', 'job.new');
        echo "Published raw job to queue: {$job['title']}\n";
    }
    
    /**
     * Update last fetch time for source
     */
    private function updateLastFetchTime($sourceName) {
        $this->db->update(
            "UPDATE job_sources SET last_fetch = NOW() WHERE name = ?",
            [$sourceName]
        );
    }
    
    /**
     * Log duplicate job
     */
    private function logDuplicate($job, $source) {
        $this->db->insert(
            "INSERT INTO duplicate_jobs (job_hash, source, job_data, created_at) VALUES (?, ?, ?, NOW())",
            [$this->generateJobHash($job), $source['name'], json_encode($job)]
        );
    }
    
    /**
     * Log error
     */
    private function logError($source, $error) {
        $this->db->insert(
            "INSERT INTO job_aggregation_errors (source, error_message, created_at) VALUES (?, ?, NOW())",
            [$source, $error]
        );
    }
    
    /**
     * Close connections
     */
    public function close() {
        if ($this->channel) {
            $this->channel->close();
        }
        if ($this->connection) {
            $this->connection->close();
        }
    }
}

// CLI usage
if (php_sapi_name() === 'cli') {
    // Load environment variables
    if (file_exists(__DIR__ . '/../.env')) {
        $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                list($key, $value) = explode('=', $line, 2);
                $_ENV[trim($key)] = trim($value);
            }
        }
    }
    
    // Initialize database connection
    $db = new PDO(
        "mysql:host=" . ($_ENV['DB_HOST'] ?? 'localhost') . ";dbname=" . ($_ENV['DB_NAME'] ?? 'emploi') . ";charset=utf8mb4",
        $_ENV['DB_USERNAME'] ?? 'root',
        $_ENV['DB_PASSWORD'] ?? '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Create job aggregator and run
    $aggregator = new JobAggregator($db);
    $aggregator->fetchAllJobs();
    $aggregator->close();
}
