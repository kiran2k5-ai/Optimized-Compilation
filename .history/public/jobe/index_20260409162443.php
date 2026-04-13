<?php
/**
 * Jobe API Reverse Proxy
 * Forwards requests from /jobe/index.php/restapi/* to our mock API
 * File: /jobe/index.php
 */

// Read the request body
$input = file_get_contents('php://input');

// Set response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// Get the resource from PATH_INFO
$path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '/';

// Extract resource (languages, runs, etc.)
if (preg_match('|/restapi/([a-z]+)|i', $path_info, $matches)) {
    $resource = $matches[1];
    
    // Parse input data
    $data = json_decode($input, true) ?: [];
    
    // Prepare response
    $response = array(
        'status' => 0,
        'cputime' => 0.01,
        'walltime' => 0.02,
        'stdout' => '',
        'stderr' => '',
        'returncode' => 0
    );
    
    // Handle /jobe/index.php/restapi/languages
    if (strtolower($resource) === 'languages' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        http_response_code(200);
        echo json_encode([
            ['python3', 'Python 3', '3.9.0', true],
            ['java', 'Java', '11', true],
            ['cpp', 'C++', '11', true],
            ['c', 'C', '11', true]
        ]);
        exit(0);
    }
    
    // Handle /jobe/index.php/restapi/runs
    if (strtolower($resource) === 'runs' && ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT')) {
        // Extract code or source
        $code = $data['sourcefilename.py'] ?? $data['code'] ?? $data['source'] ?? '';
        
        if (!empty($code)) {
            $response['stdout'] = 'Code executed successfully';
            $response['returncode'] = 0;
        }
        
        http_response_code(200);
        echo json_encode($response);
        exit(0);
    }
}

// Default: return error
http_response_code(404);
echo json_encode(['error' => 'Not Found', 'path_info' => $path_info]);
?>
