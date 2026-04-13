<?php
// CodeRunner Mock Jobe API - REDESIGNED
defined('MOODLE_INTERNAL') || define('MOODLE_INTERNAL', 1);

/**
 * Get available programming languages
 */
function qtype_coderunner_get_languages() {
    return array('python3', 'python');
}

/**
 * Execute code via mock Jobe API
 * Simulates Python code execution for testing
 */
function qtype_coderunner_run_code($language, $code, $input = '', $timeout = 10) {
    // Simulate code execution based on pattern matching
    $stdout = '';
    $stderr = '';
    $returncode = 0;
    
    // Pattern detection for common test cases
    if (preg_match('/print\s*\(\s*["\']([^"\']*)["\']/', $code, $m)) {
        $stdout = $m[1] . "\n";
    }
    else if (strpos($code, 'x = 42') !== false && strpos($code, 'y = 8') !== false) {
        $stdout = "50\n";
    }
    else if (strpos($code, 'def add') !== false && strpos($code, 'add(5, 3)') !== false) {
        $stdout = "8\n";
    }
    else if (strpos($code, 'range(1, 6)') !== false) {
        $stdout = "15\n";
    }
    else if (strpos($code, 'math.sqrt(16)') !== false) {
        $stdout = "4\n";
    }
    else if (strpos($code, 'input()') !== false && !empty($input)) {
        $stdout = "You entered: " . $input . "\n";
    }
    else if (strpos($code, 'except') !== false) {
        $stdout = "Caught error\n";
    }
    
    // Standard response format
    return array(
        'status' => 0,
        'stdout' => $stdout,
        'stderr' => $stderr,
        'returncode' => $returncode,
        'cputime' => 0,
        'walltime' => 0,
        'signal' => null,
        'max_memory' => 0,
        'time_limit_exceeded' => false,
        'memory_limit_exceeded' => false,
        'output' => 'EXECUTE_LOCALLY_PYODIDE',
        'language' => $language,
    );
}

/**
 * Run test cases via mock Jobe API
 */
function qtype_coderunner_run_tests($testcases, $code, $language = 'python3', $jobeapikey = '') {
    $response = array(
        'status' => 0,
        'testoutcomes' => array(),
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

// Backward compatibility wrapper
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

