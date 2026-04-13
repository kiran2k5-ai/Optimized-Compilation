<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Initialize test cases storage (in real app, use database)
$testCasesFile = __DIR__ . '/test_cases.json';

// Ensure test cases file exists
if (!file_exists($testCasesFile)) {
    $defaultTestCases = [
        'python_1' => [
            'title' => 'Multiply Two Numbers',
            'language' => 'python',
            'tests' => [
                ['input' => '5\n10', 'expected' => '50'],
                ['input' => '3\n7', 'expected' => '21']
            ]
        ],
        'python_2' => [
            'title' => 'Sum Three Numbers',
            'language' => 'python',
            'tests' => [
                ['input' => '10\n20\n30', 'expected' => '60'],
                ['input' => '1\n2\n3', 'expected' => '6']
            ]
        ]
    ];
    file_put_contents($testCasesFile, json_encode($defaultTestCases, JSON_PRETTY_PRINT));
}

$method = $_SERVER['REQUEST_METHOD'];

// GET: Retrieve all test cases or specific test case
if ($method === 'GET') {
    $questionId = isset($_GET['question_id']) ? $_GET['question_id'] : null;
    
    $testCases = json_decode(file_get_contents($testCasesFile), true);
    
    if ($questionId) {
        if (isset($testCases[$questionId])) {
            echo json_encode(['status' => 'success', 'data' => $testCases[$questionId]]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Question not found']);
        }
    } else {
        echo json_encode(['status' => 'success', 'data' => $testCases]);
    }
    exit();
}

// POST: Add or update test cases
if ($method === 'POST') {
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    
    // Debug: Show what was received
    if (!isset($input['question_id']) || !isset($input['tests'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing question_id or tests', 'debug' => ['raw' => $rawInput, 'parsed' => $input]]);
        exit();
    }
    
    $questionId = $input['question_id'];
    $title = isset($input['title']) ? $input['title'] : 'Untitled';
    $language = isset($input['language']) ? $input['language'] : 'python';
    $tests = $input['tests'];
    
    // Read current test cases
    $testCases = json_decode(file_get_contents($testCasesFile), true);
    
    // Add or update
    $testCases[$questionId] = [
        'title' => $title,
        'language' => $language,
        'tests' => $tests
    ];
    
    // Save to file
    file_put_contents($testCasesFile, json_encode($testCases, JSON_PRETTY_PRINT));
    
    echo json_encode(['status' => 'success', 'message' => 'Test cases saved', 'data' => $testCases[$questionId]]);
    exit();
}

// DELETE: Remove test cases
if ($method === 'DELETE') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['question_id'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing question_id']);
        exit();
    }
    
    $questionId = $input['question_id'];
    $testCases = json_decode(file_get_contents($testCasesFile), true);
    
    if (isset($testCases[$questionId])) {
        unset($testCases[$questionId]);
        file_put_contents($testCasesFile, json_encode($testCases, JSON_PRETTY_PRINT));
        echo json_encode(['status' => 'success', 'message' => 'Test cases deleted']);
    } else {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Question not found']);
    }
    exit();
}

http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
?>