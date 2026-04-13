<?php
/**
 * Config Test - Logs to file
 */

$logfile = dirname(__FILE__) . '/error_log_output.txt';

ob_start();

try {
    define('CLI_SCRIPT', false);
    $moodle_root = dirname(__FILE__);
    
    file_put_contents($logfile, "=== MOODLE CONFIG TEST ===\n");
    file_put_contents($logfile, "Time: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
    file_put_contents($logfile, "Moodle Root: $moodle_root\n", FILE_APPEND);
    file_put_contents($logfile, "Config File: $moodle_root/config.php\n", FILE_APPEND);
    file_put_contents($logfile, "\n--- Attempting to load config.php ---\n", FILE_APPEND);
    
    if (!file_exists($moodle_root . '/config.php')) {
        file_put_contents($logfile, "ERROR: config.php NOT FOUND!\n", FILE_APPEND);
        echo "Error logged. Check error_log_output.txt";
        exit;
    }
    
    // Try loading
    require_once($moodle_root . '/config.php');
    
    file_put_contents($logfile, "SUCCESS: config.php loaded\n", FILE_APPEND);
    
    if (isset($CFG)) {
        file_put_contents($logfile, "\n--- Config Values ---\n", FILE_APPEND);
        file_put_contents($logfile, "DBHost: " . $CFG->dbhost . "\n", FILE_APPEND);
        file_put_contents($logfile, "DBName: " . $CFG->dbname . "\n", FILE_APPEND);
        file_put_contents($logfile, "DBUser: " . $CFG->dbuser . "\n", FILE_APPEND);
        file_put_contents($logfile, "DBPrefix: " . $CFG->prefix . "\n", FILE_APPEND);
    }
    
} catch (Throwable $e) {
    file_put_contents($logfile, "EXCEPTION: " . $e->getMessage() . "\n", FILE_APPEND);
    file_put_contents($logfile, "File: " . $e->getFile() . "\n", FILE_APPEND);
    file_put_contents($logfile, "Line: " . $e->getLine() . "\n", FILE_APPEND);
    file_put_contents($logfile, "Trace:\n" . $e->getTraceAsString() . "\n", FILE_APPEND);
}

$output = ob_get_clean();

if (!empty($output)) {
    file_put_contents($logfile, "\n--- Output During Load ---\n" . $output . "\n", FILE_APPEND);
}

// Now show the log file
echo "<pre>";
echo file_get_contents($logfile);
echo "</pre>";

?>
