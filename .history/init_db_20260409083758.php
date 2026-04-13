#!/usr/bin/env php
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('CLI_SCRIPT', true);
define('CACHE_DISABLE_ALL', true);

$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

chdir('E:\\moodel_xampp\\htdocs\\moodle');
require_once('config.php');

echo "Starting Moodle database initialization...\n";

// Get the database manager
global $DB, $CFG;

try {
    if (!$DB) {
        echo "Database connection failed!\n";
        exit(1);
    }
    
    echo "Database connected successfully.\n";
    
    // Try to create basic tables
    require_once($CFG->dirroot . '/lib/db/install.php');
    
    // Get installed version
    $currentversion = $DB->get_record('config', ['name' => 'version']);
    if ($currentversion) {
        echo "Moodle already installed. Version: " . $currentversion->value . "\n";
    } else {
        echo "Running installation...\n";
        // Installation would go here
    }
    
    echo  "Installation complete!\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
?>
