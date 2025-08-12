<?php
/**
 * Password Migration Script
 * Migrates existing MD5 passwords to secure Argon2ID hashing
 * 
 * WARNING: This script should only be run once in a secure environment
 * Make sure to backup your database before running this script
 */

require_once 'include/config.php';
require_once 'include/Database.php';
require_once 'include/Security.php';

// Initialize database connection
$db = new Database();

echo "Starting password migration...\n";

try {
    // Get all users with MD5 passwords
    $users = $db->fetchAll("SELECT id, user, pass FROM users WHERE pass REGEXP '^[a-f0-9]{32}$'");
    
    if (empty($users)) {
        echo "No MD5 passwords found to migrate.\n";
        exit;
    }
    
    echo "Found " . count($users) . " users with MD5 passwords.\n";
    
    $migrated = 0;
    $failed = 0;
    
    foreach ($users as $user) {
        try {
            // For MD5 passwords, we need to rehash them
            // Since we can't reverse MD5, we'll set a temporary password
            // Users will need to reset their password on next login
            
            $tempPassword = bin2hex(random_bytes(8)); // Generate temporary password
            $hashedPassword = Security::hashPassword($tempPassword);
            
            // Update the password
            $db->update("UPDATE users SET pass = ? WHERE id = ?", [$hashedPassword, $user['id']]);
            
            echo "Migrated user: {$user['user']} - Temporary password: $tempPassword\n";
            $migrated++;
            
        } catch (Exception $e) {
            echo "Failed to migrate user {$user['user']}: " . $e->getMessage() . "\n";
            $failed++;
        }
    }
    
    echo "\nMigration completed:\n";
    echo "Successfully migrated: $migrated users\n";
    echo "Failed migrations: $failed users\n";
    echo "\nIMPORTANT: Users will need to reset their passwords on next login.\n";
    echo "Please notify all users about this change.\n";
    
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>




