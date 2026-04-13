<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== DEBUGGING GRADER ISSUE ===\n\n";

// Check what's in the database
$q20 = $DB->get_record('question_coderunner_options', ['questionid' => 20]);
echo "Q20 Grader in DB: '{$q20->grader}'\n\n";

// Check what graders exist in CodeRunner
echo "Checking CodeRunner grader classes...\n";
$grader_path = 'public/question/type/coderunner/classes/graders';
if (is_dir($grader_path)) {
    $files = scandir($grader_path);
    echo "Grader files found:\n";
    foreach ($files as $f) {
        if (strpos($f, '.php') > 0) {
            echo "  - $f\n";
        }
    }
} else {
    echo "Graders directory not found\n";
}

// Let's check the grading code
echo "\n\nLet me check what Template Grader expects...\n";
$render_file = 'public/question/type/coderunner/classes/Twig_grader.php';
if (file_exists($render_file)) {
    echo "TemplateGrader found\n";
} else {
    echo "TemplateGrader not found\n";
}
?>
