<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== CLEARING QUESTION ATTEMPT CACHE ===\n\n";

// Find all question attempts for Q20 in attempt 1
$qry = "SELECT qa.* FROM {question_attempts} qa 
        WHERE qa.questionid = 20 
        AND qa.quizattemptid IN (SELECT id FROM {quiz_attempts} WHERE id = 1)";

$q_attempts = $DB->get_recordset_sql($qry);

$deleted = 0;
foreach ($q_attempts as $qa) {
    echo "Found question attempt {$qa->id}\n";
    
    // Delete all steps for this attempt
    $DB->delete_records_list('question_attempt_steps', 'questionattemptid', [$qa->id]);
    echo "  ✅ Deleted steps\n";
    
    // Delete the question attempt itself
    $DB->delete_records('question_attempts', ['id' => $qa->id]);
    echo "  ✅ Deleted question attempt\n";
    
    $deleted++;
}

$q_attempts->close();

echo "\n✅ Deleted $deleted question attempt(s)\n\n";

// Verify the current template
$q20 = $DB->get_record('question_coderunner_options', ['questionid' => 20]);
echo "Current Q20 settings:\n";
echo "  Grader: {$q20->grader}\n";
echo "  Is Combinator: {$q20->iscombinatortemplate}\n";
echo "  Template (first 100 chars): " . substr($q20->template, 0, 100) . "...\n\n";

echo "Now when you reload the page, Q20 will be re-created fresh\n";
echo "with the current template that shows output!\n";
?>
