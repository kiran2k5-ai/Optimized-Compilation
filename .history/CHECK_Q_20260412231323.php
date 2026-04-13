<?php
// Enable error reporting
define('CLI_SCRIPT', true);
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include Moodle bootstrap
require_once(__DIR__ . '/public/config.php');

echo "=== CHECKING QUESTION CONFIGURATION ===\n\n";

// Direct query to check questions
try {
    $sql = "SELECT id, name, qtype, category FROM {question} ORDER BY id";
    $questions = $DB->get_records_sql($sql);
    echo "Total questions: " . count($questions) . "\n\n";
    
    foreach ($questions as $q) {
        echo "ID: {$q->id}, Type: {$q->qtype}, Name: {$q->name}\n";
    }
    
    echo "\n=== CHECKING QUESTION 20 ===\n";
    $q20 = $DB->get_record_sql("SELECT * FROM {question} WHERE id = 20");
    if ($q20) {
        echo "Found Question 20!\n";
        echo "Type: {$q20->qtype}\n";
        
        // Check if it's in question_coderunner table
        $cr = $DB->get_record_sql("SELECT * FROM {question_coderunner} WHERE questionid = 20");
        if ($cr) {
            echo "CodeRunner Config Found:\n";
            echo "  - coderunnertype: {$cr->coderunnertype}\n";
            echo "  - sandbox: {$cr->sandbox}\n";
            echo "  - grader: {$cr->grader}\n";
        } else {
            echo "ERROR: No CodeRunner config found for Question 20!\n";
        }
    } else {
        echo "ERROR: Question 20 not found!\n";
    }
    
} catch (Exception $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
}
?>
