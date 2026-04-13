<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
require_once $CFG->dirroot . '/question/engine/lib.php';
global $DB;

$attempt = $DB->get_record('quiz_attempts', ['id' => 1]);
$quba = question_engine::load_questions_usage_by_activity($attempt->uniqueid);

$qa = $quba->get_question_attempt(1);
$q = $qa->get_question();

echo "Question object type: " . get_class($q) . "\n";
echo "All properties of question:\n";

// Get object properties manually
$refl = new ReflectionObject($q);
$props = $refl->getProperties();

foreach ($props as $prop) {
    $prop->setAccessible(true);
    $name = $prop->getName();
    $value = $prop->getValue($q);
    
    $type = gettype($value);
    if ($type === 'object') {
        $value_str = "Object(" . get_class($value) . ")";
    } else if ($type === 'array') {
        $value_str =  "Array[" . count($value) . "]";
    } else if (is_string($value) && strlen($value) > 50) {
        $value_str = '"' . substr($value, 0, 50) . '..."';
    } else {
        $value_str = var_export($value, true);
    }
    
    echo "  \$$name = $value_str\n";
}

// Check options specifically
echo "\nQuestion->options properties:\n";
if (isset($q->options)) {
    $opts = $q->options;
    echo "Options object type: " . get_class($opts) . "\n";
    $refl2 = new ReflectionObject($opts);
    $props2 = $refl2->getProperties();
    foreach ($props2 as $prop) {
        $prop->setAccessible(true);
        $name = $prop->getName();
        $value = $prop->getValue($opts);
        if (in_array($name, ['sandbox', 'language', 'grader'])) {
            echo "  \$$name = " . var_export($value, true) . "\n";
        }
    }
} else {
    echo "No options object!\n";
}
?>
