<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== QUESTION LOADING DIAGNOSTIC ===\n\n";

// Load Q20 directly from database
echo "1. Loading Q20 from database...\n";
$question_record = $DB->get_record('question', ['id' => 20]);
$options_record = $DB->get_record('question_coderunner_options', ['questionid' => 20]);

echo "   Question ID: {$question_record->id}\n";
echo "   Question name: {$question_record->name}\n";
echo "   Question type: {$question_record->qtype}\n";
echo "   Grader from options: {$options_record->grader}\n\n";

if ($options_record->grader !== 'EqualityGrader') {
    echo "❌ ERROR: Grader is '{$options_record->grader}' but should be 'EqualityGrader'\n";
} else {
    echo "✅ Grader is correct in DB: EqualityGrader\n";
}

// Try loading through question bank
echo "\n2. Loading via Moodle's question bank...\n";
require_once('question/bank/loadquestions.php');

try {
    $q = question_bank::load_question(20);
    echo "   Question loaded successfully\n";
    echo "   Loaded grader: {$q->grader}\n";
    
    if ($q->grader !== 'EqualityGrader') {
        echo "   ❌ WARNING: Question bank loaded grader as '{$q->grader}'\n";
    } else {
        echo "   ✅ Question bank correctly loaded as EqualityGrader\n";  
    }
} catch (Exception $e) {
    echo "   Error loading through question bank: " . $e->getMessage() . "\n";
}
?>
