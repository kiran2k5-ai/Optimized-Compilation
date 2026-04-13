## Developer Reference - CodeRunner + Pyodide Integration

Quick reference for developers modifying or debugging the integration.

---

## 🔌 API Endpoints

### Execution Request

```javascript
POST /question/type/coderunner/jobe_api_mock.php

{
  "action": "run_spec",
  "run_spec": {
    "language_id": "python3",
    "sourcecode": "print('Hello')",
    "input": "",
    "expected_output": "Hello"
  }
}
```

### Execution Response

```json
{
  "ok": true,
  "result": {
    "status": 0,
    "stdout": "Hello\n",
    "stderr": "",
    "time_limit_exceeded": false,
    "output": "Hello\n"
  }
}
```

---

## 🧩 Code Structure

### Execution Flow

```
1. Student clicks "Execute"
   ↓
2. renderer.php triggers pyodide_executor.js
   ↓
3. JavaScript prepares execution request
   ↓
4. AJAX calls jobe_api_mock.php
   ↓
5. jobe_api_mock.php validates and routes
   ↓
6. Returns execution result
   ↓
7. renderer.php displays output
   ↓
8. Student clicks "Submit"
   ↓
9. Moodle saves to database
```

### File Dependencies

```
renderer.php
  ├── Uses: enable_pyodide.php (config)
  ├── Loads: pyodide_executor.js
  └── Calls: jobe_api_mock.php

jobe_api_mock.php
  ├── Uses: enable_pyodide.php (config)
  └── Returns: Jobe-compatible response

pyodide_executor.js
  ├── Loads: Pyodide from CDN
  ├── Calls: jobe_api_mock.php (AJAX)
  └── Returns: results to DOM

setup_pyodide.php
  └── Uses: enable_pyodide.php (config)
```

---

## 🐛 Debugging Guide

### Enable Debug Mode

Edit `enable_pyodide.php`:
```php
define('PYODIDE_DEBUG', true);
```

### Browser Console Debugging

Open browser console (F12 → Console):

```javascript
// Check if Pyodide is loaded
console.log(window.Pyodide);

// Check last execution result
console.log(window.lastExecutionResult);

// Check configuration
console.log(window.Pyodide_Config);
```

### Server-Side Debugging

Check Moodle logs:
```
Admin → Reports → Logs
Search: coderunner
Filter: Component: qtype_coderunner
```

### Common Debug Points

**In pyodide_executor.js:**
```javascript
// Line to add for debugging:
console.log('Execution:', {
  code: sourceCode,
  input: stdin,
  output: stdout,
  error: stderr,
  timeLimit: executionTime
});
```

**In jobe_api_mock.php:**
```php
// Add this to debug API calls:
error_log('PYODIDE DEBUG: ' . json_encode($request));
```

---

## 🔧 Modification Examples

### Change Pyodide Version

```php
// In enable_pyodide.php
define('PYODIDE_VERSION', '0.24.0'); // Changed from 0.23.0
```

### Add Library Support

```javascript
// In pyodide_executor.js, in init_pyodide():
// Add after Pyodide loads:
await window.Pyodide.loadPackage('numpy');  // Add numpy
await window.Pyodide.loadPackage('scipy');  // Add scipy
```

### Custom Execution Timeout

```php
// In enable_pyodide.php
define('PYODIDE_TIMEOUT', 60); // Increase to 60 seconds
```

### Enable Additional Logging

```javascript
// In pyodide_executor.js
const DEBUG = true;

function log(...args) {
  if (DEBUG) console.log('[PyodideExecutor]', ...args);
}
```

---

## 📊 Data Flow Diagram

```
┌─────────────────────┐
│  Student Code       │
└──────────┬──────────┘
           │
           ↓
┌─────────────────────────────────────┐
│  renderer.php                       │
│  - Display question                 │
│  - Inject execute button            │
│  - Show results                     │
└──────────┬──────────────────────────┘
           │ (Click Execute)
           ↓
┌─────────────────────────────────────┐
│  pyodide_executor.js                │
│  - Prepare code                     │
│  - Load Pyodide                     │
│  - Execute in browser               │
└──────────┬──────────────────────────┘
           │ (AJAX POST)
           ↓
┌─────────────────────────────────────┐
│  jobe_api_mock.php                  │
│  - Validate request                 │
│  - Parse execution result           │
│  - Return response                  │
└──────────┬──────────────────────────┘
           │ (JSON Response)
           ↓
┌─────────────────────────────────────┐
│  pyodide_executor.js (cont)         │
│  - Display output                   │
│  - Show errors                      │
│  - Enable submit button             │
└──────────┬──────────────────────────┘
           │ (Click Submit)
           ↓
┌─────────────────────────────────────┐
│  Moodle Grade System                │
│  - Save attempt                     │
│  - Check answer                     │
│  - Update scores                    │
└─────────────────────────────────────┘
```

---

