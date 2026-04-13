<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== SETTING Q20 TEMPLATE EXPLICITLY ===\n\n";

// Get the python3_w_input prototype template
$proto17 = $DB->get_record('question_coderunner_options', ['questionid' => 17]);
$python_template = $proto17->template;

echo "Python3_w_input template length: " . strlen($python_template) . "\n";
echo "First 150 chars:\n" . substr($python_template, 0, 150) . "\n\n";

// Update Q20 to use this template
$q20 = $DB->get_record('question_coderunner_options', ['questionid' => 20]);
$q20->template = $python_template;
$DB->update_record('question_coderunner_options', $q20);

echo "✅ Q20 template updated!\n\n";

// Verify
$q20 = $DB->get_record('question_coderunner_options', ['questionid' => 20]);
echo "Q20 template length now: " . strlen($q20->template) . "\n";
echo "First 150 chars:\n" . substr($q20->template, 0, 150) . "\n";

// Clear cache
purge_all_caches();
echo "\n✅ Cache cleared!\n";
?>
