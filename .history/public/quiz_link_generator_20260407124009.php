<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Link Generator</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { color: #333; }
        textarea {
            width: 100%;
            height: 300px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: monospace;
            font-size: 12px;
            margin: 20px 0;
        }
        button {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover { background: #0056b3; }
        .result {
            margin-top: 20px;
            padding: 15px;
            background: #f0f0f0;
            border-radius: 4px;
            word-break: break-all;
        }
        .copy-btn {
            background: #28a745;
            padding: 8px 15px;
            font-size: 14px;
        }
        .copy-btn:hover { background: #218838; }
        .example {
            background: #f9f9f9;
            padding: 15px;
            border-left: 4px solid #007bff;
            margin: 20px 0;
            font-family: monospace;
            font-size: 12px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📝 Quiz Link Generator</h1>
        <p>Paste your questions JSON below to generate a shareable quiz link (like Moodle sends to quiz page)</p>

        <textarea id="questionsJson" placeholder='Paste your questions JSON here. Example:
{
  "q1": {
    "title": "Backtracking",
    "description": "Solve N-Queens...",
    "language": "python",
    "marks": 10,
    "tests": [
      {"input": "4", "expected": "2"}
    ]
  }
}'></textarea>

        <button onclick="generateLink()">Generate Quiz Link</button>

        <div id="result" style="display:none;" class="result">
            <p><strong>Your Quiz Link:</strong></p>
            <input type="text" id="quizLink" readonly style="width:100%; padding:10px; margin:10px 0; border:1px solid #ddd;">
            <button class="copy-btn" onclick="copyLink()">📋 Copy Link</button>
            <p style="margin-top:15px; color:#666; font-size:12px;">
                ✅ Share this link - students paste it in browser to see questions, test cases, and marks
            </p>
        </div>

        <div class="example">
            <strong>Example JSON for 3 Questions (10 marks each):</strong>
            <pre>{
  "q1": {
    "title": "Backtracking - N-Queens",
    "description": "Solve the N-Queens problem",
    "language": "python",
    "marks": 10,
    "tests": [
      {"input": "4", "expected": "2"},
      {"input": "1", "expected": "1"}
    ]
  },
  "q2": {
    "title": "Dynamic Programming - Fibonacci",
    "description": "Solve nth Fibonacci using DP",
    "language": "python",
    "marks": 10,
    "tests": [
      {"input": "5", "expected": "5"},
      {"input": "7", "expected": "13"}
    ]
  },
  "q3": {
    "title": "Tree - Binary Tree Height",
    "description": "Calculate height of binary tree",
    "language": "python",
    "marks": 10,
    "tests": [
      {"input": "1 2 3", "expected": "2"},
      {"input": "5", "expected": "0"}
    ]
  }
}</pre>
        </div>
    </div>

    <script>
        function generateLink() {
            const jsonText = document.getElementById('questionsJson').value;
            try {
                const questionsObj = JSON.parse(jsonText);
                const base64 = btoa(JSON.stringify(questionsObj));
                const link = 'http://localhost/code_runner.php?setup=' + base64;
                
                document.getElementById('quizLink').value = link;
                document.getElementById('result').style.display = 'block';
            } catch (e) {
                alert('❌ Invalid JSON: ' + e.message);
            }
        }

        function copyLink() {
            const link = document.getElementById('quizLink');
            link.select();
            document.execCommand('copy');
            alert('✅ Link copied to clipboard!');
        }

        // Allow Enter key to generate link
        document.getElementById('questionsJson').addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.key === 'Enter') generateLink();
        });
    </script>
</body>
</html>
