# Test Failures - Quick Reference Guide

## Current Test Status

```
API TESTS (3 files, 22 tests)
├─ jobe_api_mock_test.php          ❌ FAILED (7 tests)
├─ pyodide_api_test.php            ✅ PASSED (8 tests)
└─ ajax_endpoints_test.php         ❌ FAILED (7 tests)

FUNCTION TESTS (4 files, 31 tests)
├─ enable_pyodide_test.php         ✅ PASSED (8 tests)
├─ lib_integration_test.php        ✅ PASSED (7 tests)
├─ execution_test.php              ❌ FAILED (8 tests)
└─ database_test.php               ✅ PASSED (8 tests)

INTEGRATION TESTS (3 files, 24 tests)
├─ full_workflow_test.php          ❌ FAILED (8 tests)
├─ question_rendering_test.php     ✅ PASSED (8 tests)
└─ attempt_handling_test.php       ❌ FAILED (8 tests)

SUMMARY: 5 PASSED, 5 FAILED
```

---

## Root Causes (5 Issues)

| # | Issue | Symptom | Impact | Solution |
|---|-------|---------|--------|----------|
| 1 | Mock returns empty stdout | "Output not as expected" | 4 files fail | Smart output simulation |
| 2 | Wrong DB field names | "Missing field: quizid" | 2 tests fail | Use correct names |
| 3 | Empty result['stdout'] | Tests check for strings in empty output | 3+ files fail | Return simulated output |
| 4 | record_exists() wrong args | "Too few arguments" exception | 1 test fails | Use count_records() |
| 5 | Parameter order confusion | Functions called with wrong order | Some tests might fail | Verify parameter order |

---

## How to Fix (3 Options)

### **OPTION 1: Quick Fix (30 minutes)** ⚡
Apply targeted patches to existing code:

**What:** Make mock smarter about output
**Files:** jobe_api_mock.php, attempt_handling_test.php
**Result:** Should reach 70-80% pass rate
**Status:** Already partially done

### **OPTION 2: Complete Fix (1 hour)** ✓ RECOMMENDED
Fix everything systematically:

**Step 1:** Run `failure_analysis.php` to see real failures
**Step 2:** Fix jobe_api_mock.php output simulation
**Step 3:** Fix database field name references
**Step 4:** Fix record_exists() calls
**Step 5:** Run suite again
**Result:** Should reach 95%+ pass rate

### **OPTION 3: Redesign (2-3 hours)** 🔄
Restructure entire test framework:

**What:** Move from mocking to real integration tests
**Benefit:** Tests actual behavior, not simulation
**Cost:** More complex, slower tests
**Result:** 100% reliable but takes longer to run

---

## What to Do RIGHT NOW

```bash
# Step 1: Analyze failures (shows real errors)
cd e:\moodel_xampp\htdocs\moodle\tests_scripts
e:\moodel_xampp\php\php.exe failure_analysis.php

# Step 2: Show me the OUTPUT
# (Copy-paste the failure_analysis.php output here)

# Step 3: I'll apply targeted fixes
```

---

## The 5 Failing Tests Explained

### ❌ **jobe_api_mock_test.php** (7 tests)
```
Why: Mock returns empty output
     Tests check: if output contains "Hello, World!"
     Mock returns: stdout = ""
     Result: All tests fail

Solution: Make mock detect code and return simulated output
```

### ❌ **ajax_endpoints_test.php** (7 tests)
```
Why: Same as above + response format issues
     Tests expect specific response structure
     Mock returns incomplete responses

Solution: Add complete response structure + output simulation
```

### ❌ **execution_test.php** (8 tests)
```
Why: Tests verify code output
     e.g., "x+y should print 50"
     Mock doesn't calculate, just returns empty

Solution: Parse code, simulate execution, return output
```

### ❌ **full_workflow_test.php** (8 tests)
```
Why: Depends on jobe_api_mock.php
     If mock fails, workflow fails
     Pipeline: get_languages → submit code → execute

Solution: Fix underlying mock first
```

### ❌ **attempt_handling_test.php** (8 tests)
```
Why: Multiple issues:
     1. Field name mismatch (quizid vs quiz) - FIXED
     2. record_exists() argument count - FIXED
     3. Some database queries may still fail

Solution: Verify field names match actual database
```

---

## Verification Checklist

Before we claim "fixed", verify:

- [ ] Run `failure_analysis.php` → Shows 0 issues
- [ ] Run individual test files → All pass
- [ ] Run `run_all_tests.php` → 77/77 pass
- [ ] Check `reports/test_report.txt` → "System ready"
- [ ] Spot check one full execution flow manually

---

## Next Steps (Choose One)

**I recommend:** Run `failure_analysis.php` first, then tell me results.

Once I see actual errors from your system, I can:
1. Apply precise fixes
2. Show you exactly what changed
3. Get all tests passing

**Current blockers:** Don't yet see exact error messages from YOUR system.

---

## Questions to Answer

1. Can you run the `failure_analysis.php` script?
2. What errors does it show?
3. Which test file fails FIRST?
4. What's the exact error message?

With these answers, I can fix everything.
