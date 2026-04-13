<?php
/**
 * HARDENED JOBE API MOCK - Secure Code Execution
 * File: /jobe/index_secure.php
 * Features: API Key Auth, IP Whitelist, Rate Limiting, Logging, Timeouts
 */

// ===== SECURITY CONFIGURATION =====
const JOBE_API_KEY = 'moodle_local_api_key_12345'; // Change this!
const MAX_REQUESTS_PER_MINUTE = 10; // Rate limit
const EXECUTION_TIMEOUT_SECS = 5; // Max execution time
const MAX_OUTPUT_CHARS = 50000; // Max output size
const LOG_FILE = 'E:\moodel_xampp\moodledata\jobe_execution.log';
const ALLOWED_IPS = ['127.0.0.1', 'localhost']; // Restrict to localhost only

// ===== SECURITY FUNCTIONS =====

function log_execution($event, $data = []) {
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $log_entry = "[$timestamp] IP=$ip | Event=$event | " . json_encode($data) . "\n";
    
    // Append to log file (create if doesn't exist)
    $log_dir = dirname(LOG_FILE);
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0700, true);
    }
    file_put_contents(LOG_FILE, $log_entry, FILE_APPEND);
}

function check_rate_limit($identifier) {
    $cache_key = 'jobe_ratelimit_' . md5($identifier);
    $cache_file = sys_get_temp_dir() . '/' . $cache_key;
    
    $current_time = time();
    $window_start = $current_time - 60; // 1 minute window
    
    // Read existing counts
    $counts = [];
    if (file_exists($cache_file)) {
        $counts = json_decode(file_get_contents($cache_file), true) ?: [];
    }
    
    // Remove old entries outside the window
    $counts = array_filter($counts, function($timestamp) use ($window_start) {
        return $timestamp > $window_start;
    });
    
    // Check if exceeded limit
    if (count($counts) >= MAX_REQUESTS_PER_MINUTE) {
        return false; // Rate limit exceeded
    }
    
    // Add current request
    $counts[] = $current_time;
    file_put_contents($cache_file, json_encode($counts));
    
    return true; // OK to proceed
}

function verify_api_key($key) {
    return hash_equals($key, JOBE_API_KEY);
}

function validate_ip() {
    $client_ip = $_SERVER['REMOTE_ADDR'] ?? '';
    return in_array($client_ip, ALLOWED_IPS);
}

function sanitize_output($str) {
    // Limit output size
    if (strlen($str) > MAX_OUTPUT_CHARS) {
        $str = substr($str, 0, MAX_OUTPUT_CHARS) . "[OUTPUT TRUNCATED]";
    }
    return $str;
}

// ===== MAIN SECURITY CHECKS =====

// Set secure headers
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Restrict CORS to localhost only (NOT *)
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, ['http://localhost', 'http://localhost:80'])) {
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
    header('Access-Control-Allow-Origin: http://localhost');
}
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// Check IP whitelist
if (!validate_ip()) {
    log_execution('BLOCKED_IP', ['ip' => $_SERVER['REMOTE_ADDR']]);
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit(0);
}

// Check API Key authentication
$auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$api_key = '';

if (preg_match('/Bearer\s+(\S+)/', $auth_header, $matches)) {
    $api_key = $matches[1];
} else {
    $api_key = $_GET['api_key'] ?? $_POST['api_key'] ?? '';
}

if (empty($api_key) || !verify_api_key($api_key)) {
    log_execution('UNAUTHORIZED_REQUEST', ['has_key' => !empty($api_key)]);
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized - valid API key required']);
    exit(0);
}

// Check rate limit
$rate_limit_key = $_SERVER['REMOTE_ADDR'];
if (!check_rate_limit($rate_limit_key)) {
    log_execution('RATE_LIMIT_EXCEEDED', ['ip' => $rate_limit_key]);
    http_response_code(429);
    echo json_encode(['error' => 'Too many requests - rate limit exceeded']);
    exit(0);
}

// ===== MAIN API HANDLER =====

$path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '/';

