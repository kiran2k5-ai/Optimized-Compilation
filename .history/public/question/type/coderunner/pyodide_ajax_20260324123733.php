<?php
/**
 * AJAX endpoint for Pyodide sandbox
 * Returns test cases and executes code for a CodeRunner question
 */

require_once(__DIR__ . '/../../../../config.php');
require_once($CFG->libdir . '/questionlib.php');

$action = required_param('action', PARAM_ALPHA);
$questionid = required_param('qid', PARAM_INT);
$code = optional_param('code', '', PARAM_RAW);

header('Content-Type: application/json');

try {
    // Check if question exists
    $question = question_bank::load_question($questionid);
    
    if (!$question || $question->qtype->name() !== 'coderunner') {
        throw new Exception('Invalid question');
    }
    
    if ($action === 'gettestcases') {
        // Return all test cases (visible + hidden)
        $testcases = $question->testcases;
        $result = [];
        
        foreach ($testcases as $testcase) {
            $result[] = [
                'id' => $testcase->id,
                'display' => $testcase->display,
                'input' => $testcase->input,
                'expected_output' => $testcase->expected_output,
                'extra' => $testcase->extra,
                'stdin' => $testcase->stdin
            ];
        }
        
        echo json_encode([
            'success' => true,
            'testcases' => $result
        ]);
        
    } else if ($action === 'checkcode') {
        // Execute code and return test results
        if (empty($code)) {
            throw new Exception('No code provided');
        }
        
        // This will be handled by Pyodide on client side
        // This is just a placeholder for server-side verification later
        echo json_encode([
            'success' => true,
            'message' => 'Ready to execute'
        ]);
        
    } else {
        throw new Exception('Unknown action: ' . $action);
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
