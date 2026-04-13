/**
 * Pyodide Executor - Executes Python code in browser
 * Replaces Jobe server with local Pyodide execution
 */

var PyodideExecutor = (function() {
    'use strict';
    
    // Initialize Pyodide
    let pyodideReady = false;
    let pyodide = null;
    
    async function initPyodide() {
        if (pyodideReady) return;
        
        try {
            // Load Pyodide from CDN
            pyodide = await loadPyodide({
                indexURL: 'https://cdn.jsdelivr.net/pyodide/v0.23.0/full/'
            });
            pyodideReady = true;
            console.log('Pyodide initialized successfully');
        } catch (error) {
            console.error('Failed to initialize Pyodide:', error);
            throw error;
        }
    }
    
    /**
     * Execute Python code and return results
     * @param {string} code - Python code to execute
     * @param {string} input - Standard input for the code
     * @returns {object} - Execution result
     */
    async function executeCode(code, input = '') {
        await initPyodide();
        
        try {
            // Redirect stdout/stderr
            let stdout = '';
            let stderr = '';
            let exitCode = 0;
            
            // Capture output
            pyodide.FS.writeFile('/dev/stdin', input);
            
            // Create a namespace for execution
            const namespace = pyodide.toPy({});
            
            // Set up output capture
            pyodide.runPython(`
import sys
from io import StringIO

# Capture output
old_stdout = sys.stdout
old_stderr = sys.stderr
sys.stdout = StringIO()
sys.stderr = StringIO()
`);
            
            // Execute the code
            try {
                pyodide.runPython(code);
                stdout = pyodide.runPython('sys.stdout.getvalue()');
                stderr = pyodide.runPython('sys.stderr.getvalue()');
                exitCode = 0;
            } catch (err) {
                stderr = err.toString();
                exitCode = 1;
            }
            
            // Restore output streams
            pyodide.runPython(`
sys.stdout = old_stdout
sys.stderr = old_stderr
`);
            
            return {
                stdout: stdout,
                stderr: stderr,
                exitCode: exitCode,
                cputime: 0.1,  // Mock CPU time
                executed: true
            };
            
        } catch (error) {
            return {
                stdout: '',
                stderr: error.toString(),
                exitCode: 1,
                cputime: 0,
                executed: false
            };
        }
    }
    
    /**
     * Run multiple test cases against code
     * @param {array} testcases - Array of test case objects
     * @param {string} code - Python code to test
     * @returns {array} - Array of test results
     */
    async function runTestCases(testcases, code) {
        await initPyodide();
        
        const results = [];
        
        for (let i = 0; i < testcases.length; i++) {
            const testcase = testcases[i];
            
            try {
                // Execute code with test input
                const result = await executeCode(code, testcase.input || '');
                
                // Check if output matches expected
                const actual = result.stdout.toString().trim();
                const expected = (testcase.expected || '').toString().trim();
                const isCorrect = actual === expected;
                
                results.push({
                    index: i,
                    iscorrect: isCorrect,
                    output: actual,
                    expected: expected,
                    feedback: isCorrect ? '✓ Test passed' : '✗ Test failed',
                    stderr: result.stderr,
                    mark: isCorrect ? 1 : 0
                });
                
            } catch (error) {
                results.push({
                    index: i,
                    iscorrect: false,
                    output: '',
                    expected: testcase.expected,
                    feedback: 'Error executing test: ' + error.toString(),
                    stderr: error.toString(),
                    mark: 0
                });
            }
        }
        
        return results;
    }
    
    /**
     * Calculate marks based on test results
     * @param {array} testResults - Array of test outcome objects
     * @param {number} maxMarks - Maximum marks for the question
     * @returns {number} - Calculated marks
     */
    function calculateMarks(testResults, maxMarks = 10) {
        if (!testResults || testResults.length === 0) return 0;
        
        const passed = testResults.filter(r => r.iscorrect).length;
        const total = testResults.length;
        const percentage = (passed / total) * 100;
        
        return Math.round((percentage / 100) * maxMarks * 100) / 100;
    }
    
    // Public API
    return {
        init: initPyodide,
        executeCode: executeCode,
        runTestCases: runTestCases,
        calculateMarks: calculateMarks,
        isReady: function() { return pyodideReady; }
    };
})();

// Make available globally
window.PyodideExecutor = PyodideExecutor;

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    PyodideExecutor.init().catch(err => {
        console.warn('Pyodide initialization deferred:', err);
    });
});
