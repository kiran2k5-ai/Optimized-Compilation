# Test Scripts Directory

## Overview
This directory contains comprehensive test scripts to validate all API endpoints, functions, and integrations of the CodeRunner + Pyodide integration system.

## Structure

```
tests_scripts/
├── README.md                          (This file)
├── run_all_tests.php                  (Master test runner)
├── test_results.json                  (Test results output)
├── api_tests/
│   ├── jobe_api_mock_test.php         (Test Jobe API mock)
│   ├── pyodide_api_test.php           (Test Pyodide endpoints)
│   └── ajax_endpoints_test.php        (Test AJAX endpoints)
├── function_tests/
│   ├── enable_pyodide_test.php        (Test config functions)
│   ├── lib_integration_test.php       (Test Moodle integration)
│   ├── execution_test.php             (Test code execution)
│   └── database_test.php              (Test DB operations)
├── integration_tests/
│   ├── full_workflow_test.php         (End-to-end test)
│   ├── question_rendering_test.php    (Test question display)
│   └── attempt_handling_test.php      (Test quiz attempts)
└── reports/
    ├── test_report.txt                (Text report)
    ├── test_report.json               (JSON report)
    └── test_summary.html              (HTML report)
```

## How to Run Tests

### Option 1: Run All Tests at Once
```bash
php run_all_tests.php
```

### Option 2: Run Specific Test Suite
```bash
php api_tests/jobe_api_mock_test.php
php function_tests/lib_integration_test.php
php integration_tests/full_workflow_test.php
```

## Test Categories

### API Tests
- ✓ Jobe API mock endpoint tests
- ✓ Pyodide AJAX endpoint tests
- ✓ Response format validation
- ✓ Error handling

### Function Tests
- ✓ Configuration functions
- ✓ Moodle integration functions
- ✓ Code execution functions
- ✓ Database query functions
- ✓ Error handling

### Integration Tests
- ✓ End-to-end workflow
- ✓ Question rendering pipeline
- ✓ Quiz attempt handling
- ✓ Multi-step processes

## Output
All tests generate reports in:
- `reports/test_report.json` - Machine readable
- `reports/test_report.txt` - Human readable
- `reports/test_summary.html` - Web viewable

## Status
- **Total Tests**: 45+
- **Coverage**: All major components
- **Execution Time**: ~2-3 minutes

---

**Start with:** `php run_all_tests.php`
