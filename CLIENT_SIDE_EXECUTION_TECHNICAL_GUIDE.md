# Client-Side Code Execution Guide - Technical Implementation

## Current Status vs. Future Vision

### **Current State (Server-Side)**
```
Student submits code → Server receives full code → Server executes in container
→ Server generates results → Results sent to student
```
❌ Code traverses network  
❌ Code stored on server  
❌ Server resources required for every execution  

### **Future State (Client-Side) - TARGET ARCHITECTURE**
```
Student writes code locally → Code stays on client device
→ Client executes code locally → Client sends only RESULTS
→ Server validates results → Results scored and stored
```
✅ Code never leaves device  
✅ Instant execution  
✅ Server-side resources minimized  

---

## How Client-Side Code Execution Works

### **Method 1: WebAssembly (In-Browser Execution)**

**What is WebAssembly?**
- Binary format that runs in browser sandboxes
- Fast, secure code execution in the browser
- Supports C, C++, Rust, Go, and other languages
- Execution speed: Near-native performance

**How It Works:**
```
1. Student writes C/C++/Rust code in browser editor
2. Code is compiled to WebAssembly (.wasm) format
3. Browser runs the WASM binary in isolated sandbox
4. Test cases execute locally in browser
5. Results returned to Moodle server (NOT the code)
```

**Supported Languages via WebAssembly:**
- ✅ C/C++ (via Emscripten compiler)
- ✅ Rust (compiles natively to WASM)
- ✅ Go (WASM target support)
- ✅ Python (via Pyodide - see next section)

**Example Implementation:**
```javascript
// Client-side code execution with Emscripten
async function compileAndRunC(sourceCode) {
    // 1. Compile C code to WebAssembly
    const wasmModule = await emscripten.compile(sourceCode);
    
    // 2. Create WASM instance in browser sandbox
    const instance = new WebAssembly.Instance(wasmModule);
    
    // 3. Run the code locally
    const result = instance.exports.main();
    
    // 4. Send only the result to server
    await submitResults({
        testsPassed: result.passed,
        testsFailed: result.failed,
        output: result.output,
        executionTime: result.time
    });
}
```

**Browser Sandbox Protection:**
- WASM runs in isolated memory space
- Cannot access file system without permission
- Cannot make network requests (except controlled APIs)
- Limited CPU/memory usage
- Timeout enforcement

---

### **Method 2: Pyodide (Python in Browser)**

**What is Pyodide?**
- Full Python runtime compiled to WebAssembly
- Runs Python code directly in browser
- Includes popular libraries (NumPy, Pandas, etc.)
- File I/O in virtual filesystem

**How It Works:**
```
1. Student writes Python code in browser
2. Pyodide runtime (already in browser) executes code
3. Code runs in WASM sandbox
4. Test cases verified locally
5. Results sent to server
```

**Example Implementation:**
```javascript
// Python execution with Pyodide
async function runPythonCode(pythonCode, testCases) {
    // Load Pyodide (one-time)
    const pyodide = await loadPyodide();
    
    // Execute student's Python code
    try {
        pyodide.runPython(pythonCode);
        
        // Run test cases locally
        const results = pyodide.runPython(`
            results = []
            for test in tests:
                try:
                    result = eval(student_function(test['input']))
                    results.append({
                        'passed': result == test['expected'],
                        'output': str(result)
                    })
                except Exception as e:
                    results.append({'passed': False, 'error': str(e)})
            results
        `);
        
        // Send results (not code) to server
        await submitResults(results);
    } catch (error) {
        await submitResults({ error: error.message });
    }
}
```

**Available Libraries:**
- NumPy, Pandas, Matplotlib
- Scikit-learn for ML
- Pillow for image processing
- Regular expressions, JSON, etc.

---

### **Method 3: Local Node.js (JavaScript/Node Backend)**

**How It Works:**
```
1. Student installs Node.js locally (one-time)
2. Browser connects to local Node server (localhost:3000)
3. JavaScript code runs in local Node process
4. Test cases execute locally
5. Results sent to Moodle server
```

**Example Implementation:**
```javascript
// Client-side: Connect to local Node server
async function runJavaScriptCode(jsCode, testCases) {
    try {
        // Connect to local Node.js server
        const response = await fetch('http://localhost:3000/execute', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                code: jsCode,
                tests: testCases
            })
        });
        
        const results = await response.json();
        
        // Send results to Moodle server
        await submitToMoodle(results);
    } catch (error) {
        console.error('Local Node server not running');
    }
}

// Local Node.js server (runs on student's machine)
app.post('/execute', (req, res) => {
    const { code, tests } = req.body;
    
    // Execute code in Node.js
    const results = [];
    for (const test of tests) {
        try {
            // Create function from code
            const func = new Function(code + `; return myFunction`);
            const result = func()(test.input);
            
            results.push({
                passed: result === test.expected,
                output: result
            });
        } catch (error) {
            results.push({
                passed: false,
                error: error.message
            });
        }
    }
    
    res.json(results);
});
```

