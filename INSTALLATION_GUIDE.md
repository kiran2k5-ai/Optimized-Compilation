## 📋 Installation & Setup Guide - CodeRunner + Pyodide

**Complete step-by-step instructions for deploying the Pyodide integration**

---

## ✅ Pre-Installation Checklist

Before you begin, verify:

- [ ] Moodle 4.0 or higher is installed
- [ ] CodeRunner plugin is installed and working
- [ ] PHP 7.4 or higher is available
- [ ] You have admin access to Moodle
- [ ] XAMPP/Server is running
- [ ] Your browser supports WebAssembly

---

## 📦 Phase 1: File Installation

### Step 1.1: Locate CodeRunner Directory

```
Your Moodle Path → public/question/type/coderunner/
```

On Windows (XAMPP):
```
e:\moodel_xampp\htdocs\moodle\public\question\type\coderunner\
```

### Step 1.2: Copy Integration Files

Place these 4 NEW files in the CodeRunner directory:

1. **enable_pyodide.php** → Controls feature activation
2. **jobe_api_mock.php** → API compatibility layer
3. **pyodide_executor.js** → Browser execution engine
4. **setup_pyodide.php** → Initialization script

### Step 1.3: Update Existing File

Modify **renderer.php** with the provided changes:
- Inject pyodide executor script
- Add execution button logic
- Implement result submission

**Result:** Your directory now contains:
```
coderunner/
├── enable_pyodide.php          ← NEW
├── jobe_api_mock.php           ← NEW
├── pyodide_executor.js         ← NEW
├── renderer.php                ← MODIFIED
├── setup_pyodide.php           ← NEW
├── PYODIDE_INTEGRATION.md      ← Reference
├── DEVELOPER_REFERENCE.md      ← Reference
└── [other existing files...]
```

---

## 🔧 Phase 2: Configuration

### Step 2.1: Review Configuration

Open `enable_pyodide.php` and verify:

```php
// Should have:
define('USE_PYODIDE', true);                    // Enable feature
define('PYODIDE_VERSION', '0.23.0');           // Correct version
define('PYODIDE_TIMEOUT', 30);                  // Timeout in seconds
define('PYODIDE_MAX_OUTPUT', 1000000);         // Output limit in bytes
define('PYODIDE_DEBUG', false);                 // Disable in production
```

### Step 2.2: Customize if Needed

**Optional: Adjust timeout for slower systems**
```php
define('PYODIDE_TIMEOUT', 60); // Increase to 60s
```

**Optional: Enable debugging**
```php
define('PYODIDE_DEBUG', true); // For troubleshooting
```

---

## 🚀 Phase 3: Initialization

### Step 3.1: Run Setup Script

**Option A: Browser Access**

1. Start your Moodle server
2. Open browser and navigate to:
   ```
   http://localhost/question/type/coderunner/setup_pyodide.php
   ```
3. You should see:
   ```
   ========================================
   CodeRunner Pyodide Integration Setup
   ========================================
   
   [1] Loading Pyodide configuration...
   ✓ Configuration loaded
   
   [2] Verifying integration files...
   ✓ All files exist
   
   [3] Configuring Moodle...
   ✓ Configuration updated
   
   [4] Verifying database...
   ✓ All tables present
   
   Setup Complete! ✓
   ```

**Option B: Command Line** (Windows)

```bash
cd e:\moodel_xampp\htdocs\moodle\public\question\type\coderunner\
php setup_pyodide.php
```

### Step 3.2: Verify Setup Success

After setup, verify in Moodle:

1. Go to **Site Administration** → **Development** → **CodeRunner**
2. Look for setting: `use_local_pyodide = 1`
3. Verify status shows "Pyodide Integration Active"

---

## 🧪 Phase 4: Testing

### Step 4.1: Create Test Question

1. Go to **Question Bank**
2. Click **Create new question**
3. Select **CodeRunner**
4. Fill in details:
   - **Question Name:** "Hello World Test"
   - **Question Text:** "Write Python code to print 'Hello World'"
   - **Answer:** 
     ```python
     print("Hello World")
     ```
   - **Test Cases:** (at least one)
     - Input: (empty)
     - Output: `Hello World`

5. Click **Save changes**

### Step 4.2: Add to Quiz

1. Create a new **Quiz**
2. Click **Edit quiz**
3. Click **Add question from bank**
4. Select your "Hello World Test" question
5. Click **Add selected questions to the quiz**
6. Click **Done**

### Step 4.3: Test as Student

1. Access the quiz as a student (or use a test student account)
2. Attempt the question
3. Enter code in the code editor:
   ```python
   print("Hello World")
   ```
4. Click **Execute** (or similar button)
5. **Expected:** Code runs in browser, output appears
6. Click **Submit**
7. **Expected:** Results saved to Moodle

### Step 4.4: Verify in Browser Console

Open browser developer tools (F12) → Console tab:

Look for messages like:
```
[PyodideExecutor] Initializing Pyodide...
[PyodideExecutor] Loading from CDN...
[PyodideExecutor] Execution started
[PyodideExecutor] Result: success
[PyodideExecutor] Sent to server
```

---

## 📊 Phase 5: Verification

### Checklist: Everything Working?

- [ ] Setup script ran without errors
- [ ] Moodle shows Pyodide integration active
- [ ] Question created successfully
- [ ] Code executes in browser (not on server)
- [ ] Results display correctly
- [ ] Submission saves to Moodle
- [ ] Browser console shows no errors

### Common Verification Issues

**Issue:** Setup script shows file not found
- **Solution:** Verify all files are in correct directory

**Issue:** Browser shows blank screen
- **Solution:** Check F12 console for JavaScript errors

