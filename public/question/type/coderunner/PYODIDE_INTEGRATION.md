# CodeRunner + Pyodide Integration

## Overview

This integration enables **local code execution** in CodeRunner questions using **Pyodide**, eliminating the need for a Jobe server. Students can write and execute Python code directly in their browser.

## Features

✅ **Local Execution** - Code runs in the browser using Pyodide
✅ **No Jobe Server** - Reduces infrastructure requirements
✅ **Instant Feedback** - Immediate code execution results
✅ **Moodle Integration** - Results automatically saved
✅ **Browser-Based** - Works offline once Pyodide is loaded
✅ **Secure** - Code execution is sandboxed in the browser

## Architecture

```
┌─────────────────────────────────────────┐
│     Student Browser (Frontend)          │
├─────────────────────────────────────────┤
│                                         │
│  ┌──────────────────────────────────┐  │
│  │   CodeRunner Question Display    │  │
│  └──────────────────────────────────┘  │
│           ↓                             │
│  ┌──────────────────────────────────┐  │
│  │   Pyodide Python Runtime         │  │
│  │   (Browser-based execution)      │  │
│  └──────────────────────────────────┘  │
│           ↓                             │
│  ┌──────────────────────────────────┐  │
│  │   pyodide_executor.js            │  │
│  │   (Execution Manager)            │  │
│  └──────────────────────────────────┘  │
│           ↓                             │
│  ┌──────────────────────────────────┐  │
│  │   Moodle API                     │  │
│  │   (Result Submission)            │  │
│  └──────────────────────────────────┘  │
└─────────────────────────────────────────┘
           ↓ (AJAX)
┌─────────────────────────────────────────┐
│   Moodle Server (Backend)               │
├─────────────────────────────────────────┤
│   - Save Results to Database            │
│   - Verify Question Logic               │
│   - Generate Feedback                   │
└─────────────────────────────────────────┘
```

## Installation Steps

### 1. Copy Integration Files

Place the following files in your CodeRunner question type directory:

```
public/question/type/coderunner/
├── enable_pyodide.php         (NEW - Configuration)
├── jobe_api_mock.php          (NEW - API Wrapper)
├── pyodide_executor.js        (NEW - Browser Executor)
├── setup_pyodide.php          (NEW - Setup Script)
├── renderer.php               (MODIFIED - Question Display)
└── ... existing files
```

### 2. Run Setup Script (via browser or CLI)

**Option A: Via Browser**
```
http://localhost/question/type/coderunner/setup_pyodide.php
```

**Option B: Via CLI**
```bash
php setup_pyodide.php
```

### 3. Verify Configuration

After setup, check Moodle config:
- Site Administration → Development → Code runner Pyodide integration enabled

## File Descriptions

### enable_pyodide.php
Global configuration file that:
- Enables/disables Pyodide integration
- Configures Pyodide version (0.23.0)
- Sets execution ports and timeouts
- Defines supported languages (Python 3.11)

### jobe_api_mock.php
Mock Jobe API wrapper that:
- Intercepts Jobe API calls
- Routes to local Pyodide executor
- Maintains API compatibility
- Returns standardized responses

### pyodide_executor.js
Browser-side executor that:
- Loads Pyodide runtime
- Manages Python execution
- Handles stdin/stdout/stderr
- Reports execution results
- Communicates with Moodle

### renderer.php (Modified)
Question renderer that:
- Injects Pyodide executor script
- Provides execution interface
- Handles result submission
- Displays feedback

## Usage

### For Instructors

1. **Create a CodeRunner Question:**
   - Go to Question Bank
   - Create New → CodeRunner
   - Write question text and answer code
   - Set test cases

2. **Configure Execution (Leave Default):**
   - No need to change Jobe settings
   - Pyodide will auto-detect and activate
   - No server configuration needed

3. **Add to Quiz:**
   - Create quiz
   - Add CodeRunner question
   - Students can now work offline

### For Students

1. **Attempt Question:**
   - Open quiz
   - View CodeRunner question
   - Write Python code

2. **Execute Code:**
   - Click "Execute" or similar button
   - Code runs locally in browser
   - See results immediately

