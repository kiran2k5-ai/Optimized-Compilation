<?php
/**
 * CodeRunner Mock Jobe API - HTTP Endpoint
 * A standalone Jobe server implementation for local CodeRunner testing
 * File: public/question/type/coderunner/jobe_api_mock.php
 */

// Set content type
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] ===  'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// Initialize response
$response = array(
    'status' => 0,
    'cputime' => 0.01,
    'walltime' => 0.02,
    'stdout' => '',
    'stderr' => '',
    'returncode' => 0
);

// Get request data
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$input = file_get_contents('php://input');
$data = json_decode($input, true) ?: array();

// Handle languages endpoint
if (strpos($path, 'languages') !== false && $method === 'GET') {
    http_response_code(200);
    echo json_encode(array(
        'languages' => array(
            'python3' => array('name' => 'Python 3', 'version' => '3.9')
        ),
        'status' => 0
    ));
    exit(0);
}

// Handle code execution endpoint  
if (($method === 'POST' || $method === 'PUT') && (strpos($path, 'runs') !== false || strpos($path, 'jobe_api_mock.php') !== false)) {
    
    // Extract code from request (CodeRunner sends various formats)
    $code = $data['sourcefilename.py'] ?? $data['code'] ?? $data['source'] ?? '';
    $language = $data['language'] ?? 'python3';
    $stdin = $data['stdin'] ?? '';
    
    // Execute code (simulated based on patterns)
    $stdout = '';
    $stderr = '';
    $returncode = 0;
    
    // Simulate execution
    if (!empty($code)) {
        // Check for syntax errors
        if (preg_match('/SyntaxError|IndentationError|NameError/', $code)) {
            $stderr = "SyntaxError: invalid syntax\n";
            $returncode = 1;
        }
        // Any valid code returns success
        else {
            $stdout = "Code executed successfully\n";
            $returncode = 0;
        }
    }
    
    $response['stdout'] = $stdout;
    $response['stderr'] = $stderr;
    $response['returncode'] = $returncode;
    
    http_response_code(200);
    echo json_encode($response);
    exit(0);
}

// Default response for any other request
http_response_code(200);
echo json_encode(array(
    'status' => 0,
    'stdout' => '',
    'stderr' => '',
    'returncode' => 0,
    'cputime' => 0.01,
    'walltime' => 0.01
));
?>

