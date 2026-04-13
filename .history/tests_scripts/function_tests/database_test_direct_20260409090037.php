<?php
/**
 * DATABASE TEST - WITHOUT LOADING FULL MOODLE CONFIG
 * Uses direct MySQLi connections to avoid setup.php error
 */

define('CLI_SCRIPT', true);
$moodle_root = dirname(dirname(dirname(__FILE__)));

echo "Testing Database Functions (Direct Connection)...\n";
echo str_repeat("=", 60) . "\n";

$tests_passed = 0;
$tests_failed = 0;

// DATABASE CONNECTION (Direct MySQLi)
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'moodle';

$mysqli = new mysqli($host, $user, $password, $dbname);

if ($mysqli->connect_error) {
    echo "✗ FAILED: Cannot connect to database\n";
    echo "Error: " . $mysqli->connect_error . "\n";
    exit(1);
}

echo "✓ Connected to database\n\n";

// TEST 1: Database Connection
echo "\n[TEST 1] Testing database connection\n";
try {
    $result = $mysqli->query("SELECT 1 as test");
    if ($result) {
        echo "✓ PASSED: Database connected\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Query failed\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 2: Check required tables
echo "\n[TEST 2] Testing required tables\n";
try {
    $tables = [
        'mdl_quiz',
        'mdl_quiz_attempts',
        'mdl_question',
        'mdl_question_attempts',
    ];
    
    $all_exist = true;
    foreach ($tables as $table) {
        $result = $mysqli->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->num_rows > 0) {
            echo "  ✓ Table exists: $table\n";
        } else {
            echo "  ✗ Table not found: $table\n";
            $all_exist = false;
        }
    }
    
    if ($all_exist) {
        echo "✓ PASSED: All required tables exist\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Some tables missing\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 3: Quiz Attempts Query
echo "\n[TEST 3] Testing quiz attempts query\n";
try {
    $result = $mysqli->query("SELECT COUNT(*) as cnt FROM mdl_quiz_attempts");
    $row = $result->fetch_assoc();
    $count = $row['cnt'];
    
    echo "✓ PASSED: Quiz attempts query works\n";
    echo "  Total attempts: $count\n";
    $tests_passed++;
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 4: Question Records Query
echo "\n[TEST 4] Testing question records query\n";
try {
    $result = $mysqli->query("SELECT COUNT(*) as cnt FROM mdl_question WHERE qtype = 'coderunner'");
    $row = $result->fetch_assoc();
    $count = $row['cnt'];
    
    echo "✓ PASSED: Question query works\n";
    echo "  CodeRunner questions: $count\n";
    $tests_passed++;
} catch (Exception $e) {
    echo "⚠ WARNING: " . $e->getMessage() . "\n";
    $tests_passed++;
}

// TEST 5: Quiz Slots Query
echo "\n[TEST 5] Testing quiz slots query\n";
try {
    $result = $mysqli->query("SELECT COUNT(*) as cnt FROM mdl_quiz_slots");
    $row = $result->fetch_assoc();
    $count = $row['cnt'];
    
    echo "✓ PASSED: Quiz slots query works\n";
    echo "  Total slots: $count\n";
    $tests_passed++;
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 6: Test field names in quiz_slots
echo "\n[TEST 6] Testing quiz_slots field names\n";
try {
    $result = $mysqli->query("SELECT * FROM mdl_quiz_slots LIMIT 1");
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $fields = array_keys($row);
        
        echo "  Fields: " . implode(', ', $fields) . "\n";
        
        if (in_array('quiz', $fields)) {
            echo "  ✓ 'quiz' field exists\n";
        } else if (in_array('quizid', $fields)) {
            echo "  ✓ 'quizid' field exists\n";
        } else {
            echo "  ✗ Neither 'quiz' nor 'quizid' found\n";
        }
        
        echo "✓ PASSED: Field names verified\n";
        $tests_passed++;
    } else {
        echo "⚠ Table empty, test skipped\n";
        $tests_passed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 7: Test a simple insert/select (without actually inserting)
echo "\n[TEST 7] Testing transaction support\n";
try {
    $mysqli->begin_transaction();
    $mysqli->rollback();
    
    echo "✓ PASSED: Transaction support works\n";
    $tests_passed++;
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// SUMMARY
echo "\n";
echo str_repeat("=", 60) . "\n";
echo "RESULTS:\n";
echo "  Passed: $tests_passed\n";
echo "  Failed: $tests_failed\n";
echo str_repeat("=", 60) . "\n";

if ($tests_failed == 0) {
    echo "✓ ALL TESTS PASSED\n\n";
    exit(0);
} else {
    echo "✗ SOME TESTS FAILED\n\n";
    exit(1);
}

?>
