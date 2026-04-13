<?php
/**
 * Error Logger
 * Captures actual errors from config.php loading
 */

// Start output buffering to capture any output
ob_start();

// Enable error reporting to see what's wrong
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Custom error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    global $error_log;
    $error_log[] = "[$errno] $errstr in $errfile:$errline";
    return false;  // Continue with default handler
}, E_ALL);

// Capture exceptions
set_exception_handler(function($e) {
    global $error_log;
    $error_log[] = "EXCEPTION: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine();
    display_errors($error_log);
});

global $error_log;
$error_log = [];

$moodle_root = dirname(__FILE__);

try {
    define('CLI_SCRIPT', false);
    define('AJAX_SCRIPT', false);
    
    // Log: Starting config load
    $error_log[] = "Loading config.php from: $moodle_root/config.php";
    
    if (!file_exists($moodle_root . '/config.php')) {
        $error_log[] = "ERROR: config.php not found!";
        display_errors($error_log);
        exit;
    }
    
    // Try to load config
    require_once($moodle_root . '/config.php');
    
    $error_log[] = "✓ config.php loaded successfully";
    $error_log[] = "Database: " . $CFG->dbhost . " / " . $CFG->dbname;
    
} catch (Throwable $e) {
    $error_log[] = "FATAL ERROR: " . $e->getMessage();
    $error_log[] = "File: " . $e->getFile() . ":" . $e->getLine();
    $error_log[] = "Trace: " . $e->getTraceAsString();
    display_errors($error_log);
    exit;
}

// Clean output buffer
$output = ob_get_clean();

if (!empty($output)) {
    $error_log[] = "Output during load: " . $output;
}

// Display all captured errors
display_errors($error_log);

function display_errors($errors) {
    echo "<html><head><title>Moodle Error Log</title>";
    echo "<style>body { font-family: monospace; padding: 20px; } ";
    echo ".error { color: red; } .success { color: green; } .warning { color: orange; }</style>";
    echo "</head><body>";
    echo "<h1>Moodle Error Log</h1>";
    echo "<hr>";
    
    foreach ($errors as $error) {
        if (strpos($error, '✓') === 0) {
            echo "<p class='success'>" . htmlspecialchars($error) . "</p>";
        } else if (strpos($error, 'ERROR') !== false || strpos($error, 'EXCEPTION') !== false || strpos($error, 'FATAL') !== false) {
            echo "<p class='error'>" . htmlspecialchars($error) . "</p>";
        } else {
            echo "<p>" . htmlspecialchars($error) . "</p>";
        }
    }
    
    echo "</body></html>";
}

?>
