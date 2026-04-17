<?php  // Moodle configuration file

unset($CFG);
global $CFG;
$CFG = new stdClass();

// ===== ENVIRONMENT DETECTION =====
// Detect if running in Docker or locally
$is_docker = is_dir('/var/moodledata');

// ===== DATABASE CONFIGURATION =====
$CFG->dbtype    = 'mariadb';
$CFG->dblibrary = 'native';

if ($is_docker) {
    // Docker environment
    $CFG->dbhost    = 'mysql';
    $CFG->dbname    = 'moodle';
    $CFG->dbuser    = 'moodle';
    $CFG->dbpass    = 'moodlepass';
} else {
    // Local Windows environment
    $CFG->dbhost    = 'localhost';
    $CFG->dbname    = 'moodle';
    $CFG->dbuser    = 'root';
    $CFG->dbpass    = '';
}

$CFG->prefix    = 'mdl_';
$CFG->dboptions = array (
  'dbpersist' => 0,
  'dbport' => '',
  'dbsocket' => '',
  'dbcollation' => 'utf8mb4_general_ci',
);

// ===== SITE URL & PATHS =====
if ($is_docker) {
    // Docker deployment - detect from request
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $CFG->wwwroot   = $protocol . '://' . $host;
} else {
    // Local development
    $CFG->wwwroot   = 'http://localhost';
}

// moodledata directory
if ($is_docker) {
    $CFG->dataroot  = '/var/moodledata';
} else {
    $CFG->dataroot  = 'E:\\moodel_xampp\\moodledata';
}

// ===== ADMIN & SECURITY =====
$CFG->admin     = 'admin';
$CFG->directorypermissions = 0777;

// ===== TRUSTED HOSTS & PROXIES =====
if ($is_docker) {
    // Allow Docker deployment hosts
    $CFG->trusteddomains = array();
    $CFG->trusteddomains[0] = 'localhost';
    $CFG->trusteddomains[1] = 'localhost:80';
    $CFG->trusteddomains[2] = 'localhost:443';
    $CFG->trusteddomains[3] = '127.0.0.1';
} else {
    // Local development
    $CFG->trusteddomains = array();
    $CFG->trusteddomains[0] = 'localhost';
    $CFG->trusteddomains[1] = '127.0.0.1';
    $CFG->trusteddomains[2] = 'localhost:80';
}

// ===== SESSION CONFIGURATION =====
$CFG->sessiontimeout = 7200; // 2 hours
$CFG->sessioncookiedomain = '';
$CFG->sessioncookieinsecure = $is_docker ? false : true; // Allow http on local
$CFG->sessioncookiesamesite = 'Lax';

// ===== SECURITY SETTINGS =====
$CFG->curlsecurityblockedhosts = '192.168.0.0/16
10.0.0.0/8
172.16.0.0/12
0.0.0.0
169.254.169.254
0000::1';

// ===== DEBUG SETTINGS =====
if ($is_docker) {
    // Production debug settings
    @error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
    @ini_set('display_errors', '0');
    $CFG->debug = DEBUG_MINIMAL;
    $CFG->debugdisplay = false;
    $CFG->logsdir = '/var/log/moodle';
} else {
    // Development debug settings
    @error_reporting(E_ALL | E_STRICT);
    @ini_set('display_errors', '1');
    $CFG->debug = DEBUG_DEVELOPER;
    $CFG->debugdisplay = true;
}

// ===== LOGGING =====
$CFG->sysadmins = '2';

// ===== CODERUNNER SETTINGS =====
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

// ===== JOBE API CONFIGURATION =====
if ($is_docker) {
    // Docker - Jobe runs on separate container if available
    $CFG->coderunner_jobe_server = 'http://localhost:4000';
} else {
    // Local development
    $CFG->coderunner_jobe_server = 'http://localhost:4000';
}

// ===== CORE MOODLE SETUP =====
require_once(__DIR__ . '/lib/setup.php');

