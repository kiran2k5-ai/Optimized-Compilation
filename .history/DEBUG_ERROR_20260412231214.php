<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include Moodle bootstrap
require_once(__DIR__ . '/public/config.php');

echo "=== DEBUGGING QUESTION ERROR ===\n\n";

// Check all questions in the database
$questions = $DB->get_records('question');
echo "Total questions in database: " . count($questions) . "\n";

// Check CodeRunner questions specifically
$cr_questions = $DB->get_records('question_coderunner');
echo "CodeRunner questions: " . count($cr_questions) . "\n\n";

// List all questions with their types
echo "All Questions:\n";
foreach ($questions as $q) {
    echo "ID: {$q->id}, Name: {$q->name}, Type: {$q->qtype}, Category: {$q->category}\n";
}

echo "\n=== CODERUNNER QUESTIONS ===\n";
foreach ($cr_questions as $cr) {
    echo "Question ID: {$cr->questionid}, Type: {$cr->coderunnertype}\n";
}

// Try to load question 20 with more detail
echo "\n=== QUESTION 20 DETAILS ===\n";
$q20 = $DB->get_record('question', ['id' => 20]);
if ($q20) {
    echo "Found! Type: {$q20->qtype}\n";
    $cr20 = $DB->get_record('question_coderunner', ['questionid' => 20]);
    if ($cr20) {
        echo "CodeRunner Type: {$cr20->coderunnertype}\n";
        echo "Sandbox: {$cr20->sandbox}\n";
        echo "Grader: {$cr20->grader}\n";
    }
} else {
    echo "Question 20 NOT FOUND!\n";
}

echo "\n";
?>
