<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== CHECKING QUESTION 20 (Addition) ===\n";
$q = $DB->get_record('question', ['id' => 20]);
if ($q) {
    echo "Question 20:\n";
    echo "  Name: {$q->name}\n";
    echo "  QType: {$q->qtype}\n";
} else {
    echo "Question 20 NOT FOUND\n";
}

// Get question options
$opts = $DB->get_record('qtype_coderunner_options', ['questionid' => 20]);
if ($opts) {
    echo "\nQuestion Options:\n";
    echo "  Sandbox: {$opts->sandbox}\n";
    echo "  Language: {$opts->language}\n";
    echo "  Grader: {$opts->grader}\n";
} else {
    echo "Question options NOT FOUND\n";
}

echo "\n=== CHECKING QUIZ SLOT 1 ===\n";
$slot = $DB->get_record('quiz_slots', ['quizid' => 1, 'slot' => 1]);
if ($slot) {
    echo "Slot 1:\n";
    echo "  Question ID: {$slot->questionid}\n";
    echo "  Require Previous: {$slot->requireprevious}\n";
} else {
    echo "Quiz slot NOT FOUND\n";
}

echo "\n=== CHECKING QUIZ ATTEMPTS ===\n";
$attempt = $DB->get_record('quiz_attempts', ['id' => 1]);
if ($attempt) {
    echo "Attempt 1:\n";
    echo "  Quiz ID: {$attempt->quiz}\n";
    echo "  User ID: {$attempt->userid}\n";
    echo "  State: {$attempt->state}\n";
} else {
    echo "Attempt 1 NOT FOUND\n";
}

echo "\n=== CHECKING QUESTION ATTEMPTS ===\n";
$qattempt = $DB->get_record('question_attempts', ['responsesummary' => '', 'questionusageid' => 1]);
if ($qattempt) {
    echo "Question Attempt found:\n";
    echo "  Question ID: {$qattempt->questionid}\n";
    echo "  Slot: {$qattempt->slot}\n";
} else {
    echo "No question attempts found with responsesummary=''\n";
    // Try getting whatever exists
    $qa = $DB->get_records('question_attempts', ['questionusageid' => 1], '', 'id, slot, questionid, responsesummary');
    if ($qa) {
        echo "But found other question attempts:\n";
        foreach ($qa as $q) {
            echo "  Slot {$q->slot}: Q{$q->questionid} - '{$q->responsesummary}'\n";
        }
    }
}
?>
