<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
require_once $CFG->dirroot . '/question/engine/lib.php';
global $DB;

echo "=== CHECKING GET_SANDBOX ERROR ===\n";

// Load the quiz attempt  
$attempt = $DB->get_record('quiz_attempts', ['id' => 1]);
$quba = question_engine::load_questions_usage_by_activity($attempt->uniqueid);

$qa = $quba->get_question_attempt(1);
$q = $qa->get_question();

echo "Q ID: {$q->id}\n";
echo "Q type: {$q->qtype}\n";

// Check options
if (isset($q->options)) {
    $opts = $q->options;
    echo "Options object type: " . get_class($opts) . "\n";
    echo "Sandbox property: " . var_export($opts->sandbox, true) . "\n";
    echo "Language property: " . var_export($opts->language, true) . "\n";
    
    // Try to call get_sandbox
    echo "\nCalling get_sandbox()...\n";
    try {
        $sandbox = $q->get_sandbox();
        echo "SUCCESS! Got sandbox instance: " . get_class($sandbox) . "\n";
    } catch (TypeError $te) {
        echo "TypeError: " . $te->getMessage() . "\n";
        echo "Stack:\n";
        foreach ($te->getTrace() as $i => $t) {
            $func = ($t['class'] ?? '') . ($t['class'] ? '::' : '') . $t['function'];
            echo "  #{$i} $func() at {$t['file']}:{$t['line']}\n";
        }
    } catch (Exception $e) {
        echo "Exception: " . $e->getMessage() . "\n";
        echo "Stack:\n";
        foreach ($e->getTrace() as $i => $t) {
            $func = ($t['class'] ?? '') . ($t['class'] ? '::' : '') . $t['function'];
            echo "  #{$i} $func() at {$t['file']}:{$t['line']}\n";
        }
    }
} else {
    echo "No options!\n";
}
?>
