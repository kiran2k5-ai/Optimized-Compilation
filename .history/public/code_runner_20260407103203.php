<?php
header('Content-Type: text/html; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Initialize test cases storage
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

// Check if this is an API request
$isApiRequest = isset($_GET['api']) || $_SERVER['REQUEST_METHOD'] !== 'GET' || isset($_GET['question_id']);

if ($isApiRequest) {
    // ===== API MODE =====
    header('Content-Type: application/json');
    
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
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['question_id']) || !isset($input['tests'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Missing question_id or tests']);
            exit();
        }
        
        $questionId = $input['question_id'];
        $title = isset($input['title']) ? $input['title'] : 'Untitled';
        $language = isset($input['language']) ? $input['language'] : 'python';
        $tests = $input['tests'];
        
        // Read current test cases
        $testCases = json_decode(file_get_contents($testCasesFile), true);
        
        // Add or update the test case
        $testCases[$questionId] = [
            'title' => $title,
            'language' => $language,
            'tests' => $tests
        ];
        
        // Write back to file
        file_put_contents($testCasesFile, json_encode($testCases, JSON_PRETTY_PRINT));
        
        echo json_encode(['status' => 'success', 'message' => 'Test cases saved', 'data' => $testCases[$questionId]]);
        exit();
    }
    
    // DELETE: Remove test case
    if ($method === 'DELETE') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['question_id'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Missing question_id']);
            exit();
        }
        
        $testCases = json_decode(file_get_contents($testCasesFile), true);
        $questionId = $input['question_id'];
        
        if (isset($testCases[$questionId])) {
            unset($testCases[$questionId]);
            file_put_contents($testCasesFile, json_encode($testCases, JSON_PRETTY_PRINT));
            echo json_encode(['status' => 'success', 'message' => 'Test case deleted']);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Test case not found']);
        }
        exit();
    }
}

