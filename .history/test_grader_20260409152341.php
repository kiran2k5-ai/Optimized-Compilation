<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
require_once $CFG->dirroot . '/question/engine/lib.php';
require_once $CFG->dirroot . '/question/type/coderunner/classes/grader.php';
global $DB;

$attempt = $DB->get_record('quiz_attempts', ['id' => 1]);
$quba = question_engine::load_questions_usage_by_activity($attempt->uniqueid);

$qa = $quba->get_question_attempt(1);
$q = $qa->get_question();

echo "=== CHECKING GRADER CONFIGURATION ===\n";
echo "Question->grader = '" . $q->grader . "' (type: " . gettype($q->grader) . ")\n";

echo "\nAvailable graders:\n";
$graders = qtype_coderunner_grader::available_graders();
foreach ($graders as $key => $class) {
    echo "  '$key' => '$class'\n";
}

echo "\nNow trying to get the grader instance...\n";
try {
    $grader = $q->get_grader();
    echo "SUCCESS! Got grader: " . get_class($grader) . "\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n";
    foreach ($e->getTrace() as $i => $t) {
        $func = ($t['class'] ?? '?') . '::' . $t['function'];
        echo "  #{$i} $func() at " . basename($t['file']) . ":" . ($t['line'] ?? '?') . "\n";
    }
}
?>
