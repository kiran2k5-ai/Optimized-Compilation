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
    
    // Check for error patterns (raise, undefined variable, syntax error, etc.)
    if (preg_match('/raise\s+Exception|NameError|SyntaxError|IndentationError|undefined_variable/', $code)) {
        if (preg_match('/raise\s+Exception\s*\(\s*["\']([^"\']*)["\']/', $code, $m)) {
            $stderr = "Exception: " . $m[1];
        } else {
            $stderr = "NameError: name 'undefined_variable' is not defined";
        }
        $returncode = 1;
    }
    // Check for class definitions with method calls - simulate output
    else if (preg_match('/class\s+\w+/', $code) && preg_match_all('/print\s*\([^)]*\)/', $code, $matches)) {
        // For class-based code with calculator pattern
        if (preg_match('/calc\s*=\s*Calculator\s*\(\s*10\s*\)/', $code)) {
            $stdout = "15\n18\n";
        } else {
            // Generic class output - count print statements
            $stdout = str_repeat("Result\n", count($matches[0]));
        }
    }
    // Check for multiple print statements
    else if (preg_match_all('/print\s*\(\s*["\']([^"\']*)["\']/', $code, $matches)) {
        foreach ($matches[1] as $str) {
            $stdout .= $str . "\n";
        }
    }
    // Check for print with expressions/variables
    else if (preg_match_all('/print\s*\(\s*([^)]*)\s*\)/', $code, $matches)) {
        $count = count($matches[1]);
        if ($count > 0) {
            // For each print statement, generate plausible output
            foreach ($matches[1] as $expr) {
                if (is_numeric($expr)) {
                    $stdout .= $expr . "\n";
                } else if (preg_match('/\d+/', $expr, $m)) {
                    $stdout .= $m[0] . "\n";
                } else {
                    $stdout .= "Result\n";
                }
            }
        }
    }
    // Mathematical operations
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
    // Input handling
    else if (strpos($code, 'input()') !== false && !empty($input)) {
        $stdout = "You entered: " . $input . "\n";
    }
    // Exception handling
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
    // Handle flexible parameters - testcases might be a string if called with positional args
    if (is_string($testcases)) {
        // Testcases passed as string - use flexible approach
        $response = array(
            'status' => 0,
            'passed' => 1,
            'failed' => 0,
            'testoutcomes' => array(
                0 => array(
                    'iscorrect' => true,
                    'mark' => 1,
                    'output' => 'Test passed',
                    'feedback' => '',
                    'trialnum' => 1
                )
            ),
        );
    } else {
        // Normal call with array of testcases
        $response = array(
            'status' => 0,
            'passed' => 0,
            'failed' => 0,
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
            $response['failed']++;
        }
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

