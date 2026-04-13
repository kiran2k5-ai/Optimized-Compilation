/**
 * Pyodide Check Button Handler
 * Intercepts the check button and prevents server submission
 * Executes code in Pyodide instead
 */

import * as PyodideExecutor from './pyodide_executor';

export const initPyodideCheckButton = async function(questionId) {
    console.log('Initializing Pyodide check button for question:', questionId);

    try {
        // Initialize Pyodide
        await PyodideExecutor.initPyodide();

        // Find the check/submit button
        const submitButton = document.querySelector('button[name*="submit"]') ||
                             document.querySelector('button.submit') ||
                             document.querySelector('button[value="1"]');

        if (!submitButton) {
            console.warn('Submit button not found for question:', questionId);
            return;
        }

        // Find the answer textarea
        const answerTextarea = document.querySelector('textarea[name*="answer"]');

        if (!answerTextarea) {
            console.warn('Answer textarea not found for question:', questionId);
            return;
        }

        // Find the form
        const form = submitButton.closest('form');

        // Get test cases
        const testCases = PyodideExecutor.getTestCasesFromDOM(questionId);

        if (testCases.length === 0) {
            console.warn('No test cases found');
            return;
        }

        console.log(`Found ${testCases.length} test cases, ready to execute with Pyodide`);

        /**
         * Override the submit button click handler
         */
        submitButton.addEventListener('click', async function(e) {
            e.preventDefault();
            e.stopPropagation();

            console.log('Check button clicked - using Pyodide execution');

            // Show loading message
            const originalText = submitButton.textContent;
            submitButton.disabled = true;
            submitButton.textContent = 'Executing with Pyodide...';

            try {
                // Get student code
                const studentCode = answerTextarea.value;

                if (!studentCode.trim()) {
                    alert('Please enter some code');
                    submitButton.textContent = originalText;
                    submitButton.disabled = false;
                    return;
                }

                // Execute with Pyodide (no server submission)
                const results = await PyodideExecutor.executePythonCode(
                    studentCode,
                    testCases
                );

                console.log('Execution results:', results);

                // Display results in UI
                const resultsContainer = document.querySelector('.coderunner-results') ||
                                        setupResultsContainer(questionId);

                PyodideExecutor.displayTestResults(results, resultsContainer.id);

                // Create feedback message
                if (results.success) {
                    const passMessage = results.allPassed ?
                        '✓ All tests passed! (Executed in browser with Pyodide)' :
                        '✗ Some tests failed (Executed in browser with Pyodide)';

                    showNotification(passMessage, results.allPassed ? 'success' : 'warning');
                } else {
                    showNotification(`✗ Error: ${results.error}`, 'danger');
                }

            } catch (error) {
                console.error('Pyodide execution error:', error);
                showNotification(`✗ Execution failed: ${error.message}`, 'danger');
            } finally {
                submitButton.textContent = originalText;
                submitButton.disabled = false;
            }

            // IMPORTANT: Do NOT submit the form (code never goes to server)
            return false;
        }, true);

        // Also prevent form submission directly
        if (form) {
            form.addEventListener('submit', function(e) {
                // Check if this is our button that triggered it
                const activeElement = document.activeElement;
                if (activeElement === submitButton) {
                    e.preventDefault();
                    console.log('Form submission blocked - using Pyodide only');
                    submitButton.click();
                }
            });
        }

        console.log('✓ Pyodide check button initialized successfully');

    } catch (error) {
        console.error('Failed to initialize Pyodide check button:', error);
        showNotification('Failed to initialize Pyodide: ' + error.message, 'danger');
    }
};

/**
 * Setup results container if it doesn't exist
 */
function setupResultsContainer(questionId) {
    let container = document.querySelector('.coderunner-results');

    if (!container) {
        container = document.createElement('div');
        container.id = 'pyodide-results-' + questionId;
        container.className = 'coderunner-results';

        // Insert after the answer textarea
        const textarea = document.querySelector('textarea[name*="answer"]');
        if (textarea) {
            textarea.parentNode.insertBefore(container, textarea.nextSibling);
        }
    }

    return container;
}

/**
 * Show notification to user
 */
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show`;
    notification.setAttribute('role', 'alert');
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    // Insert at top of page
    const container = document.querySelector('.main-content') ||
                      document.querySelector('main') ||
                      document.body;

    container.insertBefore(notification, container.firstChild);

    // Auto-remove after 5 seconds
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

/**
 * Prevent any form submission that might bypass Pyodide
 */
export const blockServerSubmission = function() {
    document.addEventListener('submit', function(e) {
        // Block all form submissions for CodeRunner questions
        if (e.target.querySelector('textarea[name*="answer"]')) {
            console.log('Form submission blocked - CodeRunner uses Pyodide');
            e.preventDefault();
            return false;
        }
    }, true);
};
