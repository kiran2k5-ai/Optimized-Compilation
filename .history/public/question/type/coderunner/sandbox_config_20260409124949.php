<?php
// CodeRunner Sandbox Configuration
// File: public/question/type/coderunner/sandbox_config.php
// This file configures CodeRunner to use our mock Jobe API

defined('MOODLE_INTERNAL') || die();

/**
 * Override Jobe configuration to use our local mock API
 * This prevents CodeRunner from trying to reach external Jobe servers
 */

// Get Moodle config
global $CFG;

// Set local sandbox as the only available sandbox
$CFG->qtype_coderunner_sandboxes = array(
    'jobe' => array(
        'description' => 'Local Jobe Mock (Pyodide)',
        'class' => 'qtype_coderunner_jobe_sandbox',
        'version' => '0.0.1',
        // Use our mock API endpoint
        'host' => 'localhost',
        'port' => 80,
        'protocol' => 'http',
        'path' => '/question/type/coderunner/jobe_api_mock.php',
        // Disable Jobe server check
        'check_server_available' => false,
        'use_ace' => true
    )
);

// Default sandbox for Python questions
set_config('default_sandbox', 'jobe', 'qtype_coderunner');

// Disable external Jobe
set_config('jobe_host', 'localhost', 'qtype_coderunner');
set_config('jobe_port', 80, 'qtype_coderunner');
set_config('jobe_apikey', '', 'qtype_coderunner');

// Enable local execution
set_config('enable_browser_execution', 1, 'qtype_coderunner');
set_config('use_local_pyodide', 1, 'qtype_coderunner');
set_config('sandbox', 'jobe', 'qtype_coderunner');

// Set timeout for our mock API
set_config('jobe_timeout', 10, 'qtype_coderunner');

?>
