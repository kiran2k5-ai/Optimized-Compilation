<?php
// Configuration to enable local Pyodide execution instead of Jobe

// File: E:\moodel_xampp\htdocs\moodle\public\question\type\coderunner\enable_pyodide.php

defined('MOODLE_INTERNAL') || die();

/**
 * Enable local Pyodide execution for CodeRunner
 * This configuration makes CodeRunner execute Python code in the browser
 * instead of relying on Jobe server
 */

// Define constants for Pyodide
if (!defined('PYODIDE_VERSION')) {
    define('PYODIDE_VERSION', '0.23.0');
}
if (!defined('PYODIDE_CDN_URL')) {
    define('PYODIDE_CDN_URL', 'https://cdn.jsdelivr.net/pyodide/v0.23.0/full/');
}
if (!defined('PYODIDE_TIMEOUT')) {
    define('PYODIDE_TIMEOUT', 30);
}
if (!defined('PYODIDE_MAX_OUTPUT')) {
    define('PYODIDE_MAX_OUTPUT', 1000000);
}
if (!defined('USE_LOCAL_PYODIDE')) {
    define('USE_LOCAL_PYODIDE', true);
}
if (!defined('ENABLE_BROWSER_EXECUTION')) {
    define('ENABLE_BROWSER_EXECUTION', true);
}

// Update plugin settings to enable Pyodide
$config_settings = array(
    'use_local_pyodide' => true,
    'pyodide_version' => '0.23.0',
    'pyodide_cdn' => 'https://cdn.jsdelivr.net/pyodide/v0.23.0/full/',
    'enable_browser_execution' => true,
    'fallback_to_jobe' => false  // Don't fallback to Jobe if Pyodide fails
);

// Store in Moodle config
foreach ($config_settings as $key => $value) {
    set_config($key, $value, 'qtype_coderunner');
}

// Load sandbox configuration
require_once(__DIR__ . '/sandbox_config.php');

// Load the mock Jobe API class (safe to include as library)
require_once(__DIR__ . '/jobe_api_mock.php');

/**
 * Mock Jobe API Configuration
 * 
 * The jobesandbox.php makes HTTP calls to /jobe/index.php/restapi/{resource}
 * A reverse proxy at /jobe/index.php forwards these to /question/type/coderunner/jobe_api_mock.php
 * 
 * This allows CodeRunner to work with a local mock API instead of a real Jobe server.
 */

return true;
?>
