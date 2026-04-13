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
$submissionsFile = __DIR__ . '/submissions.json';

// Ensure questions file exists (starts empty - questions come from server)
if (!file_exists($questionsFile)) {
    file_put_contents($questionsFile, json_encode([], JSON_PRETTY_PRINT));
}

// Ensure submissions file exists
if (!file_exists($submissionsFile)) {
    file_put_contents($submissionsFile, json_encode([], JSON_PRETTY_PRINT));
}

// Check if this is an API request
$isApiRequest = isset($_GET['api']) || $_SERVER['REQUEST_METHOD'] !== 'GET' || isset($_GET['question_id']) || isset($_GET['action']);

if ($isApiRequest) {
    // ===== API MODE =====
    header('Content-Type: application/json');
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    // SUBMIT: Submit all answers and calculate final score
    if ($method === 'POST' && isset($_GET['action']) && $_GET['action'] === 'submit') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['answers']) || !is_array($input['answers'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Missing answers array']);
            exit();
        }
        
        $questions = json_decode(file_get_contents($questionsFile), true);
        $totalMarks = 0;
        $obtainedMarks = 0;
        $results = [];
        
        // Calculate marks for each question
        foreach ($input['answers'] as $questionId => $answerData) {
            if (!isset($questions[$questionId])) {
                continue;
            }
            
            $question = $questions[$questionId];
            $questionMarks = isset($question['marks']) ? $question['marks'] : 10;
            $totalMarks += $questionMarks;
            
            $percentage = isset($answerData['percentage']) ? floatval($answerData['percentage']) : 0;
            $questionScore = ($percentage / 100) * $questionMarks;
            $obtainedMarks += $questionScore;
            
            $results[$questionId] = [
                'title' => $question['title'],
                'marks' => $questionMarks,
                'obtained' => round($questionScore, 2),
                'percentage' => $percentage
            ];
        }
        
        // Create submission record
        $submission = [
            'timestamp' => date('Y-m-d H:i:s'),
            'student_id' => isset($input['student_id']) ? $input['student_id'] : 'anonymous',
            'total_marks' => $totalMarks,
            'obtained_marks' => round($obtainedMarks, 2),
            'percentage' => round(($obtainedMarks / $totalMarks) * 100, 2),
            'answers' => $results
        ];
        
        // Save submission
        $submissions = json_decode(file_get_contents($submissionsFile), true);
        $submission['id'] = 'sub_' . time() . '_' . rand(1000, 9999);
        $submissions[$submission['id']] = $submission;
        file_put_contents($submissionsFile, json_encode($submissions, JSON_PRETTY_PRINT));
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Submission successful',
            'submission' => $submission
        ]);
        exit();
    }
    
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
    
    // POST: Add or update question(s) with marks
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Read current questions
        $questions = json_decode(file_get_contents($questionsFile), true);
        $savedCount = 0;
        
        // Check if input contains array of questions
        if (isset($input['questions']) && is_array($input['questions'])) {
            foreach ($input['questions'] as $questionData) {
                if (!isset($questionData['question_id']) || !isset($questionData['tests'])) {
                    continue;
                }
                
                $questionId = $questionData['question_id'];
                $title = isset($questionData['title']) ? $questionData['title'] : 'Untitled';
                $description = isset($questionData['description']) ? $questionData['description'] : '';
                $language = isset($questionData['language']) ? $questionData['language'] : 'python';
                $marks = isset($questionData['marks']) ? intval($questionData['marks']) : 10;
                $tests = $questionData['tests'];
                
                $questions[$questionId] = [
                    'title' => $title,
                    'description' => $description,
                    'language' => $language,
                    'marks' => $marks,
                    'tests' => $tests
                ];
                $savedCount++;
            }
            
            if ($savedCount === 0) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'No valid questions to save']);
                exit();
            }
            
            file_put_contents($questionsFile, json_encode($questions, JSON_PRETTY_PRINT));
            
            echo json_encode([
                'status' => 'success', 
                'message' => $savedCount . ' question(s) saved successfully',
                'count' => $savedCount
            ]);
            exit();
        } 
        // Single question format
        else if (isset($input['question_id']) && isset($input['tests'])) {
            $questionId = $input['question_id'];
            $title = isset($input['title']) ? $input['title'] : 'Untitled';
            $description = isset($input['description']) ? $input['description'] : '';
            $language = isset($input['language']) ? $input['language'] : 'python';
            $marks = isset($input['marks']) ? intval($input['marks']) : 10;
            $tests = $input['tests'];
            
            $questions[$questionId] = [
                'title' => $title,
                'description' => $description,
                'language' => $language,
                'marks' => $marks,
                'tests' => $tests
            ];
            
            file_put_contents($questionsFile, json_encode($questions, JSON_PRETTY_PRINT));
            
            echo json_encode(['status' => 'success', 'message' => 'Question saved']);
            exit();
        } else {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid format']);
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
    <title>Code Runner - Quiz</title>
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
            flex-wrap: wrap;
            gap: 10px;
        }

        .top-bar h2 {
            color: #333;
            font-size: 1.2em;
            margin: 0;
        }

        .top-info {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .marks-display {
            background: #4caf50;
            color: white;
            padding: 8px 15px;
            border-radius: 3px;
            font-size: 0.9em;
            font-weight: bold;
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

        .nav-btn, .submit-btn {
            background: #2196F3;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 3px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
        }

        .submit-btn {
            background: #4caf50;
        }

        .submit-btn:hover {
            background: #45a049;
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

        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .question-panel h3 {
            color: #333;
            font-size: 1.1em;
            margin: 0;
        }

        .question-marks {
            background: #fff3cd;
            color: #856404;
            padding: 6px 12px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 0.9em;
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

        .score-summary {
            background: #e3f2fd;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .score-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 5px 0;
        }

        .final-results {
            background: #e8f5e9;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
        }

        .final-results h2 {
            color: #2e7d32;
            margin-bottom: 10px;
        }

        .final-score {
            font-size: 2em;
            font-weight: bold;
            color: #4caf50;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="top-bar">
            <h2 id="question-title">Loading...</h2>
            <div class="top-info">
                <div class="marks-display" id="marks-display">0 / 0 Marks</div>
                <div class="question-counter">Question <span id="question-counter">1</span> of <span id="total-questions">1</span></div>
                <div class="navigation">
                    <button class="nav-btn" onclick="previousQuestion()" id="prev-btn">← Back</button>
                    <button class="nav-btn" onclick="nextQuestion()" id="next-btn">Next →</button>
                    <button class="submit-btn" onclick="submitQuiz()" id="submit-btn" style="display: none;">✓ Submit</button>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="question-panel">
                <div class="question-header">
                    <h3>Problem Statement</h3>
                    <div class="question-marks">Marks: <span id="question-marks">10</span></div>
                </div>
                <p id="question-description">Loading question...</p>
            </div>

            <div class="editor-section">
                <h3>📝 Code Editor</h3>
                <textarea id="code-editor" placeholder="Write your code here..."></textarea>
            </div>

            <div class="test-case-section">
                <h4>🧪 Test Cases</h4>
                <div id="test-cases-container"></div>
                <button class="btn-run" onclick="runCode()">▶️ Run All Tests</button>
            </div>

            <div class="output-panel">
                <h3>📊 Output & Results</h3>
                <div class="loading" id="loading">⏳ Running code...</div>
                <div class="output-box" id="output-box">Ready to run code...</div>
            </div>

            <div id="final-results-container"></div>
        </div>
    </div>

    <script>
        let pyodide = null;
        let allQuestions = [];
        let currentQuestionIndex = 0;
        let currentQuestion = null;
        let questionScores = {};
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

        // Load all questions
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
                        updateMarksDisplay();
                    }
                }
            } catch (error) {
                document.getElementById('output-box').innerHTML = '<span class="error">❌ Error: ' + error.message + '</span>';
            }
        }

        // Display current question
        function displayQuestion() {
            currentQuestion = allQuestions[currentQuestionIndex];
            
            document.getElementById('question-title').textContent = currentQuestion.title;
            document.getElementById('question-counter').textContent = currentQuestionIndex + 1;
            document.getElementById('question-marks').textContent = currentQuestion.marks || 10;
            document.getElementById('question-description').textContent = currentQuestion.description || 'No description';
            document.getElementById('code-editor').value = '';
            
            displayTestCases();
            document.getElementById('output-box').innerHTML = '<span class="success">✅ Question loaded</span>';
        }

        // Display test cases
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

        // Navigate questions
        function nextQuestion() {
            if (currentQuestionIndex < allQuestions.length - 1) {
                currentQuestionIndex++;
                displayQuestion();
                updateNavigation();
            }
        }

        function previousQuestion() {
            if (currentQuestionIndex > 0) {
                currentQuestionIndex--;
                displayQuestion();
                updateNavigation();
            }
        }

        // Update navigation
        function updateNavigation() {
            const isLast = currentQuestionIndex === allQuestions.length - 1;
            const isFirst = currentQuestionIndex === 0;
            
            document.getElementById('prev-btn').disabled = isFirst;
            document.getElementById('next-btn').disabled = isLast;
            document.getElementById('next-btn').style.display = isLast ? 'none' : 'inline-block';
            document.getElementById('submit-btn').style.display = isLast ? 'inline-block' : 'none';
        }

        // Update marks display
        function updateMarksDisplay() {
            let totalMarks = 0;
            let obtainedMarks = 0;
            
            allQuestions.forEach(q => {
                const marks = q.marks || 10;
                totalMarks += marks;
                if (questionScores[q.id]) {
                    obtainedMarks += questionScores[q.id];
                }
            });
            
            document.getElementById('marks-display').textContent = Math.round(obtainedMarks) + ' / ' + totalMarks + ' Marks';
        }

        // Run code
        async function runCode() {
            const code = document.getElementById('code-editor').value;
            const output = document.getElementById('output-box');
            const loading = document.getElementById('loading');
            
            if (!code.trim()) {
                output.innerHTML = '<span class="error">❌ Please write code first!</span>';
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
                        results += `<div class="test-result passed"><strong>✅ Test ${i + 1}: PASSED</strong></div>`;
                    } else {
                        results += `<div class="test-result failed"><strong>❌ Test ${i + 1}: FAILED</strong><br>Expected: <code>${expectedOutput}</code><br>Got: <code>${actualOutput}</code></div>`;
                    }
                } catch (error) {
                    results += `<div class="test-result failed"><strong>❌ Test ${i + 1}: ERROR</strong><br><code>${error.message}</code></div>`;
                }
            }

            const percentage = Math.round((passed / totalTests) * 100);
            const markObtained = ((percentage / 100) * (currentQuestion.marks || 10));
            questionScores[currentQuestion.id] = markObtained;
            
            loading.style.display = 'none';
            output.innerHTML = `
                <div class="score-summary">
                    <div class="score-info"><strong>Score:</strong> ${passed}/${totalTests} tests passed</div>
                    <div class="score-info"><strong>Percentage:</strong> ${percentage}%</div>
                    <div class="score-info"><strong>Marks Obtained:</strong> ${markObtained.toFixed(2)}/${currentQuestion.marks || 10}</div>
                </div>
                ${results}
            `;
            
            updateMarksDisplay();
        }

        // Submit quiz
        async function submitQuiz() {
            const answers = {};
            
            allQuestions.forEach(q => {
                let totalTests = q.tests.length;
                let passedTests = 0;
                
                if (questionScores[q.id]) {
                    const marksObtained = questionScores[q.id];
                    const totalMarks = q.marks || 10;
                    const percentage = (marksObtained / totalMarks) * 100;
                    answers[q.id] = {
                        percentage: percentage
                    };
                } else {
                    answers[q.id] = {
                        percentage: 0
                    };
                }
            });

            try {
                const response = await fetch(API_URL + '?action=submit', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        student_id: prompt('Enter your student ID (or leave blank):') || 'anonymous',
                        answers: answers
                    })
                });

                const result = await response.json();
                
                if (result.status === 'success') {
                    displayFinalResults(result.submission);
                } else {
                    alert('Error submitting quiz: ' + result.message);
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }

        // Display final results
        function displayFinalResults(submission) {
            const resultsHtml = `
                <div class="final-results">
                    <h2>✓ Quiz Submitted Successfully!</h2>
                    <div class="final-score">${submission.obtained_marks} / ${submission.total_marks}</div>
                    <p><strong>Percentage: ${submission.percentage}%</strong></p>
                    <p>Submission ID: ${submission.id}</p>
                    <p>Time: ${submission.timestamp}</p>
                </div>
            `;
            
            document.getElementById('final-results-container').innerHTML = resultsHtml;
            document.getElementById('submit-btn').disabled = true;
        }

        // Initialize
        window.onload = function() {
            initPyodide();
            loadAllQuestions();
        };
    </script>
</body>
</html>
