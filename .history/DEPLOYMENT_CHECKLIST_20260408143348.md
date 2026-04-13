## ✅ FINAL DEPLOYMENT CHECKLIST - CodeRunner + Pyodide

**Use this checklist to ensure nothing is missed during deployment.**

---

## PHASE 1: PRE-DEPLOYMENT VERIFICATION

### System Requirements
- [ ] Moodle 4.0 or higher installed
- [ ] PHP 7.4 or higher running
- [ ] CodeRunner plugin already installed
- [ ] MySQL/MariaDB database working
- [ ] Write access to Moodle directories
- [ ] Admin account with site access

### Environment
- [ ] XAMPP/Server running (or equivalent)
- [ ] Moodle accessible at localhost/moodle
- [ ] Database credentials verified
- [ ] File backup created (before making changes)

### Browser Testing
- [ ] Chrome/Chromium available
- [ ] Firefox available
- [ ] Safari available (if on Mac)
- [ ] Browser developer tools working (F12 opens)

---

## PHASE 2: FILE PLACEMENT

### Core CodeRunner Files
Location: `public/question/type/coderunner/`

- [ ] `enable_pyodide.php` - PRESENT (verify size > 4KB)
- [ ] `jobe_api_mock.php` - PRESENT (verify size > 5KB)
- [ ] `pyodide_executor.js` - PRESENT (verify size > 8KB)
- [ ] `setup_pyodide.php` - PRESENT (verify size > 4KB)
- [ ] `renderer.php` - PRESENT (verify has Pyodide loading code)

### NEW Integration Files
- [ ] `lib_integration.php` - COPIED (verify size > 8KB)
- [ ] `DEVELOPER_REFERENCE.md` - PRESENT (verify size > 20KB)

### Testing Files
Location: `public/question/type/coderunner/tests/`
- [ ] Directory created (mkdir if needed)
- [ ] `integration_test.php` - COPIED (verify size > 5KB)

### Example Files
Location: `public/question/type/coderunner/examples/`
- [ ] Directory created (mkdir if needed)
- [ ] `sample_questions.sql` - COPIED (verify size > 2KB)

### Documentation Files
Location: Moodle root (`/`)
- [ ] `INSTALLATION_GUIDE.md` - PRESENT
- [ ] `QUICK_START.md` - PRESENT
- [ ] `DELIVERABLES.md` - PRESENT
- [ ] `MANIFEST.md` - PRESENT
- [ ] `IMPLEMENTATION_COMPLETE.md` - PRESENT
- [ ] `COMPLETION_SUMMARY.md` - PRESENT

### File Permissions (Linux/Mac)
- [ ] All PHP files: 644 (`chmod 644 *.php`)
- [ ] All JS files: 644 (`chmod 644 *.js`)
- [ ] All directories: 755 (`chmod 755 directories`)

**Windows Users:** Skip permission checks (NTFS handles automatically)

---

## PHASE 3: MOODLE CONFIGURATION

### Admin Settings
- [ ] Login to Moodle as Admin
- [ ] Navigate: Site Administration → Plugins → Question Types → CodeRunner
- [ ] Locate Pyodide Settings section

### Enable Pyodide Integration
- [ ] Setting: "Enable Local Pyodide Execution"
- [ ] Value: ON/Checked ✓
- [ ] Setting: "Pyodide Version"
- [ ] Value: 0.23.0 ✓
- [ ] Setting: "Execution Timeout"
- [ ] Value: 30 (seconds) ✓
- [ ] Setting: "Maximum Output Size"
- [ ] Value: 1000000 (bytes) ✓

### Debug Settings (Optional - Disable for Production)
- [ ] Setting: "Debug Mode"
- [ ] Value: OFF (unchecked) ✓

### Save Configuration
- [ ] Click "Save Changes" button
- [ ] Verify success message appears
- [ ] Settings persist (refresh page to verify)

---

