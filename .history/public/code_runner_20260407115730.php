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

// Initialize questions storage
$questionsFile = __DIR__ . '/questions.json';

// Ensure questions file exists
if (!file_exists($questionsFile)) {
    $defaultQuestions = [
        'q1' => [
            'title' => 'Multiply Two Numbers',
            'description' => 'Write a program that takes two numbers as input and prints their product.',
            'language' => 'python',
            'tests' => [
                ['input' => '5\n10', 'expected' => '50'],
                ['input' => '3\n7', 'expected' => '21']
            ]
        ],
        'q2' => [
            'title' => 'Sum Three Numbers',
            'description' => 'Write a program that takes three numbers as input and prints their sum.',
            'language' => 'python',
            'tests' => [
                ['input' => '10\n20\n30', 'expected' => '60'],
                ['input' => '1\n2\n3', 'expected' => '6']
            ]
        ]
    ];
    file_put_contents($questionsFile, json_encode($defaultQuestions, JSON_PRETTY_PRINT));
}

// Check if this is an API request
$isApiRequest = isset($_GET['api']) || $_SERVER['REQUEST_METHOD'] !== 'GET' || isset($_GET['question_id']);

if ($isApiRequest) {
    // ===== API MODE =====
    header('Content-Type: application/json');
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    // GET: Retrieve all questions or specific question
    if ($method === 'GET') {
        $questionId = isset($_GET['question_id']) ? $_GET['question_id'] : null;
        $questions = json_decode(file_get_contents($questionsFile), true);
        
        if ($questionId) {
            if (isset($questions[$questionId])) {
                echo json_encode(['status' => 'success', 'data' => $questions[$questionId]]);
            } else {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Question not found']);
            }
        } else {
            echo json_encode(['status' => 'success', 'data' => $questions]);
        }
        exit();
    }
    
    // POST: Add or update question(s)
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Read current questions
        $questions = json_decode(file_get_contents($questionsFile), true);
        $savedCount = 0;
        
        // Check if input contains array of questions (multiple questions)
        if (isset($input['questions']) && is_array($input['questions'])) {
            // Process multiple questions
            foreach ($input['questions'] as $questionData) {
                if (!isset($questionData['question_id']) || !isset($questionData['tests'])) {
                    continue;
                }
                
                $questionId = $questionData['question_id'];
                $title = isset($questionData['title']) ? $questionData['title'] : 'Untitled';
                $description = isset($questionData['description']) ? $questionData['description'] : '';
                $language = isset($questionData['language']) ? $questionData['language'] : 'python';
                $tests = $questionData['tests'];
                
                // Add or update the question
                $questions[$questionId] = [
                    'title' => $title,
                    'description' => $description,
                    'language' => $language,
                    'tests' => $tests
                ];
                $savedCount++;
            }
            
            if ($savedCount === 0) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'No valid questions to save']);
                exit();
            }
            
            // Write back to file
            file_put_contents($questionsFile, json_encode($questions, JSON_PRETTY_PRINT));
            
            echo json_encode([
                'status' => 'success', 
                'message' => $savedCount . ' question(s) saved successfully',
                'count' => $savedCount,
                'data' => $questions
            ]);
            exit();
        } 
        // Single question format (backward compatibility)
        else if (isset($input['question_id']) && isset($input['tests'])) {
            $questionId = $input['question_id'];
            $title = isset($input['title']) ? $input['title'] : 'Untitled';
            $description = isset($input['description']) ? $input['description'] : '';
            $language = isset($input['language']) ? $input['language'] : 'python';
            $tests = $input['tests'];
            
            // Add or update the question
            $questions[$questionId] = [
                'title' => $title,
                'description' => $description,
                'language' => $language,
                'tests' => $tests
            ];
            
            // Write back to file
            file_put_contents($questionsFile, json_encode($questions, JSON_PRETTY_PRINT));
            
            echo json_encode(['status' => 'success', 'message' => 'Question saved', 'data' => $questions[$questionId]]);
            exit();
        }
        // Invalid request
        else {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid format. Send either single question with question_id or array of questions']);
            exit();
        }
    }
    
    // DELETE: Remove question
    if ($method === 'DELETE') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['question_id'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Missing question_id']);
            exit();
        }
        
        $questions = json_decode(file_get_contents($questionsFile), true);
        $questionId = $input['question_id'];
        
        if (isset($questions[$questionId])) {
            unset($questions[$questionId]);
            file_put_contents($questionsFile, json_encode($questions, JSON_PRETTY_PRINT));
            echo json_encode(['status' => 'success', 'message' => 'Question deleted']);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Question not found']);
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
    <title>Code Runner</title>
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
            max-width: 1000px;
            margin: 0 auto;
            background: #d4e8f0;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .top-bar {
            background: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ccc;
        }

        .top-bar h2 {
            color: #333;
            font-size: 1.2em;
            margin: 0;
        }

        .question-counter {
            background: #2196F3;
            color: white;
            padding: 8px 15px;
            border-radius: 3px;
            font-size: 0.9em;
            font-weight: bold;
        }

        .navigation {
            display: flex;
            gap: 10px;
        }

        .nav-btn {
            background: #2196F3;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 3px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
        }

        .nav-btn:hover:not(:disabled) {
            background: #0b7dda;
        }

        .nav-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .content {
            padding: 20px;
        }

        .question-panel {
            background: white;
            padding: 20px;
            border-radius: 3px;
            margin-bottom: 20px;
            border: 1px solid #999;
        }

        .question-panel h3 {
            color: #333;
            font-size: 1.1em;
            margin-bottom: 10px;
        }

        .question-panel p {
            color: #666;
            line-height: 1.6;
            font-size: 0.95em;
        }

        .editor-section h3 {
            color: #333;
            margin-bottom: 8px;
            font-size: 0.95em;
        }

        textarea {
            width: 100%;
            height: 250px;
            background: white;
            color: #333;
            border: 1px solid #999;
            border-radius: 3px;
            padding: 10px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            resize: vertical;
            margin-bottom: 15px;
        }

        .test-case-section h4 {
            color: #333;
            margin-bottom: 10px;
            font-size: 0.95em;
        }

        .test-display {
            background: white;
            border: 1px solid #ddd;
            border-radius: 3px;
            padding: 10px;
            margin-bottom: 10px;
            font-size: 0.9em;
        }

        .test-display strong {
            color: #333;
        }

        .test-display code {
            background: #f5f5f5;
            padding: 2px 5px;
            border-radius: 2px;
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
            transition: background 0.3s;
            margin-bottom: 20px;
        }

        .btn-run:hover {
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
            color: #2196F3;
            padding: 10px;
            font-weight: bold;
        }

        .error {
            color: #f44336;
        }

        .success {
            color: #4caf50;
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

        .test-result code {
            background: #f5f5f5;
            padding: 2px 5px;
            border-radius: 2px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- TOP BAR WITH NAVIGATION -->
        <div class="top-bar">
            <h2 id="question-title">Loading...</h2>
            <div style="display: flex; gap: 15px; align-items: center;">
                <div class="question-counter">
                    Question <span id="question-counter">1</span> of <span id="total-questions">1</span>
                </div>
                <div class="navigation">
                    <button class="nav-btn" onclick="previousQuestion()" id="prev-btn">← Back</button>
                    <button class="nav-btn" onclick="nextQuestion()" id="next-btn">Next →</button>
                </div>
            </div>
        </div>

        <div class="content">
            <!-- QUESTION DESCRIPTION -->
            <div class="question-panel">
                <h3>Problem Statement</h3>
                <p id="question-description">Loading question...</p>
            </div>

            <!-- CODE EDITOR -->
            <div class="editor-section">
                <h3>📝 Code Editor</h3>
                <textarea id="code-editor" placeholder="Write your code here..."></textarea>
            </div>

            <!-- TEST CASES -->
            <div class="test-case-section">
                <h4>🧪 Test Cases</h4>
                <div id="test-cases-container"></div>
                <button class="btn-run" onclick="runCode()">▶️ Run All Tests</button>
            </div>

            <!-- OUTPUT -->
            <div class="output-panel">
                <h3>📊 Output & Results</h3>
                <div class="loading" id="loading">⏳ Running code...</div>
                <div class="output-box" id="output-box">Ready to run code...</div>
            </div>
        </div>
    </div>

    <script>
        let pyodide = null;
        let allQuestions = [];
        let currentQuestionIndex = 0;
        let currentQuestion = null;
        const API_URL = window.location.pathname;

        // Initialize Pyodide
        async function initPyodide() {
            if (pyodide === null) {
                let output = document.getElementById('output-box');
                output.innerHTML = '<span class="success">⏳ Loading Python runtime...</span>';
                pyodide = await loadPyodide();
                output.innerHTML = '<span class="success">✅ Ready!</span>';
            }
        }

        // Fetch all questions from server
        async function loadAllQuestions() {
            try {
                const response = await fetch(API_URL);
                const result = await response.json();
                
                if (result.status === 'success') {
                    allQuestions = Object.entries(result.data).map(([id, data]) => ({
                        id: id,
                        ...data
                    }));
                    
                    if (allQuestions.length > 0) {
                        currentQuestionIndex = 0;
                        document.getElementById('total-questions').textContent = allQuestions.length;
                        displayQuestion();
                        updateNavigation();
                    } else {
                        document.getElementById('output-box').innerHTML = '<span class="error">❌ No questions available</span>';
                    }
                } else {
                    document.getElementById('output-box').innerHTML = '<span class="error">❌ Failed to load questions</span>';
                }
            } catch (error) {
                document.getElementById('output-box').innerHTML = '<span class="error">❌ Error: ' + error.message + '</span>';
            }
        }

        // Display current question
        function displayQuestion() {
            currentQuestion = allQuestions[currentQuestionIndex];
            
            // Update title and counter
            document.getElementById('question-title').textContent = currentQuestion.title;
            document.getElementById('question-counter').textContent = currentQuestionIndex + 1;
            
            // Update description
            document.getElementById('question-description').textContent = currentQuestion.description || 'No description provided';
            
            // Clear code editor
            document.getElementById('code-editor').value = '';
            
            // Display test cases
            displayTestCases();
            
            // Clear output
            document.getElementById('output-box').innerHTML = '<span class="success">✅ Question loaded. Write code and click "Run All Tests"</span>';
        }

        // Display test cases for current question
        function displayTestCases() {
            const container = document.getElementById('test-cases-container');
            container.innerHTML = '';
            
            currentQuestion.tests.forEach((test, index) => {
                const div = document.createElement('div');
                div.className = 'test-display';
                div.innerHTML = `
                    <strong>Test Case ${index + 1}:</strong><br>
                    Input: <code>${test.input.replace(/\n/g, '\\n')}</code><br>
                    Expected Output: <code>${test.expected}</code>
                `;
                container.appendChild(div);
            });
        }

        // Navigate to next question
        function nextQuestion() {
            if (currentQuestionIndex < allQuestions.length - 1) {
                currentQuestionIndex++;
                displayQuestion();
                updateNavigation();
            }
        }

        // Navigate to previous question
        function previousQuestion() {
            if (currentQuestionIndex > 0) {
                currentQuestionIndex--;
                displayQuestion();
                updateNavigation();
            }
        }

        // Update navigation button states
        function updateNavigation() {
            document.getElementById('prev-btn').disabled = currentQuestionIndex === 0;
            document.getElementById('next-btn').disabled = currentQuestionIndex === allQuestions.length - 1;
        }

        // Run code against all test cases
        async function runCode() {
            const code = document.getElementById('code-editor').value;
            const output = document.getElementById('output-box');
            const loading = document.getElementById('loading');
            
            if (!code.trim()) {
                output.innerHTML = '<span class="error">❌ Please write some code first!</span>';
                return;
            }

            if (!currentQuestion || currentQuestion.tests.length === 0) {
                output.innerHTML = '<span class="error">❌ No test cases available!</span>';
                return;
            }

            await initPyodide();
            loading.style.display = 'block';
            output.innerHTML = '';

            let results = '';
            let passed = 0;
            const totalTests = currentQuestion.tests.length;

            for (let i = 0; i < totalTests; i++) {
                const test = currentQuestion.tests[i];
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
                                <strong>✅ Test ${i + 1}: PASSED</strong>
                            </div>
                        `;
                    } else {
                        results += `
                            <div class="test-result failed">
                                <strong>❌ Test ${i + 1}: FAILED</strong><br>
                                Expected: <code>${expectedOutput}</code><br>
                                Got: <code>${actualOutput}</code>
                            </div>
                        `;
                    }
                } catch (error) {
                    results += `
                        <div class="test-result failed">
                            <strong>❌ Test ${i + 1}: ERROR</strong><br>
                            <code>${error.message}</code>
                        </div>
                    `;
                }
            }

            loading.style.display = 'none';
            const percentage = Math.round((passed / totalTests) * 100);
            output.innerHTML = `
                <div style="margin-bottom: 15px; padding: 10px; background: #e3f2fd; border-radius: 5px;">
                    <strong>Score: ${passed}/${totalTests} tests passed (${percentage}%)</strong>
                </div>
                ${results}
            `;
        }

        // Initialize on page load
        window.onload = function() {
            initPyodide();
            loadAllQuestions();
        };
    </script>
</body>
</html>
