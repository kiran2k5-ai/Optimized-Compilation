<?php
/**
 * Pyodide Sandbox Integration for Moodle Quiz
 * Replaces CodeRunner Jobe execution with browser-based Pyodide
 * 
 * This script:
 * 1. Injects Pyodide JavaScript library into quiz page
 * 2. Intercepts CodeRunner "Check" requests
 * 3. Executes code locally using Pyodide
 * 4. Returns results to Moodle
 */

// Include Moodle config
require_once('../../config.php');

// Get question ID from request
$questionid = optional_param('questionid', 0, PARAM_INT);
$code = optional_param('code', '', PARAM_RAW);
$testcases = optional_param('testcases', '[]', PARAM_RAW);

// Check if this is an API request for code execution
if ($code && $testcases) {
    header('Content-Type: application/json');
    
    // Decode test cases
    $tests = json_decode($testcases, true);
    
    // Return Pyodide execution instructions
    $response = [
        'success' => true,
        'type' => 'pyodide',
        'code' => $code,
        'testcases' => $tests,
        'message' => 'Execute this in Pyodide'
    ];
    
    echo json_encode($response);
    exit;
}

// Otherwise, output JavaScript to inject into quiz page
header('Content-Type: application/javascript');
?>

// ============================================================
// PYODIDE SANDBOX INTEGRATION
// Injected into Moodle Quiz Page
// ============================================================

(function() {
    console.log('Pyodide Sandbox: Injecting into CodeRunner quiz...');
    
    // Load Pyodide
    async function loadPyodide() {
        if (typeof globalThis.pyodide !== 'undefined') {
            return globalThis.pyodide;
        }
        
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/pyodide/v0.23.0/full/pyodide.js';
        script.async = true;
        
        return new Promise((resolve) => {
            script.onload = async function() {
                const pyodide = await window.loadPyodide();
                globalThis.pyodide = pyodide;
                resolve(pyodide);
            };
            document.head.appendChild(script);
        });
    }
    
    // Initialize Pyodide when page loads
    window.addEventListener('load', function() {
        console.log('Initializing Pyodide...');
        loadPyodide().then(() => {
            console.log('Pyodide ready!');
            hookCodeRunnerCheckButton();
        });
    });
    
    // Hook into CodeRunner "Check" button
    function hookCodeRunnerCheckButton() {
        // Find all "Check" buttons for CodeRunner questions
        const checkButtons = document.querySelectorAll('button:contains("Check")');
        
        checkButtons.forEach(button => {
            if (button.textContent.includes('Check')) {
                button.addEventListener('click', function(e) {
                    // Check if this is a CodeRunner question
                    const form = button.closest('form');
                    const codeEditor = form.querySelector('textarea[name*="subquestion"]');
                    
                    if (codeEditor && form.getAttribute('id').includes('responseform')) {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        console.log('CodeRunner Check button clicked - using Pyodide');
                        executeWithPyodide(codeEditor, form);
                        
                        return false;
                    }
                });
            }
        });
    }
    
    // Execute code using Pyodide
    async function executeWithPyodide(codeEditor, form) {
        const code = codeEditor.value;
        
        if (!code.trim()) {
            alert('Please enter some code first!');
            return;
        }
        
        try {
            const pyodide = await loadPyodide();
            
            // Get test cases (you'll need to pass these from PHP)
            // For now, we'll use placeholder test cases
            const testcases = getTestCasesFromQuestion();
            
            console.log('Running code with Pyodide:');
            console.log(code);
            
            // Run code and tests
            const results = await runTests(pyodide, code, testcases);
            
            // Display results in Moodle
            displayResults(results, form);
            
        } catch (error) {
            console.error('Pyodide execution error:', error);
            alert('Code execution error: ' + error.message);
        }
    }
    
    // Run test cases
    async function runTests(pyodide, code, testcases) {
        const results = [];
        let passed = 0;
        
        for (const testcase of testcases) {
            try {
                const fullCode = code + '\n' + testcase.input;
                const output = await pyodide.runPythonAsync(fullCode);
                const outputStr = String(output);
                const isCorrect = outputStr === testcase.expected;
                
                results.push({
                    name: testcase.name,
                    input: testcase.input,
                    expected: testcase.expected,
                    actual: outputStr,
                    passed: isCorrect
                });
                
                if (isCorrect) passed++;
            } catch (error) {
                results.push({
                    name: testcase.name,
                    input: testcase.input,
                    expected: testcase.expected,
                    actual: 'ERROR',
                    error: error.message,
                    passed: false
                });
            }
        }
        
        return {
            results: results,
            passed: passed,
            total: testcases.length,
            percentage: Math.round((passed / testcases.length) * 100)
        };
    }
    
    // Get test cases from question
    function getTestCasesFromQuestion() {
        // This should be populated from Moodle database
        // For now returning example
        return [
            { name: 'Test 1', input: 'sum(2, 3)', expected: '5' },
            { name: 'Test 2', input: 'sum(10, 20)', expected: '30' },
            { name: 'Test 3', input: 'sum(-5, 5)', expected: '0' }
        ];
    }
    
    // Display results in Moodle
    function displayResults(results, form) {
        let html = '<div style="padding: 15px; border: 1px solid #ddd; margin-top: 15px; background: #f9f9f9;">';
        html += '<h4>Test Results:</h4>';
        
        for (const result of results) {
            const statusIcon = result.passed ? '✅' : '❌';
            const statusClass = result.passed ? 'result-pass' : 'result-fail';
            const background = result.passed ? '#d4edda' : '#f8d7da';
            
            html += `<div style="padding: 10px; margin: 8px 0; border-radius: 4px; background: ${background};">
                ${statusIcon} ${result.name}: ${result.passed ? 'PASS' : 'FAIL'}
                <br><small>${result.input}</small>
                <br><small>Got: ${result.actual} (expected: ${result.expected})</small>
            </div>`;
        }
        
        const scoreBackground = results.passed === results.total ? '#d4edda' : '#f8d7da';
        html += `<div style="padding: 15px; margin-top: 15px; background: ${scoreBackground}; border-radius: 4px; text-align: center; font-weight: bold;">
            Score: ${results.passed}/${results.total} = ${results.percentage}%
        </div>`;
        html += '</div>';
        
        // Find or create results area
        let resultsDiv = form.querySelector('[id*="feedback"]');
        if (!resultsDiv) {
            resultsDiv = document.createElement('div');
            resultsDiv.style.marginTop = '15px';
            form.appendChild(resultsDiv);
        }
        resultsDiv.innerHTML = html;
    }
    
    // Helper: Check if element text contains string
    Element.prototype.contains = function(selector) {
        return this.textContent.includes(selector);
    };
})();
