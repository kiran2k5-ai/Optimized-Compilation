/**
 * Pyodide Sandbox for Moodle CodeRunner
 * Replaces externally blocked Jobe with browser-based Python execution
 * Executes code on same page without any form submission or page refresh
 */

(function() {
    'use strict';
    
    let pyodideLoaded = false;
    
    // Load Pyodide library from CDN
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
                    console.error('Failed to initialize Pyodide:', e);
                    reject(e);
                }
            };
            
            script.onerror = function() {
                console.error('Failed to load Pyodide script');
                reject(new Error('Failed to load Pyodide from CDN'));
            };
            
            document.head.appendChild(script);
        });
    }
    
    // Setup and hook the Check button
    function setupCheckButton() {
        console.log('Setting up Pyodide sandbox for CodeRunner...');
        
        // Wait for Moodle to fully initialize
        setTimeout(() => {
            try {
                // FIRST: Hook the form submission to prevent it completely
                const form = document.querySelector('form');
                if (form) {
                    console.log('✓ Found form, blocking its submission...');
                    
                    // Store original submit
                    const originalSubmit = form.submit;
                    
                    // Override form.submit() to prevent any submission
                    form.submit = function() {
                        console.warn('✗ Form submission blocked by Pyodide sandbox');
                        return false;
                    };
                    
                    // Also override onsubmit
                    form.onsubmit = function(e) {
                        console.log('Form onsubmit triggered - checking if Check button was clicked');
                        if (window.pyodideCheckClicked) {
                            window.pyodideCheckClicked = false;
                            return false;  // Don't submit
                        }
                        // Block all submissions for now
                        return false;
                    };
                }
                
                // SECOND: Find and hook the Check button
                const allButtons = document.querySelectorAll('button');
                let checkButtonFound = false;
                
                allButtons.forEach((btn) => {
                    const btnText = btn.textContent.trim();
                    
                    if (btnText === 'Check') {
                        console.log('✓ Found Check button');
                        checkButtonFound = true;
                        
                        // Replace onclick completely
                        btn.onclick = null;
                        
                        // Add our handler in capture phase (fires first)
                        btn.addEventListener('click', (e) => {
                            console.log('>> Check button clicked, preventing form submission');
                            
                            // Mark that Check was clicked
                            window.pyodideCheckClicked = true;
                            
                            // Stop ALL event propagation
                            e.preventDefault();
                            e.stopPropagation();
                            e.stopImmediatePropagation();
                            
                            // Prevent any default button behavior
                            if (form) {
                                form.onsubmit = () => false;
                            }
                            
                            // Find code textarea
                            const textarea = form ? form.querySelector('textarea') : null;
                            if (!textarea) {
                                alert('Error: Could not find code editor');
                                return false;
                            }
                            
                            const code = textarea.value;
                            if (!code || !code.trim()) {
                                alert('Please write some code first.');
                                return false;
                            }
                            
                            // Execute code
                            executeCode(code, form || document.body);
                            
                            return false;
                        }, true);  // Capture phase
                        
                        console.log('✓ Check button hooked successfully');
                    }
                });
                
                if (!checkButtonFound) {
                    console.warn('Check button not found, will retry...');
                    setTimeout(setupCheckButton, 2000);
                }
            } catch (error) {
                console.error('Error in setupCheckButton:', error);
            }
        }, 1000);
    }
    
    // Execute Python code using Pyodide
    async function executeCode(code, form) {
        try {
            console.log('Initializing Pyodide environment...');
            const pyodide = await initPyodide();
            
            console.log('Executing Python code...');
            
            // Execute the code and capture output
            let output;
            try {
                output = await pyodide.runPythonAsync(code);
            } catch (execError) {
                output = 'ERROR: ' + execError.toString();
            }
            
            const outputStr = String(output || '');
            console.log('Execution complete. Output length:', outputStr.length);
            
            // Show results in the page
            showResults(outputStr, form);
            
        } catch (error) {
            console.error('Execution failed:', error);
            showError(error.message + '\n\n' + error.stack, form);
        }
    }
    
    // Display results on the same page
    function showResults(output, form) {
        console.log('Showing results...');
        
        // Find or create results container
        let resultsContainer = document.getElementById('pyodide-results');
        if (!resultsContainer) {
            resultsContainer = document.createElement('div');
            resultsContainer.id = 'pyodide-results';
            form.appendChild(resultsContainer);
        }
        
        const safeOutput = escapeHtml(output.substring(0, 2000));
        
        resultsContainer.innerHTML = `
            <div style="margin: 20px 0; padding: 15px; background: #d4edda; border: 2px solid #28a745; border-radius: 5px;">
                <div style="color: #155724; font-weight: bold; font-size: 14px; margin-bottom: 10px;">
                    ✓ Code Executed Successfully (Pyodide)
                </div>
                <pre style="background: #f8f9fa; color: #333; padding: 12px; border-radius: 4px; overflow-x: auto; max-height: 250px; overflow-y: auto; border: 1px solid #c3e6cb; margin: 0; font-family: monospace; font-size: 12px; line-height: 1.4;">${safeOutput || '(no output)'}</pre>
            </div>
        `;
        
        // Scroll to results
        resultsContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    
    // Display errors on the same page
    function showError(errorMsg, form) {
        console.error('Showing error:', errorMsg);
        
        // Find or create results container
        let resultsContainer = document.getElementById('pyodide-results');
        if (!resultsContainer) {
            resultsContainer = document.createElement('div');
            resultsContainer.id = 'pyodide-results';
            form.appendChild(resultsContainer);
        }
        
        const safeError = escapeHtml(errorMsg.substring(0, 2000));
        
        resultsContainer.innerHTML = `
            <div style="margin: 20px 0; padding: 15px; background: #f8d7da; border: 2px solid #dc3545; border-radius: 5px;">
                <div style="color: #721c24; font-weight: bold; font-size: 14px; margin-bottom: 10px;">
                    ✗ Execution Error
                </div>
                <pre style="background: #f8f9fa; color: #721c24; padding: 12px; border-radius: 4px; overflow-x: auto; max-height: 250px; overflow-y: auto; border: 1px solid #f5c6cb; margin: 0; font-family: monospace; font-size: 12px; line-height: 1.4;">${safeError}</pre>
            </div>
        `;
        
        // Scroll to error
        resultsContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    
    // HTML escape function
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text || '').replace(/[&<>"']/g, m => map[m]);
    }
    
    // Start setup when page is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupCheckButton);
    } else {
        setupCheckButton();
    }
    
    console.log('✓ Pyodide Sandbox script loaded and ready');
})();
