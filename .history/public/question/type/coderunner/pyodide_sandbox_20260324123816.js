/**
 * Pyodide Sandbox Integration for CodeRunner
 * Adds Check button and local code execution to existing CodeRunner questions
 */

(function() {
    'use strict';
    
    // Configuration
    const PYODIDE_CDN = 'https://cdn.jsdelivr.net/pyodide/v0.23.0/full/pyodide.js';
    let pyodideReady = false;
    let pyodide = null;
    
    // Initialize Pyodide
    async function initPyodide() {
        try {
            console.log('Initializing Pyodide...');
            importScripts(PYODIDE_CDN);
            window.pyodide = await loadPyodide();
            pyodideReady = true;
            console.log('Pyodide initialized successfully');
            
            // Add Check buttons to all CodeRunner questions
            addCheckButtons();
            
        } catch (error) {
            console.error('Failed to initialize Pyodide:', error);
            showMessage('Error loading Pyodide sandbox', 'error');
        }
    }
    
    // Dynamically load Pyodide script
    function loadPyodideScript() {
        const script = document.createElement('script');
        script.src = PYODIDE_CDN;
        script.async = true;
        script.defer = true;
        script.onload = function() {
            loadPyodide().then(function(py) {
                pyodide = py;
                pyodideReady = true;
                console.log('Pyodide loaded and ready');
                addCheckButtons();
            }).catch(function(error) {
                console.error('Failed to load Pyodide:', error);
                showMessage('Error initializing sandbox: ' + error.message, 'error');
            });
        };
        document.head.appendChild(script);
    }
    
    // Find CodeRunner questions and add Check buttons
    function addCheckButtons() {
        // Find all CodeRunner answer textareas/editors
        const codeEditors = document.querySelectorAll('[id^="id_answer"]');
        
        codeEditors.forEach(function(editor, index) {
            const questionDiv = editor.closest('.qtype_coderunner');
            if (!questionDiv) return;
            
            // Check if Check button already exists
            if (questionDiv.querySelector('.pyodide-check-btn')) return;
            
            // Extract question ID from element ID
            const editorId = editor.id;
            const qid = extractQuestionId(editorId);
            
            if (!qid) return;
            
            console.log('Adding Check button for question:', qid);
            
            // Create Check button container
            const checkContainer = document.createElement('div');
            checkContainer.className = 'pyodide-check-container';
            checkContainer.style.marginTop = '15px';
            
            // Create Check button
            const checkBtn = document.createElement('button');
            checkBtn.type = 'button';
            checkBtn.className = 'pyodide-check-btn btn btn-success';
            checkBtn.innerHTML = '<i class="fa fa-play"></i> Check Code';
            checkBtn.style.marginRight = '10px';
            
            if (!pyodideReady) {
                checkBtn.disabled = true;
                checkBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Loading Sandbox...';
            }
            
            // Create results container
            const resultsDiv = document.createElement('div');
            resultsDiv.id = 'pyodide-results-' + qid;
            resultsDiv.className = 'pyodide-results';
            resultsDiv.style.marginTop = '15px';
            resultsDiv.style.display = 'none';
            
            // Add click handler
            checkBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (pyodideReady) {
                    executeCode(qid, editor, resultsDiv, checkBtn);
                } else {
                    showMessage('Sandbox is still loading...', 'warning');
                }
            });
            
            // Insert into page
            const editorParent = editor.parentElement;
            checkContainer.appendChild(checkBtn);
            editorParent.appendChild(checkContainer);
            editorParent.appendChild(resultsDiv);
        });
    }
    
    // Extract question ID from element ID
    function extractQuestionId(elementId) {
        // Format: id_answer_1234567 or similar
        const match = elementId.match(/\d+/);
        return match ? match[0] : null;
    }
    
    // Execute code with test cases
    async function executeCode(qid, editor, resultsDiv, btn) {
        try {
            // Get code from editor
            let code = '';
            
            // Try to get code from Ace editor
            if (editor.env && editor.env.editor) {
                code = editor.env.editor.getValue();
            } 
            // Fallback: get from textarea
            else if (editor.value !== undefined) {
                code = editor.value;
            }
            
            if (!code.trim()) {
                showMessage('Please enter some code first!', 'warning');
                return;
            }
            
            // Disable button and show loading
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Running tests...';
            resultsDiv.style.display = 'block';
            resultsDiv.innerHTML = '<p style="text-align:center; color:#666;"><i class="fa fa-spinner fa-spin"></i> Executing code...</p>';
            
            // Fetch test cases from server
            const testcases = await fetchTestCases(qid);
            
            // Run tests
            const results = await runTests(code, testcases);
            
            // Display results
            displayResults(resultsDiv, results);
            
            // Re-enable button
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-play"></i> Check Code';
            
        } catch (error) {
            console.error('Error executing code:', error);
            resultsDiv.style.display = 'block';
            resultsDiv.innerHTML = '<div class="alert alert-danger"><strong>Error:</strong> ' + error.message + '</div>';
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-play"></i> Check Code';
        }
    }
    
    // Fetch test cases from server
    async function fetchTestCases(qid) {
        try {
            const response = await fetch('pyodide_ajax.php?action=gettestcases&qid=' + qid);
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.error || 'Failed to fetch test cases');
            }
            
            return data.testcases;
            
        } catch (error) {
            console.error('Error fetching test cases:', error);
            throw new Error('Could not load test cases: ' + error.message);
        }
    }
    
    // Run tests using Pyodide
    async function runTests(code, testcases) {
        const results = [];
        
        for (const testcase of testcases) {
            try {
                // Build complete test code
                const testCode = code + '\n' + testcase.input;
                
                // Execute in Pyodide
                const output = await pyodide.runPythonAsync(testCode);
                const outputStr = String(output).trim();
                const expectedStr = String(testcase.expected_output).trim();
                
                results.push({
                    name: 'Test ' + testcase.id,
                    input: testcase.input,
                    expected: testcase.expected_output,
                    actual: outputStr,
                    passed: outputStr === expectedStr,
                    extra: testcase.extra,
                    display: testcase.display
                });
                
            } catch (error) {
                results.push({
                    name: 'Test ' + testcase.id,
                    input: testcase.input,
                    expected: testcase.expected_output,
                    actual: 'ERROR',
                    error: error.message,
                    passed: false,
                    display: testcase.display
                });
            }
        }
        
        return results;
    }
    
    // Display test results
    function displayResults(container, results) {
        let passed = 0;
        let failed = 0;
        
        let html = '<div class="pyodide-test-results">';
        html += '<h5>Test Results:</h5>';
        html += '<div style="max-height: 300px; overflow-y: auto;">';
        
        for (const result of results) {
            const icon = result.passed ? '✅' : '❌';
            const status = result.passed ? 'PASS' : 'FAIL';
            const className = result.passed ? 'success' : 'danger';
            
            if (result.passed) passed++;
            else failed++;
            
            html += '<div style="padding:10px; margin:5px 0; border-left:3px solid ' + (result.passed ? '#28a745' : '#dc3545') + '; background:' + (result.passed ? '#d4edda' : '#f8d7da') + ';">';
            html += '<strong>' + icon + ' ' + result.name + ': ' + status + '</strong><br>';
            html += '<small style="color:#666;">' + result.input + '</small>';
            
            if (!result.passed) {
                if (result.error) {
                    html += '<br><small style="color:red;"><strong>Error:</strong> ' + result.error + '</small>';
                } else {
                    html += '<br><small style="font-family:monospace;">Got: ' + result.actual + ' | Expected: ' + result.expected + '</small>';
                }
            }
            
            html += '</div>';
        }
        
        html += '</div>';
        
        // Summary
        const total = passed + failed;
        const percentage = Math.round((passed / total) * 100);
        const summaryClass = passed === total ? 'success' : 'warning';
        
        html += '<div style="margin-top:15px; padding:15px; background:#f0f8ff; border-radius:4px; text-align:center; font-weight:bold;">';
        html += 'Score: ' + passed + '/' + total + ' = ' + percentage + '%';
        if (passed === total) {
            html += ' ✅ All tests passed!';
        }
        html += '</div>';
        
        html += '</div>';
        
        container.innerHTML = html;
    }
    
    // Show message helper
    function showMessage(message, type) {
        const alertClass = 'alert alert-' + (type === 'error' ? 'danger' : type);
        console.log('[' + type.toUpperCase() + ']', message);
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadPyodideScript);
    } else {
        loadPyodideScript();
    }
    
})();
