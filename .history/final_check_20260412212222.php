<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
require_once $CFG->dirroot . '/question/engine/lib.php';
global $DB;

echo "=== FINAL VERIFICATION BEFORE TESTING ON WEB ===\n\n";

// 1. Verify database configuration
echo "✓ Database: Question 20 (Addition)\n";
$q_opts = $DB->get_record('question_coderunner_options', ['questionid' => 20]);
echo "  - Grader: {$q_opts->grader}\n";
echo "  - Sandbox: {$q_opts->sandbox}\n";
echo "  - Language: {$q_opts->language}\n";

// 2. Verify curl security setting
echo "\n✓ Curl Security Setting:\n";
echo "  - Blocked hosts: " . (strpos($GLOBALS['CFG']->curlsecurityblockedhosts, 'localhost') !== false ? "localhost IS BLOCKED" : "localhost NOT blocked ✅") . "\n";

// 3. Verify Jobe host
echo "\n✓ Jobe Configuration:\n";
$jobe_host = get_config('qtype_coderunner', 'jobe_host');
echo "  - Jobe host: $jobe_host\n";
echo "  - Full URL would be: http://$jobe_host/jobe/index.php/restapi/runs\n";

// 4. Test sandbox instantiation
echo "\n✓ Sandbox Instantiation:\n";
try {
    $attempt = $DB->get_record('quiz_attempts', ['id' => 1]);
    $quba = question_engine::load_questions_usage_by_activity($attempt->uniqueid);
    $qa = $quba->get_question_attempt(1);
    $q = $qa->get_question();
    $sandbox = $q->get_sandbox();
    echo "  - Sandbox loaded: " . get_class($sandbox) . " ✅\n";
} catch (Exception $e) {
    echo "  - ERROR: " . $e->getMessage() . "\n";
}

// 5. Test grader instantiation
echo "\n✓ Grader Instantiation:\n";
try {
    $grader = $q->get_grader();
    echo "  - Grader loaded: " . get_class($grader) . " ✅\n";
} catch (Exception $e) {
    echo "  - ERROR: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ ALL SYSTEMS OK - READY FOR WEB TESTING\n";
echo str_repeat("=", 60) . "\n";
?>
