<?php
/**
 * CODE SUBMISSION AUDIT & VERSIONING
 * Tracks all code submissions, detects tampering, prevents cheating
 */

class CodeAudit {
    
    private $log_file = 'E:\moodel_xampp\moodledata\code_audit.log';
    
    /**
     * Log code submission with integrity check
     */
    public static function log_submission($attempt_id, $userid, $code, $timestamp = null) {
        if ($timestamp === null) {
            $timestamp = time();
        }
        
        // Create cryptographic hash of code
        $code_hash = hash('sha256', $code);
        $code_size = strlen($code);
        $code_lines = substr_count($code, "\n") + 1;
        
        // Get client information
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        // Create audit entry
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s', $timestamp),
            'attempt_id' => $attempt_id,
            'user_id' => $userid,
            'code_hash' => $code_hash,
            'code_size' => $code_size,
            'code_lines' => $code_lines,
            'ip_address' => $ip_address,
            'user_agent' => substr($user_agent, 0, 100),
            'action' => 'SUBMISSION'
        ];
        
        // Log to file
        self::write_log($log_entry);
        
        // Store in database for quick access
        self::store_in_database($attempt_id, $userid, $code, $code_hash, $timestamp);
        
        return [
            'success' => true,
            'code_hash' => $code_hash,
            'timestamp' => $timestamp
        ];
    }
    
    /**
     * Verify code hasn't been tampered with since submission
     */
    public static function verify_integrity($attempt_id, $code) {
        $mysqli = new mysqli('localhost', 'root', '', 'moodle');
        
        // Get original code hash
        $result = $mysqli->query(
            "SELECT code_hash FROM mdl_code_audit 
             WHERE attempt_id = $attempt_id 
             ORDER BY timestamp DESC 
             LIMIT 1"
        );
        
        if (!$result || $result->num_rows == 0) {
            $mysqli->close();
            return ['valid' => false, 'reason' => 'No audit record found'];
        }
        
        $row = $result->fetch_assoc();
        $stored_hash = $row['code_hash'];
        $current_hash = hash('sha256', $code);
        
        $mysqli->close();
        
        if ($stored_hash === $current_hash) {
            return ['valid' => true, 'hash' => $current_hash];
        } else {
            return [
                'valid' => false,
                'reason' => 'Code has been modified since submission',
                'original_hash' => $stored_hash,
                'current_hash' => $current_hash
            ];
        }
    }
    
    /**
     * Get submission history (all versions)
     */
    public static function get_submission_history($attempt_id) {
        $mysqli = new mysqli('localhost', 'root', '', 'moodle');
        
        $result = $mysqli->query(
            "SELECT * FROM mdl_code_audit 
             WHERE attempt_id = $attempt_id 
             ORDER BY timestamp ASC"
        );
        
        $history = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $history[] = $row;
            }
        }
        
        $mysqli->close();
        return $history;
    }
    
    /**
     * Detect suspicious modifications
     */
    public static function detect_suspicious_activity($attempt_id) {
        $history = self::get_submission_history($attempt_id);
        
        if (count($history) < 2) {
            return ['suspicious' => false];
        }
        
        $suspicions = [];
        
        // Check 1: Rapid modifications (many submissions in short time)
        $first_time = strtotime($history[0]['timestamp']);
        $timestamps = array_map(function($h) { return strtotime($h['timestamp']); }, $history);
        $time_diffs = array_map(function($t) use ($first_time) { return $t - $first_time; }, $timestamps);
        
        // If 10 submissions in less than 5 minutes, suspicious
        if (count($history) >= 10 && end($time_diffs) < 300) {
            $suspicions[] = 'Rapid submission pattern - possible automated attack';
        }
        
        // Check 2: Huge code changes (size increases dramatically)
        for ($i = 1; $i < count($history); $i++) {
            $size_diff = $history[$i]['code_size'] - $history[$i-1]['code_size'];
            if (abs($size_diff) > 5000) { // 5KB size change
                $suspicions[] = "Large code change between versions $i and " . ($i+1) . 
                               " (diff: " . $size_diff . " bytes)";
            }
        }
        
        // Check 3: Modification after due date (if applicable)
        // This would require quiz due date to be available
        
        return [
            'suspicious' => !empty($suspicions),
            'issues' => $suspicions,
            'total_versions' => count($history)
        ];
    }
    
    /**
     * Create digital signature for proof of submission
     */
    public static function create_signature($attempt_id, $code_hash, $secret_key) {
        $data = "$attempt_id:$code_hash:" . date('Y-m-d');
        $signature = hash_hmac('sha256', $data, $secret_key);
        return $signature;
    }
    
    /**
     * Store audit entry in database
     */
    private static function store_in_database($attempt_id, $userid, $code, $hash, $timestamp) {
        global $DB;
        
        try {
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $user_agent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'unknown', 0, 255);
            $code_size = strlen($code);
            
            // Use Moodle's database layer instead of direct mysqli
            $record = (object) [
                'attempt_id' => $attempt_id,
                'userid' => $userid,
                'code_hash' => $hash,
                'code_size' => $code_size,
                'ip_address' => $ip_address,
                'user_agent' => $user_agent,
                'timestamp' => date('Y-m-d H:i:s', $timestamp),
                'action' => 'SUBMISSION'
            ];
            
            $DB->insert_record('code_audit', $record);
            return true;
        } catch (Exception $e) {
            error_log('CodeAudit store_in_database error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Write audit entry to log file
     */
    private static function write_log($entry) {
        $log_file = 'E:\moodel_xampp\moodledata\code_audit.log';
        $log_dir = dirname($log_file);
        
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0700, true);
        }
        
        $log_line = json_encode($entry) . "\n";
        file_put_contents($log_file, $log_line, FILE_APPEND);
    }
}

