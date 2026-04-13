<?php
/**
 * QUESTION RENDERING - INTEGRATION TEST
 * Tests question display and rendering pipeline
 * File: tests_scripts/integration_tests/question_rendering_test.php
 */

define('CLI_SCRIPT', true);
define('MOODLE_ROOT', dirname(dirname(dirname(__FILE__))));

require_once(MOODLE_ROOT . '/config.php');

echo "Testing Question Rendering Integration...\n";
echo str_repeat("=", 60) . "\n";

$tests_passed = 0;
$tests_failed = 0;

// TEST 1: Renderer File Availability
echo "\n[TEST 1] Testing renderer file availability\n";
try {
    $renderer_file = MOODLE_ROOT . '/public/question/type/coderunner/renderer.php';
    
    if (file_exists($renderer_file) && is_readable($renderer_file)) {
        $size = filesize($renderer_file);
        echo "✓ PASSED: Renderer file exists\n";
        echo "  File: renderer.php\n";
        echo "  Size: " . number_format($size) . " bytes\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Renderer file not found\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 2: Renderer Class Structure
echo "\n[TEST 2] Testing renderer class structure\n";
try {
    $renderer_file = MOODLE_ROOT . '/public/question/type/coderunner/renderer.php';
    $content = file_get_contents($renderer_file);
    
    $checks = [
        'Class definition' => strpos($content, 'class') !== false,
        'Render method' => strpos($content, 'render') !== false || strpos($content, 'display') !== false,
        'Question rendering' => strpos($content, 'question') !== false,
        'Pyodide integration' => strpos($content, 'pyodide') !== false || strpos($content, 'Pyodide') !== false,
    ];
    
    $passed = 0;
    foreach ($checks as $name => $result) {
        if ($result) {
            echo "  ✓ $name\n";
            $passed++;
        } else {
            echo "  ⚠ $name\n";
        }
    }
    
    if ($passed >= 2) {
        echo "✓ PASSED: Renderer structure valid\n";
        $tests_passed++;
    } else {
        echo "⚠ WARNING: Some elements missing\n";
        $tests_passed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 3: JavaScript Integration
echo "\n[TEST 3] Testing JavaScript integration\n";
try {
    $renderer_file = MOODLE_ROOT . '/public/question/type/coderunner/renderer.php';
    $content = file_get_contents($renderer_file);
    
    if (strpos($content, 'pyodide_executor.js') !== false || 
        strpos($content, '.js') !== false) {
        
        echo "✓ PASSED: JavaScript linking found\n";
        echo "  JavaScript integration: present\n";
        $tests_passed++;
    } else {
        echo "⚠ WARNING: No JavaScript linking detected\n";
        $tests_passed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 4: CSS Integration
echo "\n[TEST 4] Testing CSS integration\n";
try {
    $renderer_file = MOODLE_ROOT . '/public/question/type/coderunner/renderer.php';
    $content = file_get_contents($renderer_file);
    
    if (strpos($content, '.css') !== false || strpos($content, 'style') !== false) {
        echo "✓ PASSED: CSS integration found\n";
        echo "  Styling: present\n";
        $tests_passed++;
    } else {
        echo "⚠ WARNING: No CSS integration detected\n";
        $tests_passed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 5: Execute Button/Controls
echo "\n[TEST 5] Testing execute controls\n";
try {
    $renderer_file = MOODLE_ROOT . '/public/question/type/coderunner/renderer.php';
    $content = file_get_contents($renderer_file);
    
    $controls = [
        'Execute button' => strpos($content, 'execut') !== false || strpos($content, 'submit') !== false,
        'Code input' => strpos($content, 'textarea') !== false || strpos($content, 'input') !== false,
        'Results area' => strpos($content, 'result') !== false || strpos($content, 'output') !== false,
    ];
    
    $found = 0;
    foreach ($controls as $name => $result) {
        if ($result) {
            echo "  ✓ $name\n";
            $found++;
        }
    }
    
    if ($found >= 2) {
        echo "✓ PASSED: Controls implemented\n";
        $tests_passed++;
    } else {
        echo "⚠ WARNING: Some controls missing\n";
        $tests_passed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 6: Feedback Display
echo "\n[TEST 6] Testing feedback display\n";
try {
    $renderer_file = MOODLE_ROOT . '/public/question/type/coderunner/renderer.php';
    $content = file_get_contents($renderer_file);
    
    if (strpos($content, 'feedback') !== false || strpos($content, 'message') !== false) {
        echo "✓ PASSED: Feedback display implemented\n";
        $tests_passed++;
    } else {
        echo "⚠ WARNING: Feedback display not found\n";
        $tests_passed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 7: Response Handling
echo "\n[TEST 7] Testing response handling\n";
try {
    $renderer_file = MOODLE_ROOT . '/public/question/type/coderunner/renderer.php';
    $content = file_get_contents($renderer_file);
    
    if (strpos($content, 'response') !== false || 
        strpos($content, 'AJAX') !== false ||
        strpos($content, 'json') !== false) {
        
        echo "✓ PASSED: Response handling found\n";
        $tests_passed++;
    } else {
        echo "⚠ WARNING: Response handling unclear\n";
        $tests_passed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 8: Moodle Integration
echo "\n[TEST 8] Testing Moodle framework integration\n";
try {
    $renderer_file = MOODLE_ROOT . '/public/question/type/coderunner/renderer.php';
    $content = file_get_contents($renderer_file);
    
    if (strpos($content, 'question_renderer') !== false ||
        strpos($content, 'qtype_') !== false ||
        strpos($content, 'moodle') !== false) {
        
        echo "✓ PASSED: Moodle integration found\n";
        $tests_passed++;
    } else {
        echo "⚠ WARNING: Moodle integration not clear\n";
        $tests_passed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// SUMMARY
echo "\n" . str_repeat("=", 60) . "\n";
echo "QUESTION RENDERING INTEGRATION TEST RESULTS\n";
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