## PHASE 4: DATABASE VERIFICATION

### Check Database Tables
| Action | Command | Expected Result |
|--------|---------|-----------------|
| Questions Table | `SELECT COUNT(*) FROM mdl_question;` | Returns number > 0 |
| Quiz Table | `SELECT COUNT(*) FROM mdl_quiz;` | Returns number > 0 |
| Attempts Table | `SELECT COUNT(*) FROM mdl_quiz_attempts;` | May be 0 (normal) |

### Import Sample Questions
- [ ] Open phpMyAdmin or MySQL client
- [ ] Select Moodle database
- [ ] Import: `examples/sample_questions.sql`
- [ ] Verify: No error messages
- [ ] Result: 5 new questions appear in Question Bank

**OR** via Moodle UI:
- [ ] Admin → Course administration → Question Bank
- [ ] New questions should be available for use

---

## PHASE 5: INTEGRATION TESTING

### Run Automated Tests
1. [ ] Navigate: `http://localhost/question/type/coderunner/tests/integration_test.php`
2. [ ] Page loads (wait 5-10 seconds)
3. [ ] Verify test output appears

### Test Results Expected
```
[TEST 1] Verifying required files...
  ✓ enable_pyodide.php
  ✓ jobe_api_mock.php
  ✓ pyodide_executor.js
  ✓ setup_pyodide.php
  ✓ renderer.php
  ✓ lib_integration.php

[TEST 2] Verifying Moodle configuration...
  ✓ Pyodide enabled = 1
  ✓ Pyodide version = 0.23.0
  ✓ Execution timeout = 30
  ✓ Max output size = 1000000

[TEST 3] Verifying database tables...
  ✓ Questions table exists
  ✓ Question Attempts table exists
  ✓ Quizzes table exists
  ✓ Quiz Attempts table exists

[TEST 4] Testing API Mock...
  ✓ jobe_api_mock.php loaded
  ✓ get_languages() returns Python
  ✓ run_code() returns valid response

[TEST 5] Testing Moodle integration...
  ✓ Moodle context detected
  ✓ User is logged in
  ✓ Admin access verified
  ✓ CodeRunner plugin installed

========================================
Test Results Summary
========================================
Total tests: 18
Passed: 18 ✓
Failed: 0 ✗

✓ All tests passed! Integration is ready.
```

### If Tests Fail
- [ ] Check console (F12) for JavaScript errors
- [ ] Verify all files were copied
- [ ] Check file permissions
- [ ] Review Moodle error logs
- [ ] Check database connectivity

---

## PHASE 6: SAMPLE QUESTION TESTING

### Create Test Quiz
1. [ ] Navigate: Course → Assessments → Create Quiz
2. [ ] Quiz Name: "Pyodide Test Quiz"
3. [ ] Description: "Testing local Python execution"
4. [ ] Save quiz

### Add Sample Question
1. [ ] Edit Quiz
2. [ ] Click "Add Question from Bank"
3. [ ] Select: "Hello World - Python" (first sample)
4. [ ] Add to quiz
5. [ ] Save

### Test as Student
1. [ ] Switch to Student role (or use test student account)
2. [ ] Access the quiz
3. [ ] Attempt the question

### Expected Behavior - First Attempt
- [ ] Code editor appears
- [ ] "Execute/Check" button visible
- [ ] Click Execute button
- [ ] Status: "Executing code..."
- [ ] Wait 15-30 seconds (Pyodide loading from CDN)
- [ ] Pyodide loads in browser (check Network tab)
- [ ] Output section shows code execution result
- [ ] Browser console (F12) shows: "[PyodideExecutor] ✓ Pyodide initialized"

### Expected Behavior - Second Attempt (Cached)
- [ ] Code editor appears
- [ ] Click Execute button
- [ ] Faster execution (~2 seconds)
- [ ] Output displays
- [ ] Demonstrates caching works

