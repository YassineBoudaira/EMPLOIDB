<?php
/**
 * Job Aggregation Cron Job
 * Runs automatically to scrape jobs from various sources
 * Should be called every 20 minutes via cron job
 */

// Set time limit for long-running script
set_time_limit(300); // 5 minutes

// Include necessary files
require_once __DIR__ . '/../include/config.php';
require_once __DIR__ . '/../include/Database.php';
require_once __DIR__ . '/../include/JobAggregator.php';

// Initialize database connection
$db = new Database();

// Initialize job aggregator
$jobAggregator = new JobAggregator($db);

// Log start of cron job
$logMessage = "[" . date('Y-m-d H:i:s') . "] Starting job aggregation cron job\n";
file_put_contents(LOG_PATH . 'cron.log', $logMessage, FILE_APPEND | LOCK_EX);

try {
    // Run aggregation
    $result = $jobAggregator->runAggregation();
    
    // Log success
    $logMessage = "[" . date('Y-m-d H:i:s') . "] Job aggregation completed successfully. ";
    $logMessage .= "Total jobs found: {$result['total_jobs']}, ";
    $logMessage .= "Total imported: {$result['total_imported']}, ";
    $logMessage .= "Sources processed: {$result['sources_processed']}\n";
    file_put_contents(LOG_PATH . 'cron.log', $logMessage, FILE_APPEND | LOCK_EX);
    
    // Clean up old jobs (older than 30 days)
    $cleanupResult = cleanupOldJobs($db);
    
    $logMessage = "[" . date('Y-m-d H:i:s') . "] Cleanup completed. Removed {$cleanupResult['removed_jobs']} old jobs\n";
    file_put_contents(LOG_PATH . 'cron.log', $logMessage, FILE_APPEND | LOCK_EX);
    
} catch (Exception $e) {
    // Log error
    $logMessage = "[" . date('Y-m-d H:i:s') . "] ERROR: " . $e->getMessage() . "\n";
    file_put_contents(LOG_PATH . 'cron.log', $logMessage, FILE_APPEND | LOCK_EX);
    
    // Send error notification (if configured)
    sendErrorNotification($e->getMessage());
}

/**
 * Clean up old aggregated jobs
 */
function cleanupOldJobs($db) {
    try {
        // Remove jobs older than 30 days that haven't been imported
        $removedJobs = $db->delete(
            "DELETE FROM aggregated_jobs WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY) AND imported_to_annonces = 0"
        );
        
        // Remove old scraping logs (older than 7 days)
        $db->delete(
            "DELETE FROM scraping_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)"
        );
        
        return ['removed_jobs' => $removedJobs];
        
    } catch (Exception $e) {
        error_log("Error during cleanup: " . $e->getMessage());
        return ['removed_jobs' => 0];
    }
}

/**
 * Send error notification
 */
function sendErrorNotification($errorMessage) {
    try {
        // Get admin email from settings
        $db = new Database();
        $adminEmail = $db->fetch("SELECT setting_value FROM system_settings WHERE setting_key = 'admin_email'");
        
        if ($adminEmail && !empty($adminEmail['setting_value'])) {
            $subject = "Job Aggregation Error - " . date('Y-m-d H:i:s');
            $message = "An error occurred during job aggregation:\n\n" . $errorMessage;
            $headers = "From: noreply@jobmaroc.ma\r\n";
            
            mail($adminEmail['setting_value'], $subject, $message, $headers);
        }
    } catch (Exception $e) {
        error_log("Error sending notification: " . $e->getMessage());
    }
}

// Log end of cron job
$logMessage = "[" . date('Y-m-d H:i:s') . "] Job aggregation cron job finished\n";
file_put_contents(LOG_PATH . 'cron.log', $logMessage, FILE_APPEND | LOCK_EX);
?>


