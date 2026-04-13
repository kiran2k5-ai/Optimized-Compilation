<?php
define('CLI_SCRIPT', true);
require 'config.php';

echo "=== Checking Quiz Attempt Configuration ===\n\n";

// Check attempt 1
$attempt = $DB->get_record('quiz_attempts', ['id' => 1]);
if ($attempt) {
    echo "Attempt ID: 1\n";
    echo "  - Quiz ID: {$attempt->quiz}\n";
    
    // Get quiz details
    $quiz = $DB->get_record('quiz', ['id' => $attempt->quiz]);
    echo "  - Quiz: {$quiz->name}\n";
    
    // Get course module
    $cm = $DB->get_record('course_modules', ['id' => 2]);
    echo "  - Course Module ID: {$cm->id}\n";
    echo "  - Instance (quiz): {$cm->instance}\n";
    
    // Get quiz questions
    $slots = $DB->get_records('quiz_slots', ['quizid' => $attempt->quiz]);
    echo "  - Questions in quiz:\n";
    
    foreach ($slots as $slot) {
        $question = $DB->get_record('question', ['id' => $slot->questionid]);
        $options = $DB->get_record('question_coderunner_options', ['questionid' => $question->id]);
        
        echo "    - Question ID: {$slot->questionid} ({$question->name})\n";
        if ($options) {
            echo "      - Sandbox: {$options->sandbox}\n";
            echo "      - Language: {$options->language}\n";
        } else {
            echo "      - Not a CodeRunner question\n";
        }
    }
} else {
    echo "Attempt 1 not found!\n";
}

echo "\n=== Checking All CodeRunner Questions ===\n";
$all_coderunner = $DB->get_records('question_coderunner_options', []);
foreach ($all_coderunner as $q) {
    $question = $DB->get_record('question', ['id' => $q->questionid]);
    echo "Q{$q->questionid}: {$question->name}\n";
    echo "  - Sandbox: " . ($q->sandbox ?? 'NULL (uses default)') . "\n";
}
?>
