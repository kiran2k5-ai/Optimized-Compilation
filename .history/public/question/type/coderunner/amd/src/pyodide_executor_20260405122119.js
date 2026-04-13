/**
 * Pyodide Executor for CodeRunner
 * Executes Python code directly in the browser using Pyodide
 * Blocks submission to server
 */

let pyodideInstance = null;
let pyodideReady = false;

/**
 * Initialize Pyodide from CDN
 */
export const initPyodide = async function() {
    if (pyodideReady) {
        return pyodideInstance;
    }

    try {
        // Load Pyodide from CDN
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/pyodide/v0.23.4/full/pyodide.js';
        document.head.appendChild(script);

        // Wait for Pyodide to load
        await new Promise(resolve => {
            script.onload = resolve;
        });

        // Initialize Pyodide
        pyodideInstance = await window.loadPyodide();
        pyodideReady = true;
        console.log('✓ Pyodide loaded successfully');
        return pyodideInstance;
    } catch (error) {
        console.error('✗ Failed to load Pyodide:', error);
        throw error;
    }
};

/**
 * Execute Python code with student answer and test cases
 */
export const executePythonCode = async function(studentCode, testCases) {
    try {
        if (!pyodideInstance) {
            throw new Error('Pyodide not initialized');
        }

        // Create isolated namespace for execution
        const namespace = {};

        // Run student code first
        pyodideInstance.globals.set('test_results', []);
        const runCode = `
import sys
from io import StringIO

# Capture output
old_stdout = sys.stdout
sys.stdout = StringIO()

try:
    # Student code
    ${studentCode}
    sys.stdout = old_stdout
    execution_error = None
except Exception as e:
    sys.stdout = old_stdout
    execution_error = str(e)

# Store for later
student_code_executed = True
`;

        try {
            pyodideInstance.runPython(runCode);
            const executionError = pyodideInstance.globals.get('execution_error');

            if (executionError && executionError !== 'None') {
                return {
                    success: false,
                    error: executionError,
                    testResults: []
                };
            }
        } catch (error) {
            return {
                success: false,
                error: error.toString(),
                testResults: []
            };
        }

        // Now run test cases
        const testResults = [];
        for (let i = 0; i < testCases.length; i++) {
            const testCase = testCases[i];

            try {
                // Capture output for this test
                const testCode = `
import sys
from io import StringIO

old_stdout = sys.stdout
sys.stdout = StringIO()

try:
    # Test input
    ${testCase.input || ''}
    
    # Get output
    output = sys.stdout.getvalue()
    test_passed = output.strip() == "${testCase.expectedOutput.trim().replace(/"/g, '\\"')}"
    test_error = None
except Exception as e:
    sys.stdout = old_stdout
    output = ""
    test_passed = False
    test_error = str(e)

sys.stdout = old_stdout
`;

                pyodideInstance.runPython(testCode);

                const output = pyodideInstance.globals.get('output');
                const testPassed = pyodideInstance.globals.get('test_passed');
                const testError = pyodideInstance.globals.get('test_error');

                testResults.push({
                    testNumber: i + 1,
                    passed: testPassed === true || testPassed === 'True',
                    output: output || '',
                    expected: testCase.expectedOutput,
                    input: testCase.input || '',
                    error: testError && testError !== 'None' ? testError : null
                });

            } catch (error) {
                testResults.push({
                    testNumber: i + 1,
                    passed: false,
                    output: '',
                    expected: testCase.expectedOutput,
                    input: testCase.input || '',
                    error: error.toString()
                });
            }
        }

        return {
            success: true,
            testResults: testResults,
            allPassed: testResults.every(r => r.passed)
        };

    } catch (error) {
        console.error('Execution error:', error);
        return {
            success: false,
            error: error.toString(),
            testResults: []
        };
    }
};

/**
 * Get test cases from the question (from DOM or hidden fields)
 */
export const getTestCasesFromDOM = function(questionId) {
    const testCases = [];
    
    // Look for hidden input with test cases
    const testCasesInput = document.querySelector(`input[name*="testcases_${questionId}"]`);
    
    if (testCasesInput && testCasesInput.value) {
        try {
            return JSON.parse(testCasesInput.value);
        } catch (e) {
            console.error('Failed to parse test cases:', e);
        }
    }

    // Fallback: Parse from visible test case display
    const testCaseElements = document.querySelectorAll('.coderunner-test-case');
    testCaseElements.forEach((element, index) => {
        const inputText = element.querySelector('.test-input')?.textContent || '';
        const outputText = element.querySelector('.test-output')?.textContent || '';
        
        if (inputText || outputText) {
            testCases.push({
                testNumber: index + 1,
                input: inputText.trim(),
                expectedOutput: outputText.trim()
            });
        }
    });

    return testCases;
};

/**
 * Display test results in UI
 */
export const displayTestResults = function(results, containerId) {
    const container = document.getElementById(containerId) || 
                      document.querySelector('.coderunner-results');
    
    if (!container) return;

    let html = '<div class="pyodide-test-results">';
    
    if (!results.success) {
        html += `<div class="alert alert-danger">
                    <strong>Execution Error:</strong> ${results.error}
                 </div>`;
    } else {
        html += `<div class="test-summary">
                    <strong>${results.allPassed ? '✓ All tests passed!' : '✗ Some tests failed'}</strong>
                 </div>`;
        
        results.testResults.forEach(result => {
            const passClass = result.passed ? 'test-pass' : 'test-fail';
            const icon = result.passed ? '✓' : '✗';
            
            html += `
                <div class="test-result ${passClass}">
                    <div class="test-header">${icon} Test ${result.testNumber}</div>
                    <div class="test-details">
                        ${result.input ? `<div><strong>Input:</strong> <code>${escapeHtml(result.input)}</code></div>` : ''}
                        <div><strong>Expected:</strong> <code>${escapeHtml(result.expected)}</code></div>
                        <div><strong>Got:</strong> <code>${escapeHtml(result.output)}</code></div>
                        ${result.error ? `<div class="error"><strong>Error:</strong> ${escapeHtml(result.error)}</div>` : ''}
                    </div>
                </div>
            `;
        });
    }
    
    html += '</div>';
    container.innerHTML = html;
};

/**
 * Escape HTML special characters
 */
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}
