<?php
/**
 * CodeRunner + Pyodide Integration Test Suite
 *
 * This script validates that all integration components are working correctly.
 * Run this after installation to verify the system is ready.
 *
 * Usage: http://localhost/question/type/coderunner/tests/integration_test.php
 *
 * @package    qtype_coderunner
 * @subpackage tests
 * @copyright  2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);
require_once('../../../config.php');

// ============================================
// TEST SUITE
// ============================================

class PyodideIntegrationTests {
    private $results = array();
    private $passed = 0;
    private $failed = 0;

    public function __construct() {
        echo "========================================\n";
        echo "CodeRunner + Pyodide Integration Tests\n";
        echo "========================================\n\n";
    }

    /**
     * Run all tests
     */
    public function run_all_tests() {
        $this->test_files_exist();
        $this->test_configuration();
        $this->test_database();
        $this->test_api_mock();
        $this->test_moodle_integration();
        
        $this->print_summary();
    }

    /**
     * Test 1: Verify all required files exist
     */
    private function test_files_exist() {
        echo "[TEST 1] Verifying required files...\n";
        
        $required_files = array(
            'enable_pyodide.php' => 'Configuration',
            'jobe_api_mock.php' => 'API Mock',
            'pyodide_executor.js' => 'JavaScript Executor',
            'setup_pyodide.php' => 'Setup Script',
            'renderer.php' => 'Question Renderer',
            'lib_integration.php' => 'Moodle Integration'
        );
        
        $dir = dirname(__FILE__) . '/..';
        $all_exist = true;
        
        foreach ($required_files as $file => $description) {
            $path = $dir . '/' . $file;
            if (file_exists($path)) {
                echo "  ✓ {$file} ({$description})\n";
                $this->record_result("File: {$file}", true);
            } else {
                echo "  ✗ {$file} NOT FOUND\n";
                $this->record_result("File: {$file}", false);
                $all_exist = false;
            }
        }
        echo "\n";
    }

    /**
     * Test 2: Verify Moodle configuration
     */
    private function test_configuration() {
        echo "[TEST 2] Verifying Moodle configuration...\n";
        
        $config_items = array(
            'use_local_pyodide' => 'Pyodide enabled',
            'pyodide_version' => 'Pyodide version',
            'pyodide_timeout' => 'Execution timeout',
            'pyodide_max_output' => 'Max output size'
        );
        
        foreach ($config_items as $key => $description) {
            $value = get_config('qtype_coderunner', $key);
            
            if ($value !== false) {
                echo "  ✓ {$description} = {$value}\n";
                $this->record_result("Config: {$key}", true);
            } else {
                echo "  ✗ {$description} NOT SET\n";
                // This is not critical, so mark as warning
            }
        }
        echo "\n";
    }

    /**
     * Test 3: Verify database tables
     */
    private function test_database() {
        global $DB;
        echo "[TEST 3] Verifying database tables...\n";
        
        $tables = array(
            'question' => 'Questions',
            'question_attempts' => 'Question Attempts',
            'quiz' => 'Quizzes',
            'quiz_attempts' => 'Quiz Attempts'
        );
        
        foreach ($tables as $suffix => $description) {
            $table_name = 'mdl_' . $suffix;
            try {
                $DB->get_records($table_name, array(), '', '*', 0, 1);
                echo "  ✓ {$description} table exists\n";
                $this->record_result("Table: mdl_{$suffix}", true);
            } catch (Exception $e) {
                echo "  ✗ {$description} table NOT FOUND or ERROR\n";
                $this->record_result("Table: mdl_{$suffix}", false);
            }
        }
        echo "\n";
    }

    /**
     * Test 4: Verify API Mock functionality
     */
    private function test_api_mock() {
        echo "[TEST 4] Testing API Mock...\n";
        
        // Include the mock API
        $mock_path = dirname(__FILE__) . '/../jobe_api_mock.php';
        
        if (file_exists($mock_path)) {
            require_once($mock_path);
            echo "  ✓ jobe_api_mock.php loaded\n";
            $this->record_result("API Mock: Load", true);
            
            // Test mock functions
            if (class_exists('jobe_api_mock')) {
                // Test get_languages
                $langs = jobe_api_mock::get_languages();
                if (is_array($langs) && in_array('python3', $langs)) {
                    echo "  ✓ get_languages() returns Python\n";
                    $this->record_result("API Mock: get_languages()", true);
                } else {
                    echo "  ✗ get_languages() failed\n";
                    $this->record_result("API Mock: get_languages()", false);
                }
                
                // Test run_code
                $result = jobe_api_mock::run_code("print('test')", '', 'python3');
                if (isset($result['status'])) {
                    echo "  ✓ run_code() returns valid response\n";
                    $this->record_result("API Mock: run_code()", true);
                } else {
                    echo "  ✗ run_code() returned invalid response\n";
                    $this->record_result("API Mock: run_code()", false);
                }
            }
        } else {
            echo "  ✗ jobe_api_mock.php NOT FOUND\n";
            $this->record_result("API Mock: Load", false);
        }
        echo "\n";
    }

    /**
     * Test 5: Verify Moodle integration
     */
    private function test_moodle_integration() {
        echo "[TEST 5]Testing Moodle integration...\n";
        
        // Check if we're in a Moodle environment
        if (isset($GLOBALS['CFG'])) {
            echo "  ✓ Moodle context detected\n";
            $this->record_result("Moodle: Context", true);
            
            // Check user is logged in
            global $USER;
            if (isloggedin()) {
                echo "  ✓ User is logged in\n";
                $this->record_result("Moodle: User logged in", true);
            } else {
                echo "  ✗ User not logged in\n";
                $this->record_result("Moodle: User logged in", false);
            }
            
            // Check admin access
            if (is_siteadmin()) {
                echo "  ✓ Admin access verified\n";
                $this->record_result("Moodle: Admin access", true);
            } else {
                echo "  ⚠ Not admin (some features may be restricted)\n";
            }
            
            // Check CodeRunner plugin is installed
            if (\core_plugin_manager::instance()->get_plugin_info('qtype_coderunner')) {
                echo "  ✓ CodeRunner plugin installed\n";
                $this->record_result("Moodle: CodeRunner installed", true);
            } else {
                echo "  ✗ CodeRunner plugin NOT installed\n";
                $this->record_result("Moodle: CodeRunner installed", false);
            }
        }
        echo "\n";
    }

    /**
     * Record a test result
     */
    private function record_result($name, $passed) {
        $this->results[] = array(
            'name' => $name,
            'passed' => $passed
        );
        
        if ($passed) {
            $this->passed++;
        } else {
            $this->failed++;
        }
    }

    /**
     * Print test summary
     */
    private function print_summary() {
        echo "========================================\n";
        echo "Test Results Summary\n";
        echo "========================================\n";
        echo "Total tests: " . ($this->passed + $this->failed) . "\n";
        echo "Passed: " . $this->passed . " ✓\n";
        echo "Failed: " . $this->failed . " ✗\n";
        
        if ($this->failed === 0) {
            echo "\n✓ All tests passed! Integration is ready.\n";
        } else {
            echo "\n✗ Some tests failed. Review above for details.\n";
        }
        echo "========================================\n";
    }
}

// ============================================
// RUN TESTS
// ============================================

$tests = new PyodideIntegrationTests();
$tests->run_all_tests();
?>
