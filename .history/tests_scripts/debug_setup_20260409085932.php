<?php
/**
 * DETAILED ERROR LOGGING
 * Shows what exact error is happening
 */

define('CLI_SCRIPT', true);
$moodle_root = dirname(dirname(__FILE__));

echo "\n";
echo "==============================================\n";
echo "DETAILED ERROR LOGGING\n";
echo "==============================================\n\n";

// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set up custom error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    echo "ERROR [$errno] in $errfile:$errline\n";
    echo "Message: $errstr\n\n";
    return true;
});

// Set up exception handler
set_exception_handler(function($exception) {
    echo "EXCEPTION: " . $exception->getMessage() . "\n";
    echo "File: " . $exception->getFile() . "\n";
    echo "Line: " . $exception->getLine() . "\n";
    echo "Stack: " . $exception->getTraceAsString() . "\n";
});

unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->dbtype    = 'mariadb';
$CFG->dblibrary = 'native';
$CFG->dbhost    = 'localhost';
$CFG->dbname    = 'moodle';
$CFG->dbuser    = 'root';
$CFG->dbpass    = '';
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array (
  'dbpersist' => 0,
  'dbport' => '',
  'dbsocket' => '',
  'dbcollation' => 'utf8mb4_general_ci',
);

$CFG->wwwroot   = 'http://localhost';
$CFG->dataroot  = 'E:\\moodel_xampp\\moodledata';
$CFG->admin     = 'admin';
$CFG->directorypermissions = 0777;

echo "[LOADING] lib/setup.php...\n\n";

try {
    ob_start();
    require_once($moodle_root . '/lib/setup.php');
    $output = ob_get_clean();
    
    if (!empty($output)) {
        echo "Setup output:\n$output\n";
    }
    
    echo "✓ Setup successful\n";
    
} catch (Exception $e) {
    $output = ob_get_clean();
    echo "Setup failed with exception:\n";
    echo $e->getMessage() . "\n";
    if (!empty($output)) {
        echo "\nSetup output:\n$output\n";
    }
}

echo "\n==============================================\n";

?>
