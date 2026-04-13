<?php
// CodeRunner Mock Jobe API - Direct function implementation
defined('MOODLE_INTERNAL') || define('MOODLE_INTERNAL', 1);

/**
 * Get available programming languages
 */
function qtype_coderunner_get_languages() {
    return array('python3', 'python');
}

/**
 * Execute code via mock Jobe API
 */
function qtype_coderunner_run_code($language, $code, $input = '', $timeout = 10) {
    return array(
        'status' => 0,
        'stdout' => '',
        'stderr' => '',
        'returncode' => 0,
        'cputime' => 0,
        'walltime' => 0,
        'signal' => null,
        'max_memory' => 0,
        'time_limit_exceeded' => false,
        'memory_limit_exceeded' => false,
        'output' => 'EXECUTE_LOCALLY_PYODIDE',
        'language' => $language
    );
}

/**
 * Run test cases via mock Jobe API
 */
function qtype_coderunner_run_tests($testcases, $code, $language = 'python3', $jobeapikey = '') {
    $response = array(
        'status' => 0,
        'testoutcomes' => array()
    );
    
    foreach ($testcases as $index => $testcase) {
        $response['testoutcomes'][$index] = array(
            'iscorrect' => false,
            'mark' => 0,
            'output' => 'EXECUTING',
            'feedback' => 'Running in browser',
            'trialnum' => 1
        );
    }
    
    return $response;
}

/**
 * Get Jobe server URL (mock returns local)
 */
function qtype_coderunner_get_jobe_server_url() {
    return 'LOCAL_PYODIDE';
}

// Legacy class wrapper for backward compatibility
class jobe_api_mock {
    public static function run_code($code, $input, $language, $timeout = 10) {
        return qtype_coderunner_run_code($language, $code, $input, $timeout);
    }
    public static function run_tests($testcases, $code, $language = 'python3', $jobeapikey = '') {
        return qtype_coderunner_run_tests($testcases, $code, $language, $jobeapikey);
    }
    public static function get_languages() {
        return qtype_coderunner_get_languages();
    }
    public static function get_jobe_server_url() {
        return qtype_coderunner_get_jobe_server_url();
    }
}
?>
