<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
require_once $CFG->dirroot . '/question/engine/lib.php';
require_once $CFG->dirroot . '/question/type/coderunner/classes/sandbox.php';
global $DB;

// Load the question
$attempt = $DB->get_record('quiz_attempts', ['id' => 1]);
$quba = question_engine::load_questions_usage_by_activity($attempt->uniqueid);

$qa = $quba->get_question_attempt(1);
$q = $qa->get_question();

echo "=== EXAMINING QUESTION FOR get_sandbox() CALL ===\n";
echo "Question ID: {$q->id}\n";
echo "Question type: " . get_class($q) . "\n";
echo "Question->sandbox: " . var_export($q->sandbox, true) . "\n";
echo "Question->language: " . var_export($q->language, true) . "\n";

// Now manually trace through get_sandbox
echo "\n=== TRACING get_sandbox() MANUALLY ===\n";

$sandbox = $q->sandbox;
echo "1. \$sandbox = \$q->sandbox = " . var_export($sandbox, true) . "\n";

if ($sandbox === null) {
    echo "2. Sandbox is NULL - will call get_best_sandbox\n";
} else {
    echo "2. Sandbox is NOT NULL - will call get_instance('$sandbox')\n";
    
    // Check what get_instance will do
    echo "\n=== TRACING get_instance('$sandbox') ===\n";
    
    $boxes = qtype_coderunner_sandbox::enabled_sandboxes();
    echo "3. enabled_sandboxes() returned:\n";
    var_dump($boxes);
    
    if (array_key_exists($sandbox, $boxes)) {
        $classname = $boxes[$sandbox];
        echo "4. \$classname = \$boxes['$sandbox'] = " . var_export($classname, true) . "\n";
        echo "   Type of \$classname: " . gettype($classname) . "\n";
        
        if (is_string($classname)) {
            echo "5. \$classname is a string - will try new \$classname()\n";
            
            try {
                $sb = new $classname();
               echo "6. SUCCESS! Created sandbox: " . get_class($sb) . "\n";
            } catch (Exception $e) {
                echo "6. ERROR: " . $e->getMessage() . "\n";
            }
        } else {
            echo "5. ERROR! \$classname is NOT a string, it's " . get_class($classname) . "\n";
        }
    } else {
        echo "4. ERROR! Sandbox '$sandbox' not found in enabled sandboxes\n";
        echo "   Available: " . implode(', ', array_keys($boxes)) . "\n";
    }
}
?>
