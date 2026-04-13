<?php
/**
 * AUTOMATED DEPLOYMENT SCRIPT
 * Run this once to deploy the security system
 */

define('CLI_SCRIPT', true);
require_once(__DIR__ . '/config.php');

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║          AUTOMATED SECURITY DEPLOYMENT SCRIPT                 ║\n";
echo "║                  CodeRunner Security System                   ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

$success_count = 0;
$error_count = 0;

// ===== STEP 1: CHECK FILES =====
echo "[STEP 1/5] Checking required files...\n";
$required_files = [
    'CodeValidator.php',
    'CodeAudit.php',
    'SecureCodeSubmissionHandler.php'
];

foreach ($required_files as $file) {
    $filepath = __DIR__ . '/' . $file;
    if (file_exists($filepath)) {
        echo "  ✅ $file found\n";
        $success_count++;
    } else {
        echo "  ❌ $file NOT FOUND - Please copy to Moodle root\n";
        $error_count++;
    }
}

// ===== STEP 2: CREATE DATABASE TABLES =====
echo "\n[STEP 2/5] Creating database tables...\n";

$tables_sql = [
    'code_audit' => "
        CREATE TABLE IF NOT EXISTS {code_audit} (
            id BIGINT PRIMARY KEY AUTO_INCREMENT,
            attempt_id BIGINT NOT NULL,
            userid BIGINT NOT NULL,
            code_hash VARCHAR(64) NOT NULL,
            code_size INT,
            code_snippet LONGTEXT,
            ip_address VARCHAR(45),
            user_agent VARCHAR(255),
            timestamp DATETIME,
            action VARCHAR(50),
            
            FOREIGN KEY (attempt_id) REFERENCES {quiz_attempts}(id) ON DELETE CASCADE,
            FOREIGN KEY (userid) REFERENCES {user}(id) ON DELETE CASCADE,
            
            INDEX idx_attempt (attempt_id),
            INDEX idx_user (userid),
            INDEX idx_timestamp (timestamp)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    'code_validation_log' => "
        CREATE TABLE IF NOT EXISTS {code_validation_log} (
            id BIGINT PRIMARY KEY AUTO_INCREMENT,
            userid BIGINT,
            timestamp DATETIME,
            code_submitted LONGTEXT,
            validation_errors LONGTEXT,
            was_accepted TINYINT,
            
            FOREIGN KEY (userid) REFERENCES {user}(id) ON DELETE CASCADE,
            INDEX idx_user (userid),
            INDEX idx_timestamp (timestamp)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    "
];

foreach ($tables_sql as $table_name => $sql) {
    try {
        $DB->execute($sql);
        echo "  ✅ Table {$table_name} created/verified\n";
        $success_count++;
    } catch (Exception $e) {
        echo "  ❌ Error creating table {$table_name}: " . $e->getMessage() . "\n";
        $error_count++;
    }
}

// ===== STEP 3: CREATE LOG DIRECTORIES =====
echo "\n[STEP 3/5] Creating log directories...\n";

$log_dirs = [
    'E:\moodel_xampp\moodledata\logs',
    'E:\moodel_xampp\moodledata\audit'
];

foreach ($log_dirs as $dir) {
    if (!is_dir($dir)) {
        if (@mkdir($dir, 0700, true)) {
            echo "  ✅ Created: $dir\n";
            $success_count++;
        } else {
            echo "  ⚠️  Could not create: $dir\n";
            $error_count++;
        }
    } else {
        echo "  ✅ Exists: $dir\n";
        $success_count++;
    }
}

// ===== STEP 4: UPDATE CONFIGURATION =====
echo "\n[STEP 4/5] Updating configuration...\n";

// Check if security config already exists
$config_file = __DIR__ . '/config.php';
$config_content = file_get_contents($config_file);

if (strpos($config_content, 'coderunner_security_enabled') === false) {
    // Add security config
    $security_config = "
// ===== CODERUNNER SECURITY SETTINGS =====
\$CFG->coderunner_security_enabled = true;
\$CFG->coderunner_enable_code_validation = true;
\$CFG->coderunner_enable_audit_logging = true;
\$CFG->coderunner_max_code_size = 100000;
\$CFG->coderunner_max_input_size = 100000;
\$CFG->coderunner_max_output_size = 50000;
\$CFG->coderunner_execution_timeout = 5;
\$CFG->coderunner_max_submissions_per_minute = 10;
\$CFG->coderunner_max_ip_requests_per_minute = 50;
\$CFG->coderunner_detect_suspicious_patterns = true;
\$CFG->coderunner_log_suspicious_activity = true;
";
    
    // Insert before the final comment
    $updated_config = str_replace(
        "// There is no php closing tag",
        $security_config . "\n// There is no php closing tag",
        $config_content
    );
    
    if (file_put_contents($config_file, $updated_config)) {
        echo "  ✅ Security configuration added to config.php\n";
        $success_count++;
    } else {
        echo "  ❌ Could not update config.php\n";
        $error_count++;
    }
} else {
    echo "  ℹ️  Security configuration already present in config.php\n";
    $success_count++;
}

// ===== STEP 5: RUN TESTS =====
echo "\n[STEP 5/5] Running validation tests...\n";

try {
    require_once(__DIR__ . '/CodeValidator.php');
    
    // Test 1: Safe code
    $test1 = CodeValidator::validate('print(1+1)');
    if ($test1['is_valid']) {
        echo "  ✅ Safe code validation: PASS\n";
        $success_count++;
    } else {
        echo "  ❌ Safe code validation: FAIL\n";
        $error_count++;
    }
    
    // Test 2: Dangerous code
    $test2 = CodeValidator::validate('import os\nos.system("whoami")');
    if (!$test2['is_valid']) {
        echo "  ✅ Dangerous code blocking: PASS\n";
        $success_count++;
    } else {
        echo "  ❌ Dangerous code blocking: FAIL\n";
        $error_count++;
    }
    
    // Test 3: Code audit
    require_once(__DIR__ . '/CodeAudit.php');
    $audit = CodeAudit::log_submission(1, 3, 'test_code');
    if ($audit['success']) {
        echo "  ✅ Code audit logging: PASS\n";
        $success_count++;
    } else {
        echo "  ❌ Code audit logging: FAIL\n";
        $error_count++;
    }
    
} catch (Exception $e) {
    echo "  ❌ Test error: " . $e->getMessage() . "\n";
    $error_count++;
}

// ===== DEPLOYMENT SUMMARY =====
echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║                    DEPLOYMENT SUMMARY                          ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

echo "Successful steps:   ✅ $success_count\n";
echo "Failed steps:       ❌ $error_count\n\n";

if ($error_count === 0) {
    echo "🎉 DEPLOYMENT SUCCESSFUL!\n\n";
    echo "Next steps:\n";
    echo "  1. Integrate with quiz submission handler\n";
    echo "  2. Add CSRF tokens to forms\n";
    echo "  3. Access admin dashboard at: /admin/code_security.php\n";
    echo "  4. Test with actual quiz attempt\n";
    echo "  5. Monitor logs daily\n\n";
    echo "Documentation: See DEPLOYMENT_GUIDE.txt\n";
} else {
    echo "⚠️  DEPLOYMENT INCOMPLETE!\n\n";
    echo "Please fix the errors above:\n";
    echo "  1. Ensure all 3 PHP files are copied to Moodle root\n";
    echo "  2. Check database connectivity\n";
    echo "  3. Verify file permissions\n";
    echo "  4. Check E:\moodel_xampp\moodledata\debug.log for details\n";
}

echo "\n";
?>
