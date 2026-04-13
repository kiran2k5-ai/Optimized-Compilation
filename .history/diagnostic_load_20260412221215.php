<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== QUESTION LOADING DIAGNOSTIC ===\n\n";

// Load the question exactly like Moodle does
echo "1. Loading question from question bank...\n";
require_once('question/bank/loadquestions.php');

$q = question_bank::load_question(20);

echo "   Question ID: {$q->id}\n";
echo "   Question type: {$q->qtype}\n";
echo "   Grader: {$q->grader}\n";
echo "   Template length: " . strlen($q->template) . "\n";
echo "   Coderunner type: {$q->coderunnertype}\n\n";

if ($q->grader !== 'EqualityGrader') {
    echo "❌ ERROR: Grader is '{$q->grader}' but should be 'EqualityGrader'\n";
} else {
   echo "✅ Grader is correct: EqualityGrader\n";
}

// Check the grader instance
echo "\n2. Getting grader instance...\n";
$grader = $q->get_grader();
echo "   Grader class: " . get_class($grader) . "\n";
echo "   Grader name: " . $grader->name() . "\n";
?>