if (preg_match('|/restapi/([a-z]+)|i', $path_info, $matches)) {
    $resource = $matches[1];
    
    $input = file_get_contents('php://input');
    $data = json_decode($input, true) ?: [];
    
    // Languages endpoint
    if (strtolower($resource) === 'languages' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        http_response_code(200);
        echo json_encode([
            ['python3', 'Python 3', '3.11.0', true]
        ]);
        exit(0);
    }
    
    // Execute code endpoint
    if (strtolower($resource) === 'runs' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $runspec = $data['run_spec'] ?? $data;
        $language = $runspec['language_id'] ?? 'python3';
        $sourcecode = $runspec['sourcecode'] ?? '';
        $input_data = $runspec['stdin'] ?? $runspec['input'] ?? '';
        
        // ===== VALIDATION =====
        
        // Validate language
        if (!in_array($language, ['python3', 'python', 'python2'])) {
            log_execution('INVALID_LANGUAGE', ['lang' => $language]);
            http_response_code(400);
            echo json_encode(['error' => 'Invalid language']);
            exit(0);
        }
        
        // Validate source code length
        if (strlen($sourcecode) > 100000) {
            log_execution('CODE_TOO_LARGE', ['size' => strlen($sourcecode)]);
            http_response_code(400);
            echo json_encode(['error' => 'Source code too large (max 100KB)']);
            exit(0);
        }
        
        // Validate input length
        if (strlen($input_data) > 100000) {
            log_execution('INPUT_TOO_LARGE', ['size' => strlen($input_data)]);
            http_response_code(400);
            echo json_encode(['error' => 'Input too large (max 100KB)']);
            exit(0);
        }
        
        // Basic sanity check for obvious attacks
        $dangerous_keywords = ['__import__', 'eval', 'exec', 'compile', 'globals', 'locals'];
        foreach ($dangerous_keywords as $keyword) {
            if (stripos($sourcecode, $keyword) !== false) {
                log_execution('DANGEROUS_CODE_DETECTED', ['keyword' => $keyword]);
                // Note: In real system, you might allow this but log it
                // For now we're logging as warning but not blocking
            }
        }
        
        $output = '';
        $stderr = '';
        $outcome = 15; // RESULT_SUCCESS
        
        if (in_array($language, ['python3', 'python', 'python2']) && !empty($sourcecode)) {
            try {
                $tmpdir = sys_get_temp_dir();
                $uniqid = uniqid();
                $codefile = $tmpdir . DIRECTORY_SEPARATOR . "code_{$uniqid}.py";
                
                // Write source code
                if (file_put_contents($codefile, $sourcecode) === false) {
                    throw new Exception("Cannot write code file");
                }
                
                // Execute with timeout
                $cmd = "timeout " . EXECUTION_TIMEOUT_SECS . " python \"$codefile\"";
                
                $descriptorspec = array(
                    0 => array("pipe", "r"),   // stdin
                    1 => array("pipe", "w"),   // stdout
                    2 => array("pipe", "w")    // stderr
                );
                
                $process = proc_open($cmd, $descriptorspec, $pipes);
                
                if (!is_resource($process)) {
                    throw new Exception("Cannot open process");
                }
                
                // Write input
                if (!empty($input_data)) {
                    fwrite($pipes[0], $input_data);
                }
                fclose($pipes[0]);
                
                // Read output with size limit
                $output = fread($pipes[1], MAX_OUTPUT_CHARS);
                fclose($pipes[1]);
                
                $stderr = stream_get_contents($pipes[2]);
                fclose($pipes[2]);
                
                $exit_code = proc_close($process);
                
                // Log execution
                log_execution('CODE_EXECUTED', [
                    'language' => $language,
                    'code_size' => strlen($sourcecode),
                    'exit_code' => $exit_code,
                    'had_error' => !empty($stderr)
                ]);
                
                if (!empty($stderr)) {
                    $outcome = 12; // RESULT_RUNTIME_ERROR
                }
                
                if ($exit_code === 124) {
                    $stderr = "Execution timeout (exceeded " . EXECUTION_TIMEOUT_SECS . "s limit)";
                    $outcome = 12;
                }
                
                // Clean up
                @unlink($codefile);
                
            } catch (Exception $e) {
                $stderr = $e->getMessage();
                $outcome = 12;
                log_execution('EXECUTION_ERROR', ['error' => $stderr]);
            }
        }
        
        // Sanitize output
        $output = sanitize_output($output);
        $stderr = sanitize_output($stderr);
        
        $jobe_response = [
            'outcome' => intval($outcome),
            'cmpinfo' => '',
            'stdout' => $output,
            'stderr' => $stderr,
            'signal' => 0
        ];
        
        http_response_code(200);
        echo json_encode($jobe_response);
        exit(0);
    }
}

// 404 error
http_response_code(404);
echo json_encode(['error' => 'Not Found']);
?>
