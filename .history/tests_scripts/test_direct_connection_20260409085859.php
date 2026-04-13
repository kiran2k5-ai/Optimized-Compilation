<?php
/**
 * TEST DIRECT CONNECTION WITH MOODLE CONFIG
 * Bypasses lib/setup.php to isolate the issue
 */

define('CLI_SCRIPT', true);
$moodle_root = dirname(dirname(__FILE__));

echo "\n";
echo "==============================================\n";
echo "DIRECT CONNECTION TEST (No setup.php)\n";
echo "==============================================\n\n";

// Load config but skip setup.php
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

echo "[1] Testing basic MySQLi connection...\n";

try {
    $mysqli = new mysqli(
        $CFG->dbhost,
        $CFG->dbuser,
        $CFG->dbpass,
        $CFG->dbname
    );
    
    if ($mysqli->connect_error) {
        echo "  ✗ Connection failed: " . $mysqli->connect_error . "\n";
        exit(1);
    }
    
    echo "  ✓ Connected to: " . $CFG->dbhost . "\n";
    echo "  ✓ Database: " . $CFG->dbname . "\n";
    
    // Test query
    $result = $mysqli->query("SELECT 1");
    if ($result) {
        echo "  ✓ Query test successful\n";
        $mysqli->close();
    } else {
        echo "  ✗ Query failed: " . $mysqli->error . "\n";
        exit(1);
    }
    
} catch (Exception $e) {
    echo "  ✗ Exception: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n[2] Now trying to load setup.php (THIS may cause the error)...\n";

try {
    require_once($moodle_root . '/lib/setup.php');
    echo "  ✓ setup.php loaded successfully\n";
} catch (Exception $e) {
    echo "  ✗ setup.php failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n[3] Testing Moodle database object...\n";

try {
    global $DB;
    
    $count = $DB->count_records('user');
    echo "  ✓ Database object works\n";
    echo "  ✓ Users: $count\n";
    
} catch (Exception $e) {
    echo "  ✗ Failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n✓ ALL TESTS PASSED\n\n";

?>
