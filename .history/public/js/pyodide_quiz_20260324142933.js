/**
 * Pyodide Sandbox for Moodle CodeRunner
 * Aggressive form hijacking to prevent submission to server
 */

(function() {
    'use strict';
    
    let pyodideLoaded = false;
    
    console.log('>> Pyodide script starting...');
    
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
                console.error('Failed to load Pyodide script from CDN');
                reject(new Error('Failed to load Pyodide from CDN'));
            };
            
            document.head.appendChild(script);
        });
    }
    
    // AGGRESSIVE: Block XMLHttpRequest (AJAX) submissions
    function blockAjaxSubmissions() {
        console.log('>> Blocking AJAX submissions...');
        const originalFetch = window.fetch;
        const originalXHR = window.XMLHttpRequest;
        
        // Override fetch
        window.fetch = function(...args) {
            const url = String(args[0] || '');
            if (url.includes('processattempt') || url.includes('response')) {
                console.warn('✗ BLOCKED AJAX fetch to:', url);
                return Promise.reject(new Error('Blocked by Pyodide sandbox'));
            }
            return originalFetch.apply(this, args);
        };
        
        // Override XMLHttpRequest
        window.XMLHttpRequest = function() {
            const xhr = new originalXHR();
            const originalOpen = xhr.open;
            
            xhr.open = function(method, url, ...args) {
                if (url.includes('processattempt') || url.includes('response')) {
                    console.warn('✗ BLOCKED XHR', method, 'to:', url);
                    return;
                }
                return originalOpen.apply(this, [method, url, ...args]);
            };
            
            return xhr;
        };
        
        console.log('✓ AJAX blocking enabled');
    }
    
    // Find and hook the Check button with multiple methods
    function setupCheckButton() {
        console.log('>> Looking for Check button...');
        
        // Immediately try to find and hook
        const buttons = document.querySelectorAll('button, input[type="button"], input[type="submit"]');
        console.log('Found', buttons.length, 'buttons on page');
        
        let found = false;
        
        buttons.forEach((btn, index) => {
            const btnText = (btn.textContent || btn.value || '').trim();
            const btnClass = btn.className || '';
            const btnId = btn.id || '';
            
            console.log(`  Button ${index}: "${btnText}"`);
            
            // Look for Check button
            if (btnText === 'Check' || btnClass.includes('check') || btnId.includes('check')) {
                console.log('✓✓✓ FOUND CHECK BUTTON! ✓✓✓');
                found = true;
                hookCheckButton(btn);
            }
        });
        
        if (!found) {
            console.warn('Check button not found, will watch for it...');
            // Use MutationObserver to watch for dynamically added button
            const observer = new MutationObserver(() => {
                const btns = document.querySelectorAll('button');
                for (let btn of btns) {
                    if (btn.textContent.trim() === 'Check' && !btn.dataset.pyodideHooked) {
                        console.log('✓ Check button found via MutationObserver');
                        hookCheckButton(btn);
                        observer.disconnect();
                    }
                }
            });
            observer.observe(document.body, { childList: true, subtree: true });
        }
    }
    
    // Hook a specific Check button
    function hookCheckButton(btn) {
        console.log('>> Hooking Check button...');
        
        // Mark as hooked
        btn.dataset.pyodideHooked = 'true';
        
        // Remove any existing handlers
        btn.onclick = null;
        btn.onsubmit = null;
        btn.removeAttribute('onclick');
        btn.removeAttribute('onsubmit');
        
        // Replace with our handler
        btn.addEventListener('click', (e) => {
            console.log('✓✓✓ CHECK BUTTON CLICKED ✓✓✓');
            
            // Block everything
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            
            try {
                const form = btn.closest('form') || document.querySelector('form');
                if (!form) {
                    alert('Error: Could not find form');
                    return false;
                }
                
                const textarea = form.querySelector('textarea');
                if (!textarea) {
                    alert('Error: Could not find code editor');
                    return false;
                }
                
                const code = textarea.value;
                if (!code.trim()) {
                    alert('Please enter code');
                    return false;
                }
                
                console.log('>> Executing code...');
                executeCode(code, form);
                
            } catch (error) {
                console.error('Error:', error);
                alert('Error: ' + error.message);
            }
            
            return false;
        }, true);
        
        // Also block form submission
        const form = btn.closest('form');
        if (form) {
            console.log('>> Blocking form submission...');
            
            form.onsubmit = (e) => {
                console.warn('✗ Form submit blocked');
                e.preventDefault();
                return false;
            };
            
            form.addEventListener('submit', (e) => {
                console.warn('✗ Form submit event blocked');
                e.preventDefault();
                return false;
            }, true);
        }
        
        console.log('✓ Check button successfully hooked');
    }
    
    // Execute code with Pyodide
    async function executeCode(code, form) {
        try {
            const pyodide = await initPyodide();
            console.log('>> Running code...');
            
            let output;
            try {
                output = await pyodide.runPythonAsync(code);
            } catch (error) {
                output = 'ERROR: ' + error.toString();
            }
            
            showResults(String(output || ''), form);
        } catch (error) {
            console.error('Execution error:', error);
            showError(error.toString(), form);
        }
    }
    
    // Display results on page
    function showResults(output, form) {
        let resultsDiv = document.getElementById('pyodide-results-output');
        if (!resultsDiv) {
            resultsDiv = document.createElement('div');
            resultsDiv.id = 'pyodide-results-output';
            const textarea = form.querySelector('textarea');
            if (textarea) {
                textarea.insertAdjacentElement('afterend', resultsDiv);
            } else {
                form.appendChild(resultsDiv);
            }
        }
        
        const safeOutput = escapeHtml(output.substring(0, 2000));
        
        resultsDiv.innerHTML = `
            <div style="margin: 15px 0; padding: 12px; background: #d4edda; border: 2px solid #28a745; border-radius: 4px;">
                <div style="color: #155724; font-weight: bold; margin-bottom: 8px;">✓ Executed with Pyodide</div>
                <pre style="background: white; padding: 8px; border: 1px solid #c3e6cb; border-radius: 3px; overflow-x: auto; margin: 0; font-size: 11px;">${safeOutput}</pre>
            </div>
        `;
        
        resultsDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    
    // Show errors
    function showError(error, form) {
        let resultsDiv = document.getElementById('pyodide-results-output');
        if (!resultsDiv) {
            resultsDiv = document.createElement('div');
            resultsDiv.id = 'pyodide-results-output';
            const textarea = form.querySelector('textarea');
            if (textarea) {
                textarea.insertAdjacentElement('afterend', resultsDiv);
            } else {
                form.appendChild(resultsDiv);
            }
        }
        
        const safeError = escapeHtml(error.substring(0, 2000));
        
        resultsDiv.innerHTML = `
            <div style="margin: 15px 0; padding: 12px; background: #f8d7da; border: 2px solid #dc3545; border-radius: 4px;">
                <div style="color: #721c24; font-weight: bold; margin-bottom: 8px;">✗ Error</div>
                <pre style="background: white; padding: 8px; border: 1px solid #f5c6cb; border-radius: 3px; overflow-x: auto; margin: 0; font-size: 11px; color: #721c24;">${safeError}</pre>
            </div>
        `;
        
        resultsDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    
    function escapeHtml(text) {
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return String(text || '').replace(/[&<>"']/g, m => map[m]);
    }
    
    // Initialize
    function init() {
        console.log('>> Initializing Pyodide sandbox...');
        
        // Block AJAX first
        blockAjaxSubmissions();
        
        // Then find buttons
        setupCheckButton();
        
        // Also block form submit at document level
        document.addEventListener('submit', (e) => {
            if (e.target && e.target.tagName === 'FORM') {
                console.warn('✗ Document-level submit blocked');
                e.preventDefault();
            }
        }, true);
    }
    
    // Start immediately
    init();
    
    // Also try again after DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            console.log('>> DOMContentLoaded event');
            setupCheckButton();
        });
    }
    
    // Try again after a delay
    setTimeout(() => {
        console.log('>> Retry setup (1s)');
        setupCheckButton();
    }, 1000);
    
    console.log('✓ Pyodide Sandbox initialized');
})();
