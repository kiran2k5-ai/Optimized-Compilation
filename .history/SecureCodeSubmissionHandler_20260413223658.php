<?php
/**
 * SECURE CODE SUBMISSION HANDLER
 * Integrates validation, auditing, and sandboxing
 */

require_once 'CodeValidator.php';
require_once 'CodeAudit.php';

class SecureCodeSubmissionHandler {
    
    /**
     * Process student code submission securely
     *
     * @param array $submission Submission data from POST
     * @return array Response with success/errors
     */
    public static function handle_submission($submission) {
        $response = [
            'success' => false,
            'errors' => [],
            'warnings' => [],
            'data' => []
        ];
        
        // Extract parameters
        $code = $submission['answer'] ?? '';
        $attempt_id = $submission['attempt'] ?? null;
        $userid = $submission['userid'] ?? null;
        $token = $submission['csrf_token'] ?? '';
        
        // Step 1: CSRF Protection
        if (!self::verify_csrf_token($token)) {
            $response['errors'][] = 'Security token invalid - submission rejected';
            return $response;
        }
        
        // Step 2: CODE VALIDATION
        $validation = CodeValidator::validate($code);
        
        if (!$validation['is_valid']) {
            $response['errors'] = $validation['errors'];
            return $response;
        }
        
        if (!empty($validation['warnings'])) {
            $response['warnings'] = $validation['warnings'];
            // Still proceed if only warnings
        }
        
        // Step 3: AUDIT LOGGING
        $audit_result = CodeAudit::log_submission($attempt_id, $userid, $code);
        if (!$audit_result['success']) {
            $response['errors'][] = 'Failed to log submission';
            return $response;
        }
        
        // Step 4: CHECK FOR SUSPICIOUS ACTIVITY
        $suspicious = CodeAudit::detect_suspicious_activity($attempt_id);
        if ($suspicious['suspicious']) {
            log_security_event('SUSPICIOUS_CODE_ACTIVITY', [
                'attempt_id' => $attempt_id,
                'userid' => $userid,
                'issues' => $suspicious['issues']
            ]);
            // Don't block, but flag for review
            $response['warnings'][] = 'Unusual submission pattern detected - flagged for review';
        }
        
        // Step 5: RATE LIMITING
        if (!self::check_rate_limit($userid)) {
            $response['errors'][] = 'Too many submissions - please wait before submitting again';
            return $response;
        }
        
        // Step 6: IP RATE LIMITING
        if (!self::check_ip_rate_limit($_SERVER['REMOTE_ADDR'] ?? '')) {
            $response['errors'][] = 'Too many requests from your IP';
            return $response;
        }
        
        // Step 7: CODE EXECUTION (in sandbox)
        $execution_result = self::execute_code_safely($code, 
                                                       $submission['stdin'] ?? '',
                                                       $submission['language'] ?? 'python3');
        
        if (!$execution_result['success']) {
            $response['errors'] = $execution_result['errors'];
            return $response;
        }
        
        // All checks passed!
        $response['success'] = true;
        $response['data'] = [
            'output' => $execution_result['output'],
            'stderr' => $execution_result['stderr'],
            'exit_code' => $execution_result['exit_code'],
            'execution_time' => $execution_result['duration'],
            'code_hash' => $audit_result['code_hash']
        ];
        
        return $response;
    }
    
    /**
     * Execute code safely with resource limits
     */
    private static function execute_code_safely($code, $stdin = '', $language = 'python3') {
        // Note: In production, use Docker!
        // This is just local execution with timeout
        
        $tmpdir = sys_get_temp_dir();
        $uniqid = uniqid();
        $codefile = $tmpdir . DIRECTORY_SEPARATOR . "code_{$uniqid}.py";
        
        // Write code to temp file
        if (file_put_contents($codefile, $code) === false) {
            return [
                'success' => false,
                'errors' => ['Cannot write code to file'],
                'output' => '',
                'stderr' => '',
                'duration' => 0
            ];
        }
        
        // Execute with timeout
        $start_time = microtime(true);
        $max_duration = 5; // seconds
        
        // Windows: use timeout command
        if (strpos(PHP_OS, 'WIN') === 0) {
            $cmd = "timeout {$max_duration} python \"{$codefile}\"";
        } else {
            $cmd = "timeout {$max_duration} python \"{$codefile}\"";
        }
        
        $descriptorspec = [
            0 => ['pipe', 'r'],  // stdin
            1 => ['pipe', 'w'],  // stdout
            2 => ['pipe', 'w']   // stderr
        ];
        
        $process = proc_open($cmd, $descriptorspec, $pipes);
        
        if (!is_resource($process)) {
            @unlink($codefile);
            return [
                'success' => false,
                'errors' => ['Cannot execute code'],
                'output' => '',
                'stderr' => '',
                'duration' => 0
            ];
        }
        
        // Write stdin
        if (!empty($stdin)) {
            fwrite($pipes[0], $stdin);
        }
        fclose($pipes[0]);
        
        // Read output
        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        
        $exit_code = proc_close($process);
        $duration = microtime(true) - $start_time;
        
        // Clean up
        @unlink($codefile);
        
        // Check for timeout (exit code 124 on Unix, varies on Windows)
        if ($exit_code === 124 || $exit_code > 127) {
            return [
                'success' => false,
                'errors' => ["Execution timeout (exceeded {$max_duration}s limit)"],
                'output' => $output,
                'stderr' => 'TIMEOUT',
                'exit_code' => $exit_code,
                'duration' => $duration
            ];
        }
        
        return [
            'success' => empty($stderr) || $exit_code === 0,
            'errors' => !empty($stderr) ? [trim($stderr)] : [],
            'output' => $output,
            'stderr' => $stderr,
            'exit_code' => $exit_code,
            'duration' => $duration
        ];
    }
    