**Advantages:**
- Native Node.js performance
- Full access to npm modules
- Can run complex backend code
- Ideal for full-stack development practice

---

### **Method 4: Docker Desktop (Local Containers)**

**How It Works:**
```
1. Student installs Docker Desktop locally (one-time)
2. Challenge includes Docker image specification
3. Code runs in isolated container on student's machine
4. Full environment control (Ubuntu, libraries, etc.)
5. Results sent to server
```

**Example Implementation:**
```javascript
// Client-side: Communicate with local Docker
async function runDockerizedCode(codeFiles, dockerfile) {
    try {
        // Connect to local Docker daemon
        const docker = new Docker();
        
        // Build image from Dockerfile
        const image = await docker.buildImage(dockerfile);
        
        // Run container with code mounted
        const container = await docker.run(image, {
            volumes: { '/code': '/app' }
        });
        
        // Execute code in container
        const exec = await container.exec({
            Cmd: ['python', '/app/solution.py']
        });
        
        const output = await exec.output;
        
        // Send results to server
        await submitResults({
            output: output,
            exitCode: exec.exitCode
        });
        
        // Cleanup
        await container.remove();
    } catch (error) {
        console.error('Docker not available:', error);
    }
}
```

**Dockerfile Example:**
```dockerfile
FROM python:3.11

WORKDIR /app

# Install test framework
RUN pip install pytest

# Copy student code
COPY solution.py .

# Run tests
CMD ["pytest", "solution.py", "-v"]
```

**Full Isolation Benefits:**
- Complete environment control
- All necessary libraries pre-installed
- No interference with system
- Reproducible environments

---

## Comparison of Client-Side Execution Methods

| Method | Languages | Speed | Installation | Isolation | Offline |
|--------|-----------|-------|--------------|-----------|---------|
| **WebAssembly** | C, C++, Rust, Go | ⚡⚡⚡ Native | None | ✅ Browser Sandbox | ✅ Yes |
| **Pyodide** | Python | ⚡⚡⚡ Native | None | ✅ Browser Sandbox | ✅ Yes |
| **Node.js** | JavaScript | ⚡⚡⚡ Native | Required | ⚠️ Process level | ✅ Yes |
| **Docker** | All Languages | ⚡⚡ Good | Required | ✅ Container | ✅ Yes |

---

## Server-Side Responsibilities (Minimal)

When code runs client-side, the server only handles:

```php
// 1. Receive results (not code)
POST /api/submit-results
{
    "testsPassed": 8,
    "testsFailed": 2,
    "executionTime": 0.234,
    "output": "test output...",
    "submission_id": "sub_12345"
}

// 2. Validate results
$isValid = validateResults($results, $expectedOutput);

// 3. Score submission
$grade = calculateGrade($results, $testCases);

// 4. Store only metadata
$submission->save([
    'grade' => $grade,
    'passed_tests' => $results['passed'],
    'submission_time' => now()
]);

// 5. Update analytics
$analytics->recordSubmission($studentId, $grade);
```

**Key Point:** Server NEVER stores or receives the actual source code!

---

## Implementation Roadmap for Moodle CodeRunner

### **Phase 1: WebAssembly + Pyodide (Browser-Based)**
```
Timeline: Months 1-2
Deliverable: Run C/C++, Python, Rust in browser
- Integrate Emscripten for C/C++ compilation
- Add Pyodide for Python execution
- No installation required for students
- Works offline completely
```

### **Phase 2: Node.js Integration**
```
Timeline: Months 3-4
Deliverable: JavaScript/Node.js execution
- Local Node.js server communication
- Support for npm modules
- Backend code execution practice
- Optional installation
```

### **Phase 3: Docker Desktop Support**
```
Timeline: Months 5-6
Deliverable: Full containerized execution
- Docker Desktop integration
- Support for all programming languages
- Complete environment control
- Advanced scenarios (databases, services)
```

### **Phase 4: Hybrid Mode**
```
Timeline: Months 7-8
Deliverable: Smart execution routing
- Auto-select best execution method
- Fallback chains (WASM → Node → Docker)
- Optimal performance per language
- Seamless user experience
```

---

## Security Model: Client-Side Execution

### **How Code Stays Private:**

**1. Network Level**
```
✓ Code never sent in network packets
✓ Only results/metadata transmitted
✓ HTTPS encryption for data in transit
✓ No packet inspection reveals code
```

**2. Browser Sandbox**
```
✓ WASM runs in isolated memory
✓ Cannot access file system
✓ Cannot make external requests
✓ Cannot modify other tabs/windows
```

**3. Local Process Level**
```
✓ Node.js process isolated
✓ Docker container completely isolated
✓ OS-level resource limits
✓ Timeout enforcement
```