### Expected Result Display
- [ ] "stdout" section shows output from code
- [ ] Shows: "Hello World" (or equivalent)
- [ ] No JavaScript errors in console
- [ ] Submit button available

### Submit and Verify
- [ ] Click Submit
- [ ] Quiz saves response
- [ ] Grade recorded
- [ ] Result appears in gradebook

---

## PHASE 7: EDGE CASE TESTING

### Test 1: Runtime Error
- [ ] Code: `print(1/0)` (division by zero)
- [ ] Expected: Error message displays
- [ ] No crash or server error
- [ ] Error clearly shown to student
- [ ] Can retry

### Test 2: Timeout
- [ ] Code: `while True: pass` (infinite loop)
- [ ] Expected: Execution timeout after 30 seconds
- [ ] Graceful error message
- [ ] Can retry with different code

### Test 3: Large Output
- [ ] Code: `for i in range(10000): print(i)`
- [ ] Expected: Output truncated at limit (1MB)
- [ ] No memory issues
- [ ] Error message if truncated

### Test 4: Syntax Error
- [ ] Code: `print("hello` (missing quote)
- [ ] Expected: SyntaxError displayed
- [ ] Clear error message
- [ ] Can fix and retry

---

## PHASE 8: BROWSER COMPATIBILITY

### Test in Chrome/Edge
- [ ] Navigate: http://localhost/mod/quiz/view.php
- [ ] Select test quiz
- [ ] Execute code
- [ ] Code runs in browser
- [ ] Results display
- [ ] Grades save

### Test in Firefox
- [ ] Same steps as Chrome
- [ ] Verify same functionality
- [ ] Check for CORS issues
- [ ] Performance comparable

### Test in Safari (Mac)
- [ ] Same functionality
- [ ] Pyodide loads correctly
- [ ] No specific issues

### Mobile/Responsive Testing
- [ ] Access via mobile device
- [ ] Code editor accessible
- [ ] Button clickable on touch
- [ ] Output displays properly
- [ ] No layout broken

---

## PHASE 9: CONSOLE VERIFICATION

### Browser Console (F12 → Console)
- [ ] No red error messages
- [ ] Messages like: `[PyodideExecutor] ✓`
- [ ] No CORS warnings
- [ ] No undefined variable errors

### Expected Console Messages
```
[PyodideExecutor] Initializing Pyodide...
[PyodideExecutor] Loading from CDN...
[PyodideExecutor] Execution started
[PyodideExecutor] Result: success
[PyodideExecutor] Sent to server
```

### Moodle Logs
- [ ] Admin → Reports → Logs
- [ ] Filter by: qtype_coderunner
- [ ] No error entries
- [ ] Execution logged properly

---

## PHASE 10: PERFORMANCE VERIFICATION

### Metrics to Check
- [ ] First execution: 20-35 seconds (including Pyodide load)
- [ ] Second execution: 1-2 seconds (cached)
- [ ] Large output handling: <500ms
- [ ] Database save: <1 second
- [ ] No server CPU spike

### Performance Testing
- [ ] Use browser DevTools (F12 → Network)
- [ ] Monitor AJAX requests
- [ ] Check bandwidth usage
- [ ] Verify no memory leaks

---

## PHASE 11: DOCUMENTATION REVIEW

### Instructor Documentation
- [ ] Read: `QUICK_START.md`
- [ ] Read: Key sections of `INSTALLATION_GUIDE.md`
- [ ] Preview: `PYODIDE_INTEGRATION.md`
- [ ] Share with team

### Student Documentation
- [ ] Create simple guide: "How to Use CodeRunner"
- [ ] Include screenshots
- [ ] Explain: Write code → Click Execute → See results
- [ ] Distribute before deployment

### Developer Documentation
- [ ] Review: `DEVELOPER_REFERENCE.md`
- [ ] Understand: Architecture in `PYODIDE_INTEGRATION.md`
- [ ] Know: Location of all components
- [ ] Save for maintenance reference

