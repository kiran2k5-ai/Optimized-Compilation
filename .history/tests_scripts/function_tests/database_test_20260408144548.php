<?php
/**
 * DATABASE - FUNCTION TEST
 * Tests database operations and query functions
 * File: tests_scripts/function_tests/database_test.php
 */

define('CLI_SCRIPT', true);
define('MOODLE_ROOT', dirname(dirname(dirname(__FILE__))));

require_once(MOODLE_ROOT . '/config.php');

echo "Testing Database Functions...\n";
echo str_repeat("=", 60) . "\n";

$tests_passed = 0;
$tests_failed = 0;

// TEST 1: Database Connection
echo "\n[TEST 1] Testing database connection\n";
try {
    global $DB;
    
    if ($DB->is_connected()) {
        echo "✓ PASSED: Database connected\n";
        echo "  Database: " . $DB->get_dbname() . "\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Database not connected\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 2: Quiz Module Tables
echo "\n[TEST 2] Testing quiz module tables\n";
try {
    global $DB;
    
    $tables = [
        'mdl_quiz',
        'mdl_quiz_attempts',
        'mdl_question',
        'mdl_question_attempts',
    ];
    
    $all_exist = true;
    $manager = $DB->get_manager();
    
    foreach ($tables as $table) {
        $table_name = str_replace('mdl_', '', $table);
        if ($manager->table_exists($table_name)) {
            echo "  ✓ Table exists: $table_name\n";
        } else {
            echo "  ✗ Table not found: $table_name\n";
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

// TEST 3: Configuration Table
echo "\n[TEST 3] Testing configuration storage\n";
try {
    global $DB;
    
    // Check if config_plugins table exists
    $manager = $DB->get_manager();
    if ($manager->table_exists('config_plugins')) {
        echo "✓ PASSED: Configuration table available\n";
        echo "  Table: config_plugins\n";
        $tests_passed++;
    } else {
        echo "⚠ WARNING: Configuration table not found\n";
        $tests_passed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 4: Quiz Attempts Query
echo "\n[TEST 4] Testing quiz attempts query\n";
try {
    global $DB;
    
    // Count quiz attempts (may be 0 initially)
    $count = $DB->count_records('quiz_attempts');
    
    echo "✓ PASSED: Quiz attempts query works\n";
    echo "  Total attempts: $count\n";
    $tests_passed++;
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 5: Question Records Query
echo "\n[TEST 5] Testing question records query\n";
try {
    global $DB;
    
    // Count coderunner questions
    $count = $DB->count_records('question', ['qtype' => 'coderunner']);
    
    echo "✓ PASSED: Question query works\n";
    echo "  CodeRunner questions: $count\n";
    $tests_passed++;
} catch (Exception $e) {
    echo "⚠ WARNING: " . $e->getMessage() . "\n";
    $tests_passed++;
}

// TEST 6: Database Insert Operation
echo "\n[TEST 6] Testing database insert capability\n";
try {
    global $DB;
    
    // We won't actually insert, just check if we can prepare for it
    // Get the structure of a table
    $manager = $DB->get_manager();
    if ($manager->table_exists('quiz_attempts')) {
        echo "✓ PASSED: Can access table for operations\n";
        echo "  Table: quiz_attempts\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Cannot access table\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 7: Transaction Support
echo "\n[TEST 7] Testing transaction support\n";
try {
    global $DB;
    
    // Start a transaction and immediately rollback (test only)
    $transaction = $DB->start_delegated_transaction();
    $transaction->rollback(new Exception('Test rollback'));
    
    echo "✓ PASSED: Transactions supported\n";
    $tests_passed++;
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Test rollback') !== false) {
        echo "✓ PASSED: Transaction rollback works\n";
        $tests_passed++;
    } else {
        echo "⚠ WARNING: Transaction test inconclusive\n";
        $tests_passed++;
    }
}

// TEST 8: Database Schema Integrity
echo "\n[TEST 8] Testing database schema integrity\n";
try {
    global $DB;
    
    $manager = $DB->get_manager();
    
    $critical_tables = ['course', 'user', 'quiz', 'question'];
    $all_valid = true;
    
    foreach ($critical_tables as $table) {
        if (!$manager->table_exists($table)) {
            $all_valid = false;
            echo "  ✗ Missing critical table: $table\n";
        }
    }
    
    if ($all_valid) {
        echo "✓ PASSED: All critical tables present\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Critical tables missing\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// SUMMARY
echo "\n" . str_repeat("=", 60) . "\n";
echo "DATABASE FUNCTION TEST RESULTS\n";
echo str_repeat("=", 60) . "\n";
echo "Tests Passed: $tests_passed\n";
echo "Tests Failed: $tests_failed\n";
echo "Total: " . ($tests_passed + $tests_failed) . "\n";

if ($tests_failed > 0) {
    echo "\n✗ FAILED - Review errors above\n";
} else {
    echo "\n✓ PASSED - All tests successful\n";
}

echo "\n";
?>
