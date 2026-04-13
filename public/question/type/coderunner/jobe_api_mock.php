<?php
/**
 * CodeRunner Mock Jobe API - Class and HTTP Endpoint
 * A standalone Jobe server implementation for local CodeRunner testing
 * File: public/question/type/coderunner/jobe_api_mock.php
 */

// Only set headers and handle HTTP if accessed directly (not included as library)
$is_http_request = (php_sapi_name() !== 'cli' && strpos($_SERVER['REQUEST_URI'], 'jobe_api_mock.php') !== false);

if ($is_http_request) {
    // Set content type for HTTP endpoint
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    
    // Handle CORS preflight
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit(0);
    }
}

// Define the mock class
class jobe_api_mock {
    /**
     * Run tests against the code
     */
    public static function run_tests($testcases, $code, $language = 'python3', $jobeapikey = '') {
        $response = array(
            'status' => 0,
            'cputime' => 0.01,
            'walltime' => 0.02,
            'testresults' => array(),
            'stdout' => '',
            'stderr' => '',
            'returncode' => 0,
            'error' => ''
        );
        
        if (empty($code)) {
            $response['error'] = 'Empty code';
            return $response;
        }
        
        // Mock execution: return test results
        if (is_array($testcases)) {
            foreach ($testcases as $test) {
                $response['testresults'][] = (object)array(
                    'status' => 0,  // PASS
                    'output' => isset($test->output) ? $test->output : 'Test passed',
                    'input' => isset($test->input) ? $test->input : '',
                    'expected' => isset($test->expected_output) ? $test->expected_output : ''
                );
            }
        }
        
        $response['stdout'] = "All tests passed\n";
        return $response;
    }
    
    /**
     * Run code and return output
     */
    public static function run_code($code, $input = '', $language = 'python3', $timeout = 10) {
        $response = array(
            'status' => 0,
            'cputime' => 0.01,
            'walltime' => 0.02,
            'stdout' => 'Code executed successfully',
            'stderr' => '',
            'returncode' => 0,
            'error' => ''
        );
        
        if (empty($code)) {
            $response['error'] = 'Empty code';
            return $response;
        }
        
        return $response;
    }
    
    /**
     * Get list of supported languages
     */
    public static function get_languages() {
        return array(
            'python3' => array('name' => 'Python 3', 'version' => '3.9'),
            'java' => array('name' => 'Java', 'version' => '11'),
            'cpp' => array('name' => 'C++', 'version' => '11'),
            'c' => array('name' => 'C', 'version' => '11')
        );
    }
    
    /**
     * Get Jobe server URL
     */
    public static function get_jobe_server_url() {
        return 'http://localhost';
    }
}

// Handle HTTP requests if accessing directly
if ($is_http_request) {
    $response = array(
        'status' => 0,
        'cputime' => 0.01,
        'walltime' => 0.02,
        'stdout' => '',
        'stderr' => '',
        'returncode' => 0
    );
    
    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $input = file_get_contents('php://input');
    $data = json_decode($input, true) ?: array();
    
    // Handle languages endpoint
    if (strpos($path, 'languages') !== false && $method === 'GET') {
        http_response_code(200);
        echo json_encode(array(
            'languages' => jobe_api_mock::get_languages(),
            'status' => 0
        ));
        exit(0);
    }
    
    // Handle code execution endpoint
    if (($method === 'POST' || $method === 'PUT') && (strpos($path, 'runs') !== false || strpos($path, 'jobe_api_mock.php') !== false)) {
        $code = $data['sourcefilename.py'] ?? $data['code'] ?? $data['source'] ?? '';
        $language = $data['language'] ?? 'python3';
        
        if (!empty($code)) {
            if (preg_match('/SyntaxError|IndentationError|NameError/', $code)) {
                $response['stderr'] = "SyntaxError: invalid syntax\n";
                $response['returncode'] = 1;
            } else {
                $response['stdout'] = "Code executed successfully\n";
                $response['returncode'] = 0;
            }
        }
        
        http_response_code(200);
        echo json_encode($response);
        exit(0);
    }
    
    // Default response
    http_response_code(200);
    echo json_encode($response);
    exit(0);
}

?>


