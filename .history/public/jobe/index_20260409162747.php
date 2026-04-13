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
        // CodeRunner sends the run_spec as top-level properties or in a run_spec field
        $runspec = $data['run_spec'] ?? $data;
        $language = $runspec['language_id'] ?? 'python3';
        $sourcecode = $runspec['sourcecode'] ?? '';
        $input = $runspec['input'] ?? '';
        
        $output = '';
        $stderr = '';
        $outcome = 0;  // 0 = success
        
        // Try to execute Python code if it's python3
        if (($language === 'python3' || $language === 'python') && !empty($sourcecode)) {
            // Create a temporary file to store the Python code
            $tmpdir = sys_get_temp_dir();
            $tmpfile = tempnam($tmpdir, 'coderunner_');
            
            try {
                // Write the code to a temporary file
                file_put_contents($tmpfile, $sourcecode);
                
                // For input, we can't use stdin redirection reliably on Windows
                // Instead, we'll write input to a file and modify the code to use it
                // OR we'll just note that this is a simple mock
                
                if (!empty($input)) {
                    // Write input to a file and pipe it
                    $tmpinputfile = $tmpfile . '.input';
                    file_put_contents($tmpinputfile, $input);
                    
                    // On Windows, use type to pipe the input file
                    $cmd = '(type "' . $tmpinputfile . '") | python "' . $tmpfile . '" 2>&1';
                } else {
                    // No input, just run the code
                    $cmd = 'python "' . $tmpfile . '" 2>&1';
                }
                
                // Execute the code
                $output = shell_exec($cmd);
                
                if ($output === null) {
                    $output = '';
                } else {
                    $output = trim($output);
                }
                
                // Clean up
                @unlink($tmpfile);
                if (isset($tmpinputfile)) {
                    @unlink($tmpinputfile);
                }
            } catch (Exception $e) {
                $stderr = $e->getMessage();
                $outcome = 1;  // error
            }
        } else {
            // For other languages, just return mock success
            $output = "Code executed successfully\n";
        }
        
        // Return proper Jobe API response format
        $jobe_response = [
            'outcome' => intval($outcome),
            'cmpinfo' => '',  // no compilation errors
            'stdout' => trim($output),
            'stderr' => trim($stderr),
            'signal' => 0
        ];
        
        http_response_code(200);
        echo json_encode($jobe_response);
        exit(0);
    }
}

// Default: return error
http_response_code(404);
echo json_encode(['error' => 'Not Found', 'path_info' => $path_info]);
?>
