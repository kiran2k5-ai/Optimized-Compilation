-- Check all questions and their types
SELECT id, name, qtype FROM mdl_question ORDER BY id;

-- Check for any unusual qtype values
SELECT DISTINCT qtype FROM mdl_question;

-- Check Question 20 specifically
SELECT id, name, qtype, category FROM mdl_question WHERE id = 20;
