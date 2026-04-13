/**
 * Pyodide Sandbox for Moodle CodeRunner
 * Replaces externally blocked Jobe with browser-based Python execution
 */

(function() {
    'use strict';
    
    let pyodideLoaded = false;
    
    // Load Pyodide library
    async function initPyodide() {
        if (pyodideLoaded) return globalThis.pyodide;
        
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/pyodide/v0.23.0/full/pyodide.js';
            script.async = true;
            
            script.onload = async function() {
                try {
                    const pyodide = await window.loadPyodide();
                    globalThis.pyodide = pyodide;
                    pyodideLoaded = true;
                    console.log('✓ Pyodide loaded successfully');
                    resolve(pyodide);
                } catch (e) {
                    reject(e);
                }
            };
            
            script.onerror = function() {
                reject(new Error('Failed to load Pyodide'));
            };
            
            document.head.appendChild(script);
        });
    }
    
    // Find and hook Check button
    function setupCheckButton() {
        console.log('Setting up Pyodide sandbox...');
        
        // Wait longer for page to fully initialize
        setTimeout(() => {
            try {
                // Look for CodeRunner check buttons
                const buttons = document.querySelectorAll('button');
                let found = false;
                
                buttons.forEach(btn => {
                    if (btn.textContent.trim() === 'Check') {
                        console.log('Found Check button, hooking it...');
                        found = true;
                        
                        btn.addEventListener('click', function(e) {
                            // Get the form and textarea
                            const form = btn.closest('form');
                            if (!form) {
                                console.warn('Could not find form');
                                return;
                            }
                            
                            const textarea = form.querySelector('textarea');
                            if (!textarea) {
                                console.warn('Could not find textarea');
                                return;
                            }
                            
                            // This is our CodeRunner question!
                            e.preventDefault();
                            e.stopPropagation();
                            
                            console.log('Check button clicked - executing with Pyodide');
                            executeCode(textarea.value, form);
                            
                            return false;
                        }, true);
                    }
                });
                
                if (!found) {
                    console.warn('No Check button found, will retry...');
                    // Try again after 2 seconds
                    setTimeout(setupCheckButton, 2000);
                }
            } catch (error) {
                console.error('Error setting up check button:', error);
            }
        }, 2000);  // Wait 2 seconds for full initialization
    }
    
    // Execute student's code
    async function executeCode(code, form) {
        if (!code.trim()) {
            alert('Please enter some code.');
            return;
        }
        
        try {
            // Initialize Pyodide
            const pyodide = await initPyodide();
            
            console.log('Executing code with Pyodide...');
            
            // Simple test: just run the code and capture output
            // In real scenario, you'd also run test cases here
            const output = await pyodide.runPythonAsync(code);
            
            // Display output in results area
            showResults(output, form);
            
        } catch (error) {
            console.error('Execution error:', error);
            showError(error.message, form);
        }
    }
    
    // Display results to user
    function showResults(output, form) {
        // Find feedback area or create one
        let feedback = form.querySelector('[class*="feedback"]');
        
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.style.marginTop = '15px';
            form.appendChild(feedback);
        }
        
        const outputStr = String(output);
        const html = `
            <div style="padding: 15px; background: #d4edda; border: 1px solid #28a745; border-radius: 4px;">
                <strong>✓ Execution Successful:</strong>
                <pre style="margin-top: 10px; background: white; padding: 10px; border-radius: 4px; overflow-x: auto;">${escapeHtml(outputStr)}</pre>
            </div>
        `;
        
        feedback.innerHTML = html;
    }
    
    // Display error to user
    function showError(error, form) {
        let feedback = form.querySelector('[class*="feedback"]');
        
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.style.marginTop = '15px';
            form.appendChild(feedback);
        }
        
        const html = `
            <div style="padding: 15px; background: #f8d7da; border: 1px solid #dc3545; border-radius: 4px;">
                <strong>✗ Error:</strong>
                <pre style="margin-top: 10px; background: white; padding: 10px; border-radius: 4px; overflow-x: auto; color: red;">${escapeHtml(error)}</pre>
            </div>
        `;
        
        feedback.innerHTML = html;
    }
    
    // Escape HTML for safe display
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
    
    // Initialize on page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupCheckButton);
    } else {
        setupCheckButton();
    }
    
    console.log('Pyodide Sandbox script loaded');
})();