    /**
     * Check rate limit: max 10 submissions per minute per user
     */
    private static function check_rate_limit($userid) {
        $cache_key = 'submission_rate_' . $userid;
        $rate_limit_file = sys_get_temp_dir() . '/' . md5($cache_key);
        
        $current_time = time();
        $window_start = $current_time - 60;
        
        // Load previous submissions
        $submissions = [];
        if (file_exists($rate_limit_file)) {
            $submissions = json_decode(file_get_contents($rate_limit_file), true) ?: [];
        }
        
        // Remove old entries
        $submissions = array_filter($submissions, function($t) use ($window_start) {
            return $t > $window_start;
        });
        
        // Check limit
        if (count($submissions) >= 10) {
            return false; // Exceeded
        }
        
        // Add current
        $submissions[] = $current_time;
        file_put_contents($rate_limit_file, json_encode($submissions));
        
        return true; // OK
    }
    
    /**
     * Check rate limit by IP: max 50 requests per minute per IP
     */
    private static function check_ip_rate_limit($ip) {
        $cache_key = 'ip_rate_' . $ip;
        $rate_limit_file = sys_get_temp_dir() . '/' . md5($cache_key);
        
        $current_time = time();
        $window_start = $current_time - 60;
        
        $requests = [];
        if (file_exists($rate_limit_file)) {
            $requests = json_decode(file_get_contents($rate_limit_file), true) ?: [];
        }
        
        $requests = array_filter($requests, function($t) use ($window_start) {
            return $t > $window_start;
        });
        
        if (count($requests) >= 50) {
            return false;
        }
        
        $requests[] = $current_time;
        file_put_contents($rate_limit_file, json_encode($requests));
        
        return true;
    }
    
    /**
     * Verify CSRF token
     */
    private static function verify_csrf_token($token) {
        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}

// ===== USAGE IN MOODLE =====
/*
// In mod/quiz/attempt.php or submission handler:

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// When form submitted:
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submission = [
        'answer' => $_POST['answer'] ?? '',
        'attempt' => $_POST['attempt'] ?? null,
        'userid' => $USER->id,
        'csrf_token' => $_POST['csrf_token'] ?? '',
        'stdin' => $_POST['stdin'] ?? '',
        'language' => 'python3'
    ];
    
    $result = SecureCodeSubmissionHandler::handle_submission($submission);
    
    if (!$result['success']) {
        // Show errors
        echo json_encode(['success' => false, 'errors' => $result['errors']]);
    } else {
        // Show results
        echo json_encode([
            'success' => true,
            'output' => $result['data']['output'],
            'warnings' => $result['warnings']
        ]);
    }
}
*/

// ===== MONITORING & ALERTS =====
function log_security_event($event_type, $data) {
    $log_file = 'E:\moodel_xampp\moodledata\security_events.log';
    $log_dir = dirname($log_file);
    
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0700, true);
    }
    
    $entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'event_type' => $event_type,
        'data' => $data,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    file_put_contents($log_file, json_encode($entry) . "\n", FILE_APPEND);
    
    // Alert admin on critical events
    if (in_array($event_type, ['CODE_TAMPERING_DETECTED', 'SUSPICIOUS_CODE_ACTIVITY'])) {
        // Send alert - could be email, Slack, etc.
        // email_admin_alert("Security Event: $event_type", json_encode($data));
    }
}
?>
