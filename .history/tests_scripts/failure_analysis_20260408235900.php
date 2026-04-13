<?php
/**
 * COMPREHENSIVE FAILURE ANALYSIS
 * Shows exactly what's failing and why
 */

define('CLI_SCRIPT', true);
$moodle_root = dirname(dirname(dirname(dirname(__FILE__))));  // Goes up 4 levels to moodle root

echo "\n";
echo "==============================================\n";
echo "COMPREHENSIVE TEST FAILURE ANALYSIS\n";
echo "==============================================\n\n";
echo "Moodle Root: $moodle_root\n\n";

require_once($moodle_root . '/config.php');
require_once($moodle_root . '/public/question/type/coderunner/jobe_api_mock.php');
require_once($moodle_root . '/public/question/type/coderunner/enable_pyodide.php');

$issues = [];

// ==============================================
// ISSUE 1: Test jobe_api_mock.php functions
// ==============================================
echo "[1] TESTING: jobe_api_mock.php\n";
echo "-------------------------------------------\n";

if (!function_exists('qtype_coderunner_get_languages')) {
    $issues[] = "Function qtype_coderunner_get_languages() not found";
    echo "✗ Function qtype_coderunner_get_languages NOT FOUND\n";
} else {
    echo "✓ Function qtype_coderunner_get_languages exists\n";
    $langs = qtype_coderunner_get_languages();
    echo "  Returns: " . json_encode($langs) . "\n";
}

if (!function_exists('qtype_coderunner_run_code')) {
    $issues[] = "Function qtype_coderunner_run_code() not found";
    echo "✗ Function qtype_coderunner_run_code NOT FOUND\n";
} else {
    echo "✓ Function qtype_coderunner_run_code exists\n";
    
    // Test what it returns
    $result = qtype_coderunner_run_code('python3', 'print("Hello")', '', 30);
    echo "  Returns array with keys: " . implode(', ', array_keys($result)) . "\n";
    echo "  stdout value: " . (isset($result['stdout']) ? "'" . $result['stdout'] . "'" : "NOT SET") . "\n";
    
    if (!isset($result['stdout'])) {
        $issues[] = "run_code() doesn't return 'stdout' field";
    }
    if ($result['stdout'] === '') {
        $issues[] = "run_code() returns empty stdout - tests will fail when checking for output";
    }
}

echo "\n";

// ==============================================
// ISSUE 2: Test execution simulation
// ==============================================
echo "[2] TESTING: Code execution simulation\n";
echo "-------------------------------------------\n";

$test_cases = [
    'print("Hello, World!")' => 'Hello, World!',
    'print("Hello")' => 'Hello',
    'print("test")' => 'test',
    'print("Workflow test successful")' => 'Workflow test successful',
];

foreach ($test_cases as $code => $expected) {
    $result = qtype_coderunner_run_code('python3', $code, '', 30);
    $output = $result['stdout'] ?? '';
    $found = (strpos($output, $expected) !== false);
    
    if ($found) {
        echo "✓ Code '$code' returns '$expected'\n";
    } else {
        echo "✗ Code '$code' returns '" . trim($output) . "' (expected '$expected')\n";
        $issues[] = "Code simulation failed for: $code";
    }
}

echo "\n";

// ==============================================
// ISSUE 3: Test database field names
// ==============================================
echo "[3] TESTING: Database field names\n";
echo "-------------------------------------------\n";

global $DB;

try {
    $attempts = $DB->get_records('quiz_attempts', [], '', '*', 0, 1);
    
    if (count($attempts) > 0) {
        $attempt = reset($attempts);
        $fields = get_object_vars($attempt);
        
        echo "Actual attempt fields: " . implode(', ', array_keys($fields)) . "\n";
        
        // Check which names are used
        if (isset($attempt->quiz)) {
            echo "✓ Field 'quiz' EXISTS (not 'quizid')\n";
        } elseif (isset($attempt->quizid)) {
            echo "✓ Field 'quizid' EXISTS\n";
        } else {
            echo "✗ Neither 'quiz' nor 'quizid' found\n";
            $issues[] = "Database field name mismatch for quiz ID in attempts";
        }
    } else {
        echo "⚠ No attempts in database - can't verify field names\n";
    }
} catch (Exception $e) {
    echo "✗ Error accessing attempts: " . $e->getMessage() . "\n";
    $issues[] = "Can't access quiz_attempts table";
}

echo "\n";

try {
    $slots = $DB->get_records('quiz_slots', [], '', '*', 0, 1);
    
    if (count($slots) > 0) {
        $slot = reset($slots);
        $fields = get_object_vars($slot);
        
        echo "Actual slot fields: " . implode(', ', array_keys($fields)) . "\n";
        
        if (isset($slot->question)) {
            echo "✓ Field 'question' EXISTS (not 'questionid')\n";
        } elseif (isset($slot->questionid)) {
            echo "✓ Field 'questionid' EXISTS\n";
        } else {
            echo "✗ Neither 'question' nor 'questionid' found\n";
            $issues[] = "Database field name mismatch for question ID in slots";
        }
    } else {
        echo "⚠ No slots in database - can't verify field names\n";
    }
} catch (Exception $e) {
    echo "✗ Error accessing slots: " . $e->getMessage() . "\n";
    $issues[] = "Can't access quiz_slots table";
}

echo "\n";

// ==============================================
// ISSUE 4: Test constants
// ==============================================
echo "[4] TESTING: Constants\n";
echo "-------------------------------------------\n";

$constants = ['PYODIDE_VERSION', 'PYODIDE_CDN_URL', 'PYODIDE_TIMEOUT', 'PYODIDE_MAX_OUTPUT'];

foreach ($constants as $const) {
    if (defined($const)) {
        echo "✓ Constant $const = " . constant($const) . "\n";
    } else {
        echo "✗ Constant $const NOT DEFINED\n";
        $issues[] = "Constant $const not defined";
    }
}

echo "\n";

// ==============================================
// SUMMARY
// ==============================================
echo "==============================================\n";
echo "ISSUE SUMMARY\n";
echo "==============================================\n\n";

if (empty($issues)) {
    echo "✓ NO ISSUES FOUND - All systems operational!\n";
} else {
    echo "Found " . count($issues) . " issue(s):\n\n";
    
    foreach ($issues as $i => $issue) {
        echo ($i+1) . ". " . $issue . "\n";
    }
}

echo "\n";
?>
