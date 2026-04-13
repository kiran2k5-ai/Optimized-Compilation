/**
 * Pyodide Quiz Integration
 * Executes Python code locally in the browser instead of sending to Jobe server
 */

console.log('=== PYODIDE QUIZ LOADED ===');

// Initialize Pyodide
let pyodideReady = false;
let pyodide = null;

async function initializePyodide() {
    try {
        console.log('Loading Pyodide from CDN...');
        pyodide = await loadPyodide({
            indexURL: 'https://cdn.jsdelivr.net/pyodide/v0.23.0/full/'
        });
        pyodideReady = true;
        console.log('✓ Pyodide initialized successfully');
    } catch (error) {
        console.error('✗ Failed to initialize Pyodide:', error);
    }
}

// Start loading Pyodide immediately
initializePyodide();

// Hook the Check button
function setupCheckButtonHook() {
    console.log('Setting up Check button hook...');
    
    // Find all buttons on the page
    const buttons = document.querySelectorAll('button, input[type="button"], input[type="submit"]');
    console.log(`Found ${buttons.length} buttons on page`);
    
    let checkButton = null;
    
    // Look for the Check button
    for (let btn of buttons) {
        const text = (btn.textContent || btn.value || '').trim().toLowerCase();
        console.log(`  Button: "${text}"`);
        
        if (text.includes('check') && !text.includes('checkbox')) {
            checkButton = btn;
            console.log('✓✓✓ FOUND CHECK BUTTON ✓✓✓');
            break;
        }
    }
    
    if (!checkButton) {
        console.warn('✗ Check button not found, trying MutationObserver');
        // Watch for dynamically added buttons
        const observer = new MutationObserver(() => {
            setTimeout(setupCheckButtonHook, 500);
        });
        observer.observe(document.body, { childList: true, subtree: true });
        return;
    }
    
    // Prevent form submission
    console.log('Blocking form submission...');
    const form = checkButton.closest('form');
    if (form) {
        form.onsubmit = (e) => {
            console.log('✓ Form submission blocked');
            e.preventDefault();
            return false;
        };
    }
    
    // Hook the Check button click
    checkButton.onclick = async (e) => {
        console.log('✓✓✓ CHECK BUTTON CLICKED ✓✓✓');
        e.preventDefault();
        e.stopPropagation();
        
        // Get the code from the textarea
        const codeTextarea = document.querySelector('textarea[name*="answer"], textarea[name*="code"]');
        if (!codeTextarea) {
            console.error('✗ Could not find code textarea');
            return false;
        }
        
        const code = codeTextarea.value;
        console.log('Code to execute:', code);
        
        // Run the code with Pyodide
        await executeCodeWithPyodide(code);
        
        return false;
    };
    
    console.log('✓ Check button hooked successfully');
}

// Execute code using Pyodide
async function executeCodeWithPyodide(code) {
    console.log('Executing code with Pyodide...');
    
    if (!pyodideReady) {
        console.log('Waiting for Pyodide to initialize...');
        // Wait for Pyodide
        await new Promise(resolve => {
            const checkReady = setInterval(() => {
                if (pyodideReady) {
                    clearInterval(checkReady);
                    resolve();
                }
            }, 100);
        });
    }
    
    try {
        console.log('Running code...');
        
        // Capture stdout
        let output = '';
        let originalLog = console.log;
        
        // Override console.log temporarily
        const libPython = pyodide.getattr('sys').stdout;
        
        // Execute the code
        try {
            const result = pyodide.runPython(code);
            output = pyodide.getattr('sys').stdout.getvalue ? pyodide.getattr('sys').stdout.getvalue() : '';
        } catch (error) {
            output = `Error: ${error.message}`;
        }
        
        console.log('Code executed. Output:', output);
        
        // Display results in feedback area
        showResults(output);
        
    } catch (error) {
        console.error('✗ Execution error:', error);
        showResults(`Error: ${error.message}`);
    }
}

// Display results on the page
function showResults(output) {
    console.log('Displaying results...');
    
    // Look for the feedback area or create one
    let feedbackArea = document.querySelector('.feedback, .results, [data-testid="feedback"]');
    
    if (!feedbackArea) {
        // Create a feedback div if it doesn't exist
        feedbackArea = document.createElement('div');
        feedbackArea.style.cssText = `
            margin-top: 20px;
            padding: 15px;
            background-color: #e8f4f8;
            border-left: 4px solid #0066cc;
            border-radius: 4px;
        `;
        
        const checkButton = document.querySelector('button:contains("Check"), input[value*="Check"]');
        if (checkButton) {
            checkButton.parentNode.insertBefore(feedbackArea, checkButton.nextSibling);
        } else {
            document.body.appendChild(feedbackArea);
        }
    }
    
    // Display output
    feedbackArea.innerHTML = `
        <h4>Pyodide Execution Result:</h4>
        <pre style="background-color: #f5f5f5; padding: 10px; border-radius: 3px; overflow-x: auto;">
${escapeHtml(output)}
        </pre>
    `;
    
    console.log('✓ Results displayed');
}

// Escape HTML to prevent XSS
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

// Wait for DOM to be ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupCheckButtonHook);
} else {
    setupCheckButtonHook();
}

console.log('=== PYODIDE QUIZ SETUP COMPLETE ===');
