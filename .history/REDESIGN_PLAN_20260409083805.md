# Complete Test Suite Redesign & Implementation Plan

## Phase 1: Fix Foundation Files

### 1. jobe_api_mock.php - REDESIGN
**Goal:** Make mock return realistic responses that match actual code execution

**Current Issues:**
- Returns empty stdout
- Tests can't verify output

**New Design:**
- Detect code patterns
- Return simulated output that matches what Python would return
- Match exact format tests expect

### 2. Database Helper Functions
**Goal:** Provide helper functions for consistent database access

**New Functions:**
- `get_quiz_attempt_fields()` - Returns actual field names
- `get_quiz_slot_fields()` - Returns actual field names
- `safe_db_query()` - Wraps DB calls with error handling

### 3. Test Base Class
**Goal:** Provide common setup and assertion methods

**New Class:**
```php
class TestBase {
    protected $moodle_root;
    protected $DB;
    
    public function setUp() { ... }
    public function assertFunctionExists($name) { ... }
    public function assertOutputContains($code, $expected) { ... }
}
```

---

## Phase 2: Fix Each Test File

### API Tests
1. **jobe_api_mock_test.php** ← FIX FIRST
   - Use new output simulation
   - Verify all 7 tests pass

2. **pyodide_api_test.php** ← ALREADY WORKING
   - Keep as-is
   - Verify all 8 tests pass

3. **ajax_endpoints_test.php** ← FIX
   - Update response format expectations
   - Use correct field names

### Function Tests
1. **enable_pyodide_test.php** ← ALREADY WORKING
   - Keep as-is
   - Verify all 8 tests pass

2. **lib_integration_test.php** ← ALREADY WORKING
   - Keep as-is
   - Verify all 7 tests pass

3. **execution_test.php** ← FIX
   - Use new output simulation
   - Verify code detection works

4. **database_test.php** ← ALREADY WORKING
   - Verify all 8 tests pass

### Integration Tests
1. **full_workflow_test.php** ← FIX
   - Depends on jobe_api_mock fixes
   - Should pass after Phase 1

2. **question_rendering_test.php** ← ALREADY WORKING
   - Keep as-is
   - Verify all 8 tests pass

3. **attempt_handling_test.php** ← FIX
   - Use correct DB field names
   - Use safe DB calls

---

## Phase 3: Create Test Utilities

### New Files:
1. `tests_scripts/lib/test_helpers.php`
   - Common helper functions
   - Database field mappings
   - Code simulation engine

2. `tests_scripts/lib/test_base.php`
   - Base test class
   - Setup/teardown
   - Common assertions

3. `tests_scripts/lib/mock_database.php`
   - Safe database operations
   - Field name resolution
   - Error handling

---

## Implementation Timeline

**Estimated:** 2-3 hours

**Breakdown:**
- Helper files: 20 min
- jobe_api_mock.php: 30 min
- Fix 5 failing test files: 1 hour
- Run & verify: 30 min
- Final adjustments: 30 min

---

## Success Criteria

✓ All 77 tests pass
✓ No database errors
✓ Mock simulates reality accurately
✓ All responses have required fields
✓ Field names match actual database
✓ Reports generate successfully

---

## Start Implementation

Ready to proceed with systematic redesign:
1. Create test utilities
2. Fix jobe_api_mock.php
3. Fix test files one by one
4. Verify each step
5. Run full suite

Shall I proceed?