## 🧪 Testing Scenarios

### Test Case 1: Simple Print Output
```python
# Code
print("Hello World")

# Expected
Hello World
```

### Test Case 2: Input Handling
```python
# Code
name = input("Enter name: ")
print(f"Hi {name}")

# Input
Alice

# Expected
Hi Alice
```

### Test Case 3: Error Handling
```python
# Code
x = 1 / 0

# Should catch
ZeroDivisionError
```

### Test Case 4: Complex Logic
```python
# Code
def fibonacci(n):
    if n <= 1:
        return n
    return fibonacci(n-1) + fibonacci(n-2)

print(fibonacci(10))

# Expected
55
```

---

## ⚙️ Configuration Reference

### Enable/Disable Pyodide

```php
// In enable_pyodide.php
define('USE_PYODIDE', true); // Set to false to disable

// OR in Moodle admin
set_config('use_local_pyodide', 1, 'qtype_coderunner');
```

### Performance Tuning

```php
// Execution timeout
define('PYODIDE_TIMEOUT', 30);      // seconds

// Output limits
define('PYODIDE_MAX_OUTPUT', 100000); // bytes

// Memory limits
define('PYODIDE_MAX_MEMORY', 512);   // MB

// Cache duration
define('PYODIDE_CACHE_TTL', 3600);   // seconds
```

### CDN Configuration

```javascript
// In pyodide_executor.js
const PYODIDE_CDN = 'https://cdn.jsdelivr.net/pyodide/v0.23.0/full/';
```

---

## 🔄 Error Handling

### Common Errors & Solutions

| Error | Cause | Solution |
|-------|-------|----------|
| Pyodide undefined | CDN not loaded | Check network, refresh browser |
| CORS error | CDN blocked | Whitelist CDN in CORS policy |
| Timeout | Long execution | Increase PYODIDE_TIMEOUT |
| Memory error | Large output | Increase PYODIDE_MAX_OUTPUT |
| Module not found | Missing library | Load with Pyodide.loadPackage() |

### Exception Handling

```javascript
try {
  result = await executePyodide(code);
} catch (error) {
  console.error('Execution failed:', error);
  displayError(error.message);
}
```

---

## 📝 Logging & Monitoring

### Enable Event Tracking

```php
// In jobe_api_mock.php
if (PYODIDE_DEBUG) {
  error_log('Request received: ' . time());
  error_log('Language: ' . $language_id);
  error_log('Code length: ' . strlen($source_code));
}
```

### Monitor Performance

```javascript
const startTime = performance.now();
// ... execution ...
const endTime = performance.now();
console.log(`Execution time: ${endTime - startTime}ms`);
```

---

## 🚀 Performance Optimization

### Caching Strategy

```javascript
// Cache Pyodide after first load
if (window.PyodideLoaded) {
  // Skip loading, use cached version
} else {
  // First time: load Pyodide
  await loadPyodide();
  window.PyodideLoaded = true;
}
```

### Output Buffering

```javascript
// Limit output size
const MAX_OUTPUT_LINES = 1000;
let outputLines = 0;
stdout = stdout.split('\n')
  .slice(0, MAX_OUTPUT_LINES)
  .join('\n');
```

---

## 🔐 Security Hardening

### Input Validation

```php
// In jobe_api_mock.php
if (!is_string($request['run_spec']['sourcecode'])) {
  return error_response('Invalid code');
}

if (strlen($request['run_spec']['sourcecode']) > 1000000) {
  return error_response('Code too large');
}
```

### Output Sanitization

```php
$output = htmlspecialchars($result['stdout']);
$output = substr($output, 0, PYODIDE_MAX_OUTPUT);
```

---

## 📚 API Reference

### RunSpec Interface

```php
class RunSpec {
  public $language_id;      // 'python3'
  public $sourcecode;       // Student code
  public $input;            // stdin
  public $expected_output;  // For autograding
  public $timeout;          // Execution timeout
}
```

### ExecutionResult Interface

```php
class ExecutionResult {
  public $status;              // 0=success, non-zero=error
  public $stdout;              // Program output
  public $stderr;              // Error messages
  public $time_limit_exceeded; // Boolean
  public $signal;              // Signal number if killed
}
```

---

## 🔗 External Resources

- **Pyodide Docs:** https://pyodide.org/en/stable/
- **Moodle API:** https://docs.moodle.org/dev/
- **WebAssembly:** https://webassembly.org/
- **JavaScript Fetch API:** https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API

---

## 📋 Checklist for New Developers

- [ ] Read PYODIDE_INTEGRATION.md
- [ ] Review file architecture above
- [ ] Test in browser console (F12)
- [ ] Debug with PYODIDE_DEBUG=true
- [ ] Test error scenarios
- [ ] Check performance with profiler
- [ ] Verify security validations
- [ ] Update version numbers when modifying

---

**Last Updated:** 2024
**Version:** 1.0