3. **Submit Response:**
   - Click "Submit" when ready
   - Results saved to Moodle
   - Receive feedback

## Configuration Parameters

Edit `enable_pyodide.php` to customize:

```php
// Pyodide Version
define('PYODIDE_VERSION', '0.23.0');

// Execution Timeout (seconds)
define('PYODIDE_TIMEOUT', 30);

// Maximum Output Size (bytes)
define('PYODIDE_MAX_OUTPUT', 1000000);

// Enable Debugging
define('PYODIDE_DEBUG', false);

// Supported Languages
define('PYODIDE_LANGUAGES', ['python3']);
```

## Supported Languages

Currently supported:
- **Python 3.11** - Full Pyodide environment

Future support:
- JavaScript (Pyodide extension)
- WASM-compiled languages

## Troubleshooting

### Issue: Code Doesn't Execute

**Solution:**
1. Check browser console (F12) for errors
2. Verify Pyodide CDN is accessible
3. Check `PYODIDE_DEBUG` is enabled
4. Review Moodle error logs

### Issue: Results Not Saving

**Solution:**
1. Verify AJAX is enabled in Moodle
2. Check network requests (F12 Network tab)
3. Ensure user has capability to attempt questions
4. Check quiz settings allow submissions

### Issue: Timeouts During Execution

**Solution:**
1. Increase `PYODIDE_TIMEOUT` value
2. Check system resources
3. Profile code for performance
4. Consider splitting into smaller lessons

### Issue: Memory Errors

**Solution:**
1. Reduce output capture size
2. Avoid infinite loops
3. Use generators for large datasets
4. Consider code structure

## Performance Considerations

| Factor | Impact | Optimization |
|--------|--------|--------------|
| Pyodide Load | First load ~15-30s | Cache after first load |
| Execution Speed | CPU dependent | Keep code simple |
| Output Size | Memory limited | Limit output capture |
| Network | Minimal for local exec | Still needed for results |

## Security Notes

✓ **Sandboxed Execution** - Code runs in browser sandbox
✓ **No Server Access** - Code cannot access Moodle files
✓ **Limited Scope** - Only Python standard library available
✓ **No Network** - Code cannot make external requests

⚠️ **Considerations:**
- Students can see other test cases
- No protection against local code modification
- Code timing can leak information

## Browser Compatibility

| Browser | Version | Status |
|---------|---------|--------|
| Chrome/Edge | 69+ | ✅ Full Support |
| Firefox | 65+ | ✅ Full Support |
| Safari | 14+ | ✅ Full Support |
| Opera | 56+ | ✅ Full Support |

## Limitations

❌ Cannot use external packages (only Python stdlib)
❌ Cannot make network requests
❌ Cannot access file system directly
❌ Limited to single-threaded execution
❌ Memory limited to browser heap

## Examples

### Example 1: Simple Calculator

```python
# Student enters:
def add(a, b):
    return a + b

# Test cases automatically run
# Results display instantly
```

### Example 2: List Operations

```python
# Student enters:
def remove_duplicates(lst):
    return list(set(lst))

# Output shown in browser
# No server communication until submit
```

## Integration Points

### Moodle Core

- **Question Banking** - Standard Moodle integration
- **Quiz Module** - Question attempts tracked
- **Grading** - Automatic feedback generation
- **Reports** - Performance analytics

### Third-Party

- **Pyodide** - Python runtime in browser
- **AJAX** - Result submission
- **Browser Storage** - Session caching

## Future Enhancements

🔮 Multiple language support (JavaScript, Java, etc.)
🔮 Persistent browser storage for drafts
🔮 Real-time collaboration features
🔮 Advanced debugging interface
🔮 Package management (pip integration)

## Support & Documentation

| Resource | Link |
|----------|------|
| Pyodide Docs | https://pyodide.org |
| Moodle Docs | https://docs.moodle.org |
| CodeRunner Docs | CodeRunner documentation |

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | 2024 | Initial release |
| - | - | Pyodide 0.23.0 integration |
| - | - | Jobe API compatibility |

## Author & License

Created for Moodle CodeRunner integration
Licensed under same license as Moodle

---

**Ready to use!** Execute `setup_pyodide.php` to initialize the integration.
