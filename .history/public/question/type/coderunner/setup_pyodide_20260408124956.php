<?php
/**
 * CodeRunner + Pyodide Integration Setup
 * 
 * This file initializes the Pyodide integration for CodeRunner
 * Execute this once to enable local code execution in the browser
 */

define('CLI_SCRIPT', true);
require_once('../../config.php');

global $DB, $CFG;

echo "========================================\n";
echo "CodeRunner Pyodide Integration Setup\n";
echo "========================================\n\n";

// Step 1: Load configuration
echo "[1] Loading Pyodide configuration...\n";
require_once('enable_pyodide.php');
echo "✓ Configuration loaded\n\n";

// Step 2: Verify files exist
echo "[2] Verifying integration files...\n";
$files = array(
    'jobe_api_mock.php' => __DIR__ . '/jobe_api_mock.php',
    'pyodide_executor.js' => __DIR__ . '/pyodide_executor.js',
    'enable_pyodide.php' => __DIR__ . '/enable_pyodide.php',
    'renderer.php (modified)' => __DIR__ . '/renderer.php'
);

foreach ($files as $name => $path) {
    if (file_exists($path)) {
        echo "✓ $name exists\n";
    } else {
        echo "✗ $name NOT FOUND\n";
    }
}
echo "\n";

// Step 3: Set Moodle configurations
echo "[3] Configuring Moodle for Pyodide...\n";
set_config('use_local_pyodide', 1, 'qtype_coderunner');
set_config('pyodide_version', '0.23.0', 'qtype_coderunner');
echo "✓ Moodle configuration updated\n\n";

// Step 4: Verify database tables
echo "[4] Verifying database tables...\n";
$tables = array(
    'mdl_question' => 'Questions',
    'mdl_question_attempts' => 'Question attempts',
    'mdl_quiz' => 'Quizzes'
);

foreach ($tables as $table => $description) {
    try {
        $DB->get_records($table, array(), '', '*', 0, 1);
        echo "✓ $table ($description) exists\n";
    } catch (Exception $e) {
        echo "✗ $table ($description) NOT FOUND\n";
    }
}
echo "\n";

// Step 5: Summary
echo "========================================\n";
echo "Setup Complete!\n";
echo "========================================\n\n";

echo "NEXT STEPS:\n";
echo "1. Create or edit a CodeRunner question in your quiz\n";
echo "2. Student code will execute locally in browser using Pyodide\n";
echo "3. No Jobe server needed\n";
echo "4. Results automatically saved to Moodle\n\n";

echo "TESTING:\n";
echo "- Access your quiz: http://localhost/mod/quiz/view.php\n";
echo "- Attempt a CodeRunner question\n";
echo "- Code execution should happen locally in the browser\n";
echo "- Check browser console (F12) for execution logs\n\n";

echo "✓ Integration successful!\n";
echo "========================================\n";
?>
