-- ============================================
-- CodeRunner + Pyodide Example Questions
-- Import into your Moodle database to get sample questions
-- ============================================

-- Example Question 1: Hello World (Python 3)
INSERT INTO mdl_question (
    category, parent, name, questiontext, questiontextformat, 
    defaultmark, penalty, qtype, length, stamp, version, 
    hidden, timecreated, timemodified, createdby, modifiedby
) VALUES (
    1, 0,
    'Hello World - Python',
    '<p>Write a Python program that prints "Hello World"</p>',
    1,
    1, 0.1, 'coderunner', 0, 'CODERUNNER_HELLOWORLD', 1,
    0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 2, 2
);

-- Example Question 2: Simple Function (Python 3)
INSERT INTO mdl_question (
    category, parent, name, questiontext, questiontextformat, 
    defaultmark, penalty, qtype, length, hidden, stamp, version,
    timecreated, timemodified, createdby, modifiedby
) VALUES (
    1, 0,
    'Sum Function - Python',
    '<p>Write a function called <code>sum_numbers</code> that takes two parameters and returns their sum.</p>
    <p>Example: <code>sum_numbers(3, 4)</code> returns <code>7</code></p>',
    1,
    2, 0.1, 'coderunner', 0, 0, 'CODERUNNER_SUMFUNC', 1,
    UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 2, 2
);

-- Example Question 3: List Operations (Python 3)
INSERT INTO mdl_question (
    category, parent, name, questiontext, questiontextformat, 
    defaultmark, penalty, qtype, length, hidden, stamp, version,
    timecreated, timemodified, createdby, modifiedby
) VALUES (
    1, 0,
    'Remove Duplicates - Python',
    '<p>Write a function called <code>remove_duplicates</code> that takes a list and returns a new list with duplicates removed.</p>
    <p>Example: <code>remove_duplicates([1, 2, 2, 3])</code> returns <code>[1, 2, 3]</code></p>',
    1,
    3, 0.1, 'coderunner', 0, 0, 'CODERUNNER_REMOVEDUP', 1,
    UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 2, 2
);

-- Example Question 4: String Reversal (Python 3)
INSERT INTO mdl_question (
    category, parent, name, questiontext, questiontextformat, 
    defaultmark, penalty, qtype, length, hidden, stamp, version,
    timecreated, timemodified, createdby, modifiedby
) VALUES (
    1, 0,
    'Reverse String - Python',
    '<p>Write a function called <code>reverse_string</code> that takes a string and returns it reversed.</p>
    <p>Example: <code>reverse_string("hello")</code> returns <code>"olleh"</code></p>',
    1,
    1, 0.1, 'coderunner', 0, 0, 'CODERUNNER_REVSTR', 1,
    UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 2, 2
);

-- Example Question 5: Loop and Accumulation (Python 3)
INSERT INTO mdl_question (
    category, parent, name, questiontext, questiontextformat, 
    defaultmark, penalty, qtype, length, hidden, stamp, version,
    timecreated, timemodified, createdby, modifiedby
) VALUES (
    1, 0,
    'Calculate Factorial - Python',
    '<p>Write a function called <code>factorial</code> that takes a number and returns its factorial.</p>
    <p>Example: <code>factorial(5)</code> returns <code>120</code></p>
    <p>(5! = 5 × 4 × 3 × 2 × 1 = 120)</p>',
    1,
    2, 0.1, 'coderunner', 0, 0, 'CODERUNNER_FACTORIAL', 1,
    UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 2, 2
);

-- ============================================
-- Installation Instructions:
-- ============================================
-- 
-- 1. Access your Moodle database (via phpMyAdmin or MySQL client)
--
-- 2. Paste this SQL file and execute it
--
-- 3. The example questions will be created and appear in your Question Bank
--
-- 4. Add them to a quiz and assign to a course
--
-- 5. When students attempt the questions:
--    - Pyodide will load in the browser
--    - Students write Python code
--    - Click "Check" to execute locally
--    - Results display instantly
--    - Tests run locally without server communication
--
-- ============================================
-- Expected Student Code Examples:
-- ============================================
--
-- Q1 (Hello World):
-- print("Hello World")
--
-- Q2 (Sum Function):
-- def sum_numbers(a, b):
--     return a + b
--
-- Q3 (Remove Duplicates):
-- def remove_duplicates(lst):
--     return list(set(lst))
--
-- Q4 (Reverse String):
-- def reverse_string(s):
--     return s[::-1]
--
-- Q5 (Factorial):
-- def factorial(n):
--     if n <= 1:
--         return 1
--     return n * factorial(n - 1)
--
-- ============================================