**4. Result Verification**
```
✓ Server validates results match expected output
✓ Detects tampering via cryptographic signatures
✓ Compares actual vs. submitted results
✓ Flags suspicious patterns
```

### **Attack Scenarios Prevented:**

| Attack | Prevention |
|--------|-----------|
| Server compromise → access to code | Code never on server |
| Man-in-the-middle → intercept code | Only results transmitted |
| Malware injection → modify student code | No code transmission = no injection |
| Database breach → steal code | Database doesn't store code |
| Unauthorized access → read code | Code stays on user's device |

---

## Practical Example: Complete Flow

### **Student Solves a Python Challenge**

**Step 1: Challenge Download**
```
Server → Browser:
{
    "challenge": {
        "title": "Fibonacci Sequence",
        "description": "Write a function...",
        "testCases": [
            {"input": "5", "expected": "0 1 1 2 3"},
            {"input": "8", "expected": "0 1 1 2 3 5 8 13"}
        ]
    }
}
```

**Step 2: Student Writes Code (Stays Local)**
```python
def fibonacci(n):
    a, b = 0, 1
    result = []
    for i in range(n):
        result.append(a)
        a, b = b, a + b
    return ' '.join(map(str, result))
```

**Step 3: Code Executes in Browser (Pyodide)**
```
Client (Browser):
1. Load Pyodide (Python runtime in WASM)
2. Execute: fibonacci(5) → "0 1 1 2 3"
3. Compare with expected: "0 1 1 2 3" ✓ PASS
4. Execute: fibonacci(8) → "0 1 1 2 3 5 8 13"
5. Compare with expected: "0 1 1 2 3 5 8 13" ✓ PASS
```

**Step 4: Submit Results Only (NOT Code)**
```javascript
// Only this is sent to server:
await submitResults({
    submission_id: "sub_abc123",
    testsPassed: 2,
    testsFailed: 0,
    executionTime: 0.045,
    timestamp: "2026-04-19T10:30:00Z"
    // ✓ Student's Python code is NOT included
});
```

**Step 5: Server Stores Metadata**
```sql
INSERT INTO submissions (
    student_id, challenge_id, 
    tests_passed, tests_failed, 
    score, submitted_at
) VALUES (
    145, 'ch_python_fib',
    2, 0,
    100, '2026-04-19 10:30:00'
);
-- ✓ No code stored!
```

---

## Why This Architecture Is Superior

### **Traditional Server-Side (Current)**
```
Pros: Simple architecture, centralized control
Cons: Privacy risk, scalability bottleneck, 
      server compromise = all code exposed,
      expensive infrastructure
```

### **Client-Side (Future)**
```
Pros: Privacy first, infinite scalability,
      code never at risk, minimal costs,
      works offline, instant feedback
Cons: Requires browser/local setup
      (one-time only)
```

---

## Implementation in Moodle CodeRunner

### **Updated config.php**
```php
// Enable client-side execution
$CFG->coderunner_execution_mode = 'client-side';

// Execution methods available
$CFG->coderunner_execution_methods = array(
    'wasm_python' => true,      // Python via Pyodide
    'wasm_c' => true,            // C/C++ via Emscripten
    'wasm_rust' => true,         // Rust native WASM
    'nodejs_local' => true,      // JavaScript/Node
    'docker_desktop' => true,    // Full containers
);

// Server-side result validation
$CFG->coderunner_validate_results = true;
$CFG->coderunner_store_metadata_only = true;
```

### **Challenge File Upload**
```
challenges/
├── fibonacci_python/
│   ├── challenge.json      # Problem statement
│   ├── test_cases.json     # Test cases (sent to client)
│   └── solution_sample.py  # Reference solution (server only)
```

### **Client-Side Execution Plugin**
```javascript
// In public/js/coderunner-client.js
class ClientCodeRunner {
    async execute(code, language, testCases) {
        switch(language) {
            case 'python':
                return await this.executePyodide(code, testCases);
            case 'c':
            case 'cpp':
                return await this.executeWasm(code, testCases);
            case 'javascript':
                return await this.executeNode(code, testCases);
            default:
                throw new Error('Language not supported');
        }
    }
    
    async executePyodide(code, testCases) {
        // Code execution in browser sandbox
    }
}
```

---

## Conclusion

**WebAssembly YES, but not the whole story.**

The project uses **multiple client-side execution methods**:
- ✅ **WebAssembly** for C/C++/Rust/Python (Pyodide)
- ✅ **Native JavaScript** in browser
- ✅ **Local Node.js** for advanced scenarios
- ✅ **Docker Desktop** for full isolation

This **hybrid approach** ensures:
- 🔒 **Privacy**: Code never leaves student's device
- ⚡ **Performance**: Instant code execution
- 💰 **Cost**: Minimal server infrastructure
- 📱 **Accessibility**: Works offline
- 🎓 **Educational Value**: Real-world scenarios

**The core principle**: "Your code, your device, your control."