// ===== DATABASE TABLE CREATION =====
// Run this once to create the audit table:
/*
$mysqli = new mysqli('localhost', 'root', '', 'moodle');

$sql = "
CREATE TABLE IF NOT EXISTS mdl_code_audit (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    attempt_id BIGINT NOT NULL,
    userid BIGINT NOT NULL,
    code_hash VARCHAR(64) NOT NULL COMMENT 'SHA256 hash of code',
    code_size INT,
    code_snippet LONGTEXT COMMENT 'First 500 chars of code for reference',
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    timestamp DATETIME,
    action VARCHAR(50),
    
    FOREIGN KEY (attempt_id) REFERENCES mdl_quiz_attempts(id) ON DELETE CASCADE,
    FOREIGN KEY (userid) REFERENCES mdl_user(id) ON DELETE CASCADE,
    
    INDEX idx_attempt (attempt_id),
    INDEX idx_user (userid),
    INDEX idx_timestamp (timestamp)
);
";

if ($mysqli->query($sql)) {
    echo "✅ Table created successfully";
} else {
    echo "❌ Error: " . $mysqli->error;
}

$mysqli->close();
*/

// ===== USAGE EXAMPLE =====
/*
// When student submits code:
$code = $_POST['answer'];
$attempt_id = 123;
$userid = 5;

// Log the submission
CodeAudit::log_submission($attempt_id, $userid, $code);

// Later, verify code hasn't been tampered:
$current_code = $DB->get_record('quiz_attempt', ['id' => $attempt_id])->answer;
$integrity = CodeAudit::verify_integrity($attempt_id, $current_code);

if (!$integrity['valid']) {
    echo "⚠️ WARNING: Code has been modified!";
    log_security_event('CODE_TAMPERING_DETECTED', ['attempt_id' => $attempt_id]);
}

// Check for suspicious activity:
$suspicious = CodeAudit::detect_suspicious_activity($attempt_id);
if ($suspicious['suspicious']) {
    echo "🚨 ALERT: Suspicious activity detected!";
    foreach ($suspicious['issues'] as $issue) {
        echo "  - $issue\n";
    }
}
*/
?>
