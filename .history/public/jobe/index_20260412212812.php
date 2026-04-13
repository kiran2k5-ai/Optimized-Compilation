<?php
/**
 * Jobe API Mock - Executes Python code locally
 * File: /jobe/index.php
 */

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
    
    // Read the request body
    $input = file_get_contents('php://input');
    $data = json_decode($input, true) ?: [];
    
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
        // Extract run specification
        $runspec = $data['run_spec'] ?? $data;
        $language = $runspec['language_id'] ?? 'python3';
        $sourcecode = $runspec['sourcecode'] ?? '';
        $input_data = $runspec['stdin'] ?? $runspec['input'] ?? '';
        
        $output = '';
        $stderr = '';
        $cmpinfo = '';
        $outcome = 15;  // RESULT_SUCCESS (15) by default
        
        // Execute Python code
        if (($language === 'python3' || $language === 'python') && !empty($sourcecode)) {
            try {
                // Get temp directory
                $tmpdir = sys_get_temp_dir();
                if (!is_dir($tmpdir)) {
                    $tmpdir = '.';
                }
                
                // Create unique file names
                $uniqid = uniqid();
                $codefile = $tmpdir . DIRECTORY_SEPARATOR . "code_{$uniqid}.py";
                $inputfile = $tmpdir . DIRECTORY_SEPARATOR . "input_{$uniqid}.txt";
                $outputfile = $tmpdir . DIRECTORY_SEPARATOR . "output_{$uniqid}.txt";
                $errorfile = $tmpdir . DIRECTORY_SEPARATOR . "error_{$uniqid}.txt";
                
                // Write source code to file
                if (file_put_contents($codefile, $sourcecode) === false) {
                    throw new Exception("Cannot write code file");
                }
                
                // Use proc_open to properly handle stdin/stdout/stderr
                $cmd = "python \"$codefile\"";
                $descriptorspec = array(
                    0 => array("pipe", "r"),   // stdin
                    1 => array("pipe", "w"),   // stdout
                    2 => array("pipe", "w")    // stderr
                );
                
                $process = proc_open($cmd, $descriptorspec, $pipes);
                
                if (!is_resource($process)) {
                    throw new Exception("Cannot open process");
                }
                
                // Write input to stdin if provided
                if (!empty($input_data)) {
                    fwrite($pipes[0], $input_data);
                }
                fclose($pipes[0]);
                
                // Read output
                $output = stream_get_contents($pipes[1]);
                fclose($pipes[1]);
                
                // Read error output
                $stderr = stream_get_contents($pipes[2]);
                fclose($pipes[2]);
                
                // Get exit code
                $exit_code = proc_close($process);
                
                if (!empty($stderr)) {
                    $outcome = 12;  // RESULT_RUNTIME_ERROR
                }
                
                // Check exit code
                if ($exit_code !== 0 && empty($stderr)) {
                    $stderr = "Exit code: $exit_code";
                    $outcome = 12;
                }
                
                // Clean up temp files
                @unlink($codefile);
                @unlink($inputfile);
                @unlink($outputfile);
                @unlink($errorfile);
                
            } catch (Exception $e) {
                $stderr = $e->getMessage();
                $outcome = 12;  // RESULT_RUNTIME_ERROR
            }
        } else {
            // For non-Python, return mock success
            $output = "Executed successfully\n";
        }
        
        // Return proper Jobe API response format
        $jobe_response = [
            'outcome' => intval($outcome),
            'cmpinfo' => $cmpinfo,
            'stdout' => $output,
            'stderr' => $stderr,
            'signal' => 0
        ];
        
        http_response_code(200);
        echo json_encode($jobe_response);
        exit(0);
    }
}

// Default: return error
http_response_code(404);
echo json_encode(['error' => 'Not Found', 'path' => $path_info]);
?>
