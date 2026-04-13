<?php
// Minimal test of Moodle database initialization

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set up CFG
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

echo "<h1>Testing Moodle Database Driver</h1>";
echo "<hr>";

echo "<h2>Step 1: Loading database factory</h2>";

try {
    // Load the database factory
    require_once('lib/db/mdb2/database_manager.php');
    require_once('lib/db/mdb2/mdb2.php');
    require_once('lib/db/factories.php');
    
    echo "<p style='color:green;'>✓ Database factory files loaded</p>";
} catch (Exception $e) {
    echo "<p style='color:red;'>✗ Failed: " . $e->getMessage() . "</p>";
    exit;
}

echo "<h2>Step 2: Creating database connection</h2>";

try {
    global $DB;
   
    // Try to get database factory
    $drivername = $CFG->dbtype;
    $driver = 'pdo' === $CFG->dblibrary ? 'pdo_' . $drivername : $drivername;
    
    // Get the connection factory function
    $factory = 'moodle_database_' . $driver;
    
    echo "<p>Driver: $driver</p>";
    echo "<p>Factory: $factory</p>";
    
    $DB = $factory();
    
    // Connect
    $DB->connect($CFG->dbhost, $CFG->dbuser, $CFG->dbpass, $CFG->dbname, $CFG->prefix, $CFG->dboptions);
    
    echo "<p style='color:green;'>✓ Connected to database</p>";
    
} catch (Exception $e) {
    echo "<p style='color:red;'>✗ Failed: " . $e->getMessage() . "</p>";
    echo "<p>" . $e->getTraceAsString() . "</p>";
    exit;
}

echo "<h2>Step 3: Testing query</h2>";

try {
    $count = $DB->count_records('user');
    echo "<p style='color:green;'>✓ Query successful: $count users</p>";
} catch (Exception $e) {
    echo "<p style='color:red;'>✗ Query failed: " . $e->getMessage() . "</p>";
}

?>
