<?php
/**
 * MOODLE CONFIG TEST - Simple connection check
 */

define('CLI_SCRIPT', true);
$moodle_root = dirname(dirname(__FILE__));

echo "\n";
echo "==============================================\n";
echo "MOODLE CONFIG & DATABASE TEST\n";
echo "==============================================\n\n";

echo "[1] Loading Moodle config.php...\n";

try {
    require_once($moodle_root . '/config.php');
    echo "  ✓ Config loaded successfully\n";
} catch (Exception $e) {
    echo "  ✗ FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n[2] Testing database connection...\n";

try {
    global $DB;
    
    // Simple test - just try to count users
    $user_count = $DB->count_records('user');
    echo "  ✓ Database connected!\n";
    echo "  Total users: $user_count\n";
    
} catch (Exception $e) {
    echo "  ✗ Connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n[3] Testing quiz tables...\n";

try {
    global $DB;
    
    $quiz_count = $DB->count_records('quiz');
    $attempt_count = $DB->count_records('quiz_attempts');
    $slot_count = $DB->count_records('quiz_slots');
    $question_count = $DB->count_records('question');
    
    echo "  ✓ Quiz table: $quiz_count quizzes\n";
    echo "  ✓ Quiz attempts: $attempt_count attempts\n";
    echo "  ✓ Quiz slots: $slot_count slots\n";
    echo "  ✓ Questions: $question_count questions\n";
    
} catch (Exception $e) {
    echo "  ⚠ Warning: " . $e->getMessage() . "\n";
}

echo "\n";
echo "==============================================\n";
echo "✓ MOODLE DATABASE IS READY\n";
echo "==============================================\n\n";

?>
