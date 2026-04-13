<?php
// This file is part of CodeRunner - http://coderunner.org.nz/
//
// Mock Jobe API - Executes code locally using Pyodide in browser
// Instead of calling real Jobe server, this intercepts and tells browser to execute locally

if (!defined('MOODLE_INTERNAL')) {
    define('MOODLE_INTERNAL', 1);
}

// ============================================
// WRAPPER FUNCTIONS FOR TEST COMPATIBILITY
// ============================================
// Define these FIRST so they're available immediately

/**
 * Wrapper function for get_languages()
 * Tests call this procedural function, which delegates to the class method
 */
function qtype_coderunner_get_languages() {
    return jobe_api_mock::get_languages();
}

/**
 * Wrapper function for run_code()
 * Tests call this procedural function, which delegates to the class method
 */
function qtype_coderunner_run_code($code, $input, $language, $timeout = 10) {
    return jobe_api_mock::run_code($code, $input, $language, $timeout);
}

/**
 * Wrapper function for run_tests()
 * Tests call this procedural function, which delegates to the class method
 */
function qtype_coderunner_run_tests($testcases, $code, $language = 'python3', $jobeapikey = '') {
    return jobe_api_mock::run_tests($testcases, $code, $language, $jobeapikey);
}

/**
 * Wrapper function for get_jobe_server_url()
 * Tests call this procedural function, which delegates to the class method
 */
function qtype_coderunner_get_jobe_server_url() {
    return jobe_api_mock::get_jobe_server_url();
}

// ============================================
// MOCK JOBE API CLASS
// ============================================

class jobe_api_mock {
    
    /**
     * Mock version of jobe_api::run_code()
     * Instead of executing on Jobe server, returns signal for browser execution
     */
    public static function run_code($code, $input, $language, $timeout = 10) {
        
        // Return a response that signals browser to execute with Pyodide
        return array(
            'status' => 0,  // Success
            'run' => array(
                'stdout' => '',
                'stderr' => '',
                'returned' => 0,
                'cputime' => 0,
                'output' => 'EXECUTE_LOCALLY_PYODIDE'  // Special marker for browser
            ),
            'meta' => array(
                'code' => $code,
                'input' => $input,
                'language' => $language,
                'execute_method' => 'pyodide_browser'
            )
        );
    }
    
    /**
     * Mock version of jobe_api::run_tests()
     * Used by CodeRunner to run test cases
     */
    public static function run_tests($testcases, $code, $language = 'python3', $jobeapikey = '') {
        
        // Package test data to send to browser for Pyodide execution
        $response = array(
            'status' => 0,
            'testoutcomes' => array(),
            '_Test data for Pyodide execution_' => array(
                'code' => $code,
                'testcases' => $testcases,
                'language' => $language,
                'execute_method' => 'pyodide_browser'
            )
        );
        
        // Create placeholder test outcomes (will be filled by browser)
        foreach ($testcases as $index => $testcase) {
            $response['testoutcomes'][$index] = array(
                'iscorrect' => false,
                'mark' => 0,
                'output' => 'EXECUTING_IN_BROWSER',
                'feedback' => 'Code running in browser with Pyodide...',
                'trialnum' => 1
            );
        }
        
        return $response;
    }
    
    /**
     * Get list of available languages on Jobe
     * Mock version - returns Python only for this implementation
     */
    public static function get_languages() {
        return array('python3', 'python');
    }
    
    /**
     * Check if Jobe API is available
     * Mock version always returns true (we're local)
     */
    public static function get_jobe_server_url() {
        return 'LOCAL_PYODIDE_EXECUTION';
    }
}

// ============================================
// WRAPPER FUNCTIONS FOR TEST COMPATIBILITY
// ============================================

/**
 * Wrapper function for get_languages()
 * Tests call this procedural function, which delegates to the class method
 */
function qtype_coderunner_get_languages() {
    return jobe_api_mock::get_languages();
}

/**
 * Wrapper function for run_code()
 * Tests call this procedural function, which delegates to the class method
 */
function qtype_coderunner_run_code($code, $input, $language, $timeout = 10) {
    return jobe_api_mock::run_code($code, $input, $language, $timeout);
}

/**
 * Wrapper function for run_tests()
 * Tests call this procedural function, which delegates to the class method
 */
function qtype_coderunner_run_tests($testcases, $code, $language = 'python3', $jobeapikey = '') {
    return jobe_api_mock::run_tests($testcases, $code, $language, $jobeapikey);
}

/**
 * Wrapper function for get_jobe_server_url()
 * Tests call this procedural function, which delegates to the class method
 */
function qtype_coderunner_get_jobe_server_url() {
    return jobe_api_mock::get_jobe_server_url();
}
?>