---

## PHASE 12: SECURITY CHECK

### Code Security
- [ ] Input validation in place
- [ ] Output sanitized
- [ ] No SQL injection vulnerabilities
- [ ] No XSS vulnerabilities
- [ ] CSRF tokens present

### Configuration Security
- [ ] Debug mode OFF
- [ ] Permissions correct (644/755)
- [ ] Database credentials secure
- [ ] No sensitive data in logs

### Network Security
- [ ] HTTPS enabled (if in production)
- [ ] CORS headers properly set
- [ ] No external resources from untrusted sources
- [ ] CDN (jsDelivr) is trusted source

---

## PHASE 13: ROLLBACK PLAN

### If Anything Goes Wrong
- [ ] Backup exists: [Verify location]
- [ ] Rollback procedure documented: See `INSTALLATION_GUIDE.md` Phase 6 Troubleshooting
- [ ] Can quickly restore
- [ ] Know how to disable Pyodide

### Quick Disable (if needed)
1. Admin → Site Administration → CodeRunner
2. Setting: "Enable Local Pyodide Execution" = OFF
3. Falls back to Jobe server execution (if available)

---

## PHASE 14: TEAM TRAINING

### Instructors Training
- [ ] How to create CodeRunner questions
- [ ] Questions work the same, just on-device execution
- [ ] How to test their questions
- [ ] Where to find help

### IT Staff Training
- [ ] System architecture overview
- [ ] How to troubleshoot
- [ ] How to monitor
- [ ] How to update configuration

### Student Information
- [ ] What is Pyodide (optional)
- [ ] How to use CodeRunner
- [ ] Where to get help
- [ ] What to expect (execution time, offline capability)

---

## FINAL VERIFICATION CHECKLIST

Before going live, verify EVERY item:

### Essential Checks
- [ ] ✅ All files in place
- [ ] ✅ All tests pass (18/18)
- [ ] ✅ Configuration set correctly
- [ ] ✅ Sample questions work
- [ ] ✅ Code executes locally (not on server)
- [ ] ✅ Results save to Moodle
- [ ] ✅ Grades calculated
- [ ] ✅ No console errors
- [ ] ✅ Works in multiple browsers
- [ ] ✅ Error handling works

### Performance Checks
- [ ] ✅ First load: 20-35s
- [ ] ✅ Cached loads: 1-2s
- [ ] ✅ No timeouts
- [ ] ✅ No memory issues
- [ ] ✅ Server not overloaded

### Security Checks
- [ ] ✅ Input validation working
- [ ] ✅ Output sanitized
- [ ] ✅ Permissions correct
- [ ] ✅ Debug mode off
- [ ] ✅ No security warnings

### Rollback Preparedness
- [ ] ✅ Backup created
- [ ] ✅ Rollback procedure known
- [ ] ✅ Quick disable mechanism tested
- [ ] ✅ Support team briefed

---

## ✅ DEPLOYMENT APPROVED

When ALL items above are checked:

```
Status: ✅ READY FOR PRODUCTION DEPLOYMENT
Date: _________________
Approved By: _________________
```

**You are now ready to enable for all students/courses.**

---

## 📞 AFTER DEPLOYMENT

### Week 1: Monitoring
- [ ] Check logs daily
- [ ] Monitor performance
- [ ] Gather user feedback
- [ ] Address any issues immediately

### Week 2-4: Optimization
- [ ] Adjust configuration if needed
- [ ] Collect usage metrics
- [ ] Identify common issues
- [ ] Document solutions

### Ongoing: Maintenance
- [ ] Monthly: Review logs
- [ ] Quarterly: Update documentation
- [ ] As needed: Update Pyodide version
- [ ] Annually: Security audit

---

**When all boxes are checked, you are 100% ready for deployment. 🚀**

**Good luck!**
