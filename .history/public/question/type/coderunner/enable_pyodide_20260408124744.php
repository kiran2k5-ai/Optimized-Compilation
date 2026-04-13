<?php
// Configuration to enable local Pyodide execution instead of Jobe

// File: E:\moodel_xampp\htdocs\moodle\public\question\type\coderunner\enable_pyodide.php

defined('MOODLE_INTERNAL') || die();

/**
 * Enable local Pyodide execution for CodeRunner
 * This configuration makes CodeRunner execute Python code in the browser
 * instead of relying on Jobe server
 */

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

// Ensure jobe_api_mock is loaded
if (!function_exists('jobe_api_mock')) {
    require_once(__DIR__ . '/jobe_api_mock.php');
}

/**
 * Wrapper to automatically use Mock Jobe API instead of real Jobe
 * This allows CodeRunner to work without Jobe server
 */
class jobe_api {
    // Forward all static calls to our mock implementation
    public static function __callStatic($method, $args) {
        $mock_class = 'jobe_api_mock';
        if (method_exists($mock_class, $method)) {
            return call_user_func_array([$mock_class, $method], $args);
        }
        throw new Exception("Method jobe_api_mock::$method not found");
    }
    
    // Proxy specific methods to mock
    public static function run_tests($testcases, $code, $language = 'python3', $jobeapikey = '') {
        return jobe_api_mock::run_tests($testcases, $code, $language, $jobeapikey);
    }
    
    public static function run_code($code, $input, $language, $timeout = 10) {
        return jobe_api_mock::run_code($code, $input, $language, $timeout);
    }
    
    public static function get_languages() {
        return jobe_api_mock::get_languages();
    }
    
    public static function get_jobe_server_url() {
        return jobe_api_mock::get_jobe_server_url();
    }
}

return true;
?>