// ===== HTML FRONTEND MODE =====
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔐 Secure Code Runner - Python & C</title>
    <script async src="https://cdn.jsdelivr.net/pyodide/v0.23.0/full/pyodide.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: white;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #e8f4f8;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: transparent;
            color: #333;
            padding: 20px;
            text-align: left;
            display: none;
        }

        .header h1 {
            font-size: 1.5em;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 0.9em;
            opacity: 0.8;
        }

        .content {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
            padding: 20px;
            background: #d4e8f0;
        }

        .editor-section {
            background: transparent;
            padding: 0;
            border-radius: 0;
            color: #333;
            margin-bottom: 15px;
        }

        .editor-section h3 {
            color: #333;
            margin-bottom: 8px;
            font-size: 0.95em;
            font-weight: normal;
        }

        textarea {
            width: 100%;
            height: 300px;
            background: white;
            color: #333;
            border: 1px solid #999;
            border-radius: 3px;
            padding: 10px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            resize: vertical;
        }

        .test-case-section {
            background: transparent;
            padding: 0;
            border-radius: 0;
            margin-bottom: 15px;
        }

        .test-case-section h4 {
            color: #333;
            margin-bottom: 10px;
            font-size: 0.95em;
        }

        .test-case-input {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }

        .test-case-input input,
        .test-case-input textarea {
            flex: 1;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 13px;
            font-family: 'Courier New', monospace;
            height: 40px;
        }

        .test-case-input textarea {
            height: 60px;
        }

        .btn-run {
            background: #2196F3;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 0.95em;
            font-weight: bold;
            width: auto;
            transition: background 0.3s;
            margin-bottom: 10px;
            margin-right: 10px;
        }

        .btn-run:hover {
            background: #0b7dda;
        }

        .btn-add-test {
            background: #2196F3;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 3px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
            font-size: 0.95em;
        }

        .btn-add-test:hover {
            background: #0b7dda;
        }

        .output-panel {
            background: white;
            padding: 15px;
            border-radius: 3px;
            border: 1px solid #999;
        }

        .output-panel h3 {
            color: #333;
            margin-bottom: 10px;
            font-size: 0.95em;
        }

        .output-box {
            background: white;
            color: #333;
            padding: 12px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            min-height: 100px;
            max-height: 250px;
            overflow-y: auto;
            border: 1px solid #ddd;
            line-height: 1.4;
            font-size: 0.9em;
        }

        .loading {
            display: none;
            text-align: center;
            color: #667eea;
            padding: 10px;
            font-weight: bold;
        }

        .error {
            color: #ff4444;
        }

        .success {
            color: #44ff44;
        }

        .test-result {
            background: #f5f5f5;
            padding: 8px;
            border-radius: 3px;
            margin: 8px 0;
            border-left: 3px solid #999;
            font-size: 0.9em;
        }

        .test-result.passed {
            border-left-color: #4caf50;
            background: #e8f5e9;
        }

        .test-result.failed {
            border-left-color: #f44336;
            background: #ffebee;
        }

        @media (max-width: 768px) {
            .content {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Secure Code Runner</h1>
            <p>Python Code Execution in Browser Sandbox</p>
        </div>

        <div class="content">
            <div>
                <div class="editor-section">
                    <h3>📝 Code Editor</h3>
                    <textarea id="code-editor" placeholder="Write your code here..."></textarea>
                </div>

                <div class="test-case-section">
                    <h4>🧪 Test Cases (From Server)</h4>
                    
                    <div style="margin-bottom: 10px;">
                        <h5 style="color: #666; margin-bottom: 5px;">📥 Available Test Cases:</h5>
                        <div id="server-tests" style="max-height: 150px; overflow-y: auto; background: white; padding: 8px; border-radius: 5px;"></div>
                    </div>

                    <div style="margin-bottom: 10px;">
                        <h5 style="color: #666; margin-bottom: 5px;">➕ Add Custom Test Case:</h5>
                        <div class="test-case-input">
                            <textarea id="custom-input" placeholder="Input values (one per line)"></textarea>
                            <textarea id="custom-expected" placeholder="Expected output"></textarea>
                        </div>
                        <button class="btn-add-test" onclick="addCustomTest()">+ Add Test</button>
                    </div>

                    <button class="btn-run" onclick="runCode()">▶️ Run All Tests</button>
                    <button class="btn-add-test" onclick="clearAllTests()" style="background: #f44336; margin-left: 5px;">🗑️ Clear All</button>
                </div>

                <div class="output-panel">
                    <h3>📊 Output & Results</h3>
                    <div class="loading" id="loading">⏳ Running code...</div>
                    <div class="output-box" id="output-box">Ready to run code...</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let pyodide = null;
        let currentTestCases = [];
        const API_URL = window.location.pathname; // Use current PHP file as API

        // Initialize Pyodide
        async function initPyodide() {
            if (pyodide === null) {
                let output = document.getElementById('output-box');
                output.innerHTML = '<span class="success">⏳ Loading Python runtime...</span>';
                pyodide = await loadPyodide();
                output.innerHTML = '<span class="success">✅ Ready!</span>';
            }
        }

        // Fetch all test cases from server
        async function loadAllTestCases() {
            try {
                const response = await fetch(API_URL);
                const result = await response.json();
                
                if (result.status === 'success') {
                    currentTestCases = [];
                    let html = '';
                    
                    Object.keys(result.data).forEach(key => {
                        const testCase = result.data[key];
                        html += `<div style="padding: 8px; margin: 5px 0; background: #e3f2fd; border-radius: 4px; cursor: pointer;" onclick="selectTestCase('${key}')">
                            <strong>${testCase.title}</strong> (${testCase.tests.length} tests)
                        </div>`;
                    });
                    
                    document.getElementById('server-tests').innerHTML = html || '<span class="error">No test cases available</span>';
                    document.getElementById('output-box').innerHTML = '<span class="success">✅ Test cases loaded</span>';
                } else {
                    document.getElementById('output-box').innerHTML = '<span class="error">❌ Failed to load test cases</span>';
                }
            } catch (error) {
                document.getElementById('output-box').innerHTML = '<span class="error">❌ Error: ' + error.message + '</span>';
            }
        }

        // Select a test case
        async function selectTestCase(questionId) {
            try {
                const response = await fetch(API_URL + '?question_id=' + questionId);
                const result = await response.json();
                
                if (result.status === 'success') {
                    currentTestCases = result.data.tests;
                    document.getElementById('output-box').innerHTML = '<span class="success">✅ ' + result.data.title + ' selected (' + currentTestCases.length + ' test cases)</span>';
                }
            } catch (error) {
                document.getElementById('output-box').innerHTML = '<span class="error">❌ Error: ' + error.message + '</span>';
            }
        }

        function addCustomTest() {
            const input = document.getElementById('custom-input').value;
            const expected = document.getElementById('custom-expected').value;
            
            if (input && expected) {
                currentTestCases.push({ input, expected });
                document.getElementById('custom-input').value = '';
                document.getElementById('custom-expected').value = '';
                alert('✅ Custom test added! Total: ' + currentTestCases.length + ' tests');
            } else {
                alert('❌ Please fill both input and expected output');
            }
        }

        function clearAllTests() {
            currentTestCases = [];
            document.getElementById('output-box').innerHTML = '<span class="success">✅ All tests cleared</span>';
        }

        async function runCode() {
            const code = document.getElementById('code-editor').value;
            const output = document.getElementById('output-box');
            const loading = document.getElementById('loading');
            
            if (!code.trim()) {
                output.innerHTML = '<span class="error">❌ Please write some code first!</span>';
                return;
            }

            if (currentTestCases.length === 0) {
                output.innerHTML = '<span class="error">❌ Please select a test case or add custom tests!</span>';
                return;
            }

            await initPyodide();
            loading.style.display = 'block';
            output.innerHTML = '';

            let results = '';
            let passed = 0;

            for (let i = 0; i < currentTestCases.length; i++) {
                const test = currentTestCases[i];
                try {
                    const wrappedCode = `
import sys
from io import StringIO
sys.stdin = StringIO(${JSON.stringify(test.input)})
sys.stdout = StringIO()
${code}
output = sys.stdout.getvalue()
`;
                    pyodide.runPython(wrappedCode);
                    const stdout = pyodide.globals.get('output');

                    const expectedOutput = test.expected.trim();
                    const actualOutput = stdout.trim();
                    const isCorrect = actualOutput === expectedOutput;

                    if (isCorrect) {
                        passed++;
                        results += `
                            <div class="test-result passed">
                                <strong>✅ Test ${i + 1}: PASSED</strong><br>
                                Output: "${actualOutput}"
                            </div>
                        `;
                    } else {
                        results += `
                            <div class="test-result failed">
                                <strong>❌ Test ${i + 1}: FAILED</strong><br>
                                Expected: "${expectedOutput}"<br>
                                Got: "${actualOutput}"
                            </div>
                        `;
                    }
                } catch (error) {
                    results += `
                        <div class="test-result failed">
                            <strong>❌ Test ${i + 1}: ERROR</strong><br>
                            ${error.message}
                        </div>
                    `;
                }
            }

            loading.style.display = 'none';
            output.innerHTML = `
                <div style="margin-bottom: 15px; padding: 10px; background: #e3f2fd; border-radius: 5px;">
                    <strong>Score: ${passed}/${currentTestCases.length} tests passed (${Math.round(passed/currentTestCases.length*100)}%)</strong>
                </div>
                ${results}
            `;
        }

        // Initialize on page load
        window.onload = function() {
            initPyodide();
            loadAllTestCases();
        };
    </script>
</body>
</html>
