<?php  // Moodle configuration file

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

// Allow localhost for Jobe API calls (local development)
$CFG->curlsecurityblockedhosts = '192.168.0.0/16
10.0.0.0/8
172.16.0.0/12
0.0.0.0
169.254.169.254
0000::1';

$CFG->directorypermissions = 0777;

require_once(__DIR__ . '/lib/setup.php');


// ===== CODERUNNER SECURITY SETTINGS =====
$CFG->coderunner_security_enabled = true;
$CFG->coderunner_enable_code_validation = true;
$CFG->coderunner_enable_audit_logging = true;
$CFG->coderunner_max_code_size = 100000;
$CFG->coderunner_max_input_size = 100000;
$CFG->coderunner_max_output_size = 50000;
$CFG->coderunner_execution_timeout = 5;
$CFG->coderunner_max_submissions_per_minute = 10;
$CFG->coderunner_max_ip_requests_per_minute = 50;
$CFG->coderunner_detect_suspicious_patterns = true;
$CFG->coderunner_log_suspicious_activity = true;

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!
