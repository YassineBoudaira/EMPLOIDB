<?php
/**
 * Job Aggregation Cron Job - Web Accessible Version
 * This is a web-accessible version of the cron job for testing purposes
 * The actual cron job should be run via command line
 */

// Set content type to HTML for web display
header('Content-Type: text/html; charset=utf-8');

// Set time limit for long-running script
set_time_limit(300); // 5 minutes

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Job Aggregation Cron Job - Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .log { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; padding: 15px; margin: 10px 0; font-family: monospace; white-space: pre-wrap; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .info { color: #17a2b8; }
        .header { background: #007bff; color: white; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>🔄 Job Aggregation Cron Job Test</h1>
            <p>This is a web-accessible version for testing purposes. The actual cron job should be run via command line.</p>
        </div>";

try {
    // Include necessary files
    require_once __DIR__ . '/../include/config.php';
    require_once __DIR__ . '/../include/Database.php';
    require_once __DIR__ . '/../include/JobAggregator.php';
    
    echo "<div class='log info'>✓ Configuration files loaded successfully</div>";
    
    // Initialize database connection
    $db = new Database();
    echo "<div class='log info'>✓ Database connection established</div>";
    
    // Initialize job aggregator
    $jobAggregator = new JobAggregator($db);
    echo "<div class='log info'>✓ Job aggregator initialized</div>";
    
    // Log start of cron job
    $startTime = date('Y-m-d H:i:s');
    echo "<div class='log info'>🚀 Starting job aggregation at: $startTime</div>";
    
    // Run aggregation
    $result = $jobAggregator->runAggregation();
    
    // Display results
    echo "<div class='log success'>✅ Job aggregation completed successfully!</div>";
    echo "<div class='log info'>📊 Results Summary:</div>";
    echo "<div class='log info'>   • Total jobs found: " . ($result['total_jobs'] ?? 0) . "</div>";
    echo "<div class='log info'>   • Total imported: " . ($result['total_imported'] ?? 0) . "</div>";
    echo "<div class='log info'>   • Sources processed: " . ($result['sources_processed'] ?? 0) . "</div>";
    
    // Clean up old jobs (older than 30 days)
    $cleanupResult = cleanupOldJobs($db);
    echo "<div class='log info'>🧹 Cleanup completed. Removed " . ($cleanupResult['removed_jobs'] ?? 0) . " old jobs</div>";
    
    $endTime = date('Y-m-d H:i:s');
    echo "<div class='log success'>✅ Job aggregation finished at: $endTime</div>";
    
} catch (Exception $e) {
    echo "<div class='log error'>❌ ERROR: " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<div class='log error'>Stack trace: " . htmlspecialchars($e->getTraceAsString()) . "</div>";
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

echo "
        <div class='log info'>
            <strong>Note:</strong> This is a test version. For production use, set up a proper cron job:
            <br>*/20 * * * * /usr/bin/php " . __DIR__ . "/job_aggregation_cron.php >> " . __DIR__ . "/../logs/cron.log 2>&1
        </div>
    </div>
</body>
</html>";
?>