**Issue:** Code doesn't execute
- **Solution:** Enable PYODIDE_DEBUG and check console

**Issue:** Results not saving
- **Solution:** Verify Moodle database connectivity

---

## 🐛 Phase 6: Troubleshooting

### Symptom: "Pyodide is not defined"

**Cause:** CDN not accessible or JavaScript error

**Solution:**
1. Check internet connection
2. Verify browser supports WebAssembly
3. Check F12 Console for errors
4. Try different browser

### Symptom: Code takes forever to execute

**Cause:** Pyodide still loading on first run

**Expected Behavior:** First load takes 20-30 seconds
- **Solution:** Wait or refresh after first run

### Symptom: "Maximum call stack size exceeded"

**Cause:** Infinite recursion in code

**Expected Behavior:** Should show in stderr
- **Solution:** Check student code for infinite loops

### Symptom: "Module not found" for imports

**Cause:** External package not in stdlib

**Expected:** Only Python stdlib available
- **Solution:** Use only built-in Python modules

### Full Troubleshooting Guide

See: [PYODIDE_INTEGRATION.md](PYODIDE_INTEGRATION.md#troubleshooting)

---

## 🔐 Phase 7: Security Checklist

After successful setup, verify:

- [ ] PYODIDE_DEBUG is set to `false` in production
- [ ] File permissions are correct
- [ ] Enable/disable toggle works
- [ ] Test code execution limits
- [ ] Verify CORS headers are set
- [ ] Check Moodle logs for errors

---

## 🎓 Phase 8: Training Users

### For Instructors

1. Create sample CodeRunner questions
2. Test with different code types
3. Review auto-grading functionality
4. Configure point values
5. Set up quiz completion dates

**Important Notes:**
- Code executes locally in student browsers
- No Jobe server needed
- First load takes time (Pyodide)
- Results always saved to Moodle

### For Students

1. Access quiz with CodeRunner question
2. Write Python code in editor
3. Click "Execute" to test code
4. See results immediately
5. Click "Submit" to complete

**Important Notes:**
- Must have JavaScript enabled
- First attempt may take 20-30 seconds
- Can test code multiple times
- Only submit when ready

---

## 📞 Support Resources

### If Something Goes Wrong

1. **Check Logs:**
   - Site Admin → Reports → Logs
   - Filter by: qtype_coderunner

2. **Browser Console:**
   - Press F12
   - Go to Console tab
   - Look for error messages

3. **Enable Debug Mode:**
   - Set `PYODIDE_DEBUG = true`
   - Refresh page
   - Check console output

4. **Restart Everything:**
   ```bash
   # Stop XAMPP/server
   # Clear browser cache (Ctrl+Shift+Delete)
   # Start XAMPP/server
   # Refresh Moodle page
   ```

### Getting Help

- Review: PYODIDE_INTEGRATION.md
- Check: DEVELOPER_REFERENCE.md
- Test: setup_pyodide.php
- Console: F12 Developer Tools

---

## ✨ Success Indicators

You've successfully installed if:

✅ Students can write Python code in browser
✅ Code executes locally without server
✅ Results display immediately
✅ Submissions save to Moodle
✅ Grades calculated correctly
✅ No Jobe server needed
✅ Multiple attempts allowed
✅ Feedback shown to students

---

## 📋 Post-Installation Tasks

1. **Create Course Content:**
   - Add CodeRunner questions to lessons
   - Organize questions by topic
   - Create sample quizzes

2. **Configure Grading:**
   - Set point values
   - Configure feedback messages
   - Set display options

3. **Test Thoroughly:**
   - Test with various code types
   - Test error cases
   - Test with multiple students

4. **Monitor Usage:**
   - Check performance metrics
   - Review student submissions
   - Track debug logs

5. **Gather Feedback:**
   - Ask students about experience
   - Note any issues
   - Plan improvements

---

## 🔄 Maintenance & Updates

### Regular Checks

- [ ] Weekly: Review error logs
- [ ] Monthly: Test new questions
- [ ] Quarterly: Update Pyodide version
- [ ] Annually: Security audit

### Updating Pyodide

To update Pyodide version:

```php
// In enable_pyodide.php
define('PYODIDE_VERSION', '0.24.0'); // Change version
```

Then:
1. Clear browser cache
2. Test execution
3. Verify all works

---

## 📚 Documentation Map

- **PYODIDE_INTEGRATION.md** - Complete user manual
- **DEVELOPER_REFERENCE.md** - Technical details
- **This file** - Installation steps
- **setup_pyodide.php** - Initialization script

---

## ⏱️ Installation Timeline

| Phase | Task | Time |
|-------|------|------|
| 1 | Copy files | 5 min |
| 2 | Configure | 5 min |
| 3 | Run setup | 2 min |
| 4 | Create test question | 10 min |
| 5 | Verify works | 5 min |
| 6 | Troubleshooting | 5-30 min |
| 7 | Security check | 5 min |
| **Total** | **Full installation** | **30-60 min** |

---

## ✅ Final Checklist

Installation complete when:

- [ ] All files copied to correct location
- [ ] setup_pyodide.php executed successfully
- [ ] Test question created and works
- [ ] Student code executes in browser
- [ ] Results save to Moodle
- [ ] No errors in browser console
- [ ] Documentation reviewed
- [ ] Team trained on usage

---

**Ready to deploy!** 🚀

Your Moodle instance is now configured with CodeRunner + Pyodide integration.

For questions, see the documentation files or enable DEBUG mode for detailed logs.

---

*Last Updated: 2024*
*Version: 1.0*
*For Moodle 4.0+ with CodeRunner Plugin*
