/**
 * QUIZ ATTEMPT.PHP - REQUEST/RESPONSE STRUCTURE
 * File: /mod/quiz/attempt.php
 */

// ============================================
// 1. INCOMING REQUEST (GET/POST PARAMETERS)
// ============================================

REQUEST PARAMETERS:
├── attempt (REQUIRED - PARAM_INT)
│   └── The unique attempt ID
│
├── page (OPTIONAL - PARAM_INT, default=0)
│   └── Which page of the quiz (0-based index)
│
└── cmid (OPTIONAL - PARAM_INT)
    └── Course module ID (for context validation)

Example URL:
  http://localhost/mod/quiz/attempt.php?attempt=1&page=0&cmid=5

// ============================================
// 2. INTERNAL PROCESSING FLOW
// ============================================

STEP 1: Parameter Extraction
  ├─ $attemptid = required_param('attempt', PARAM_INT)
  ├─ $page = optional_param('page', 0, PARAM_INT)
  └─ $cmid = optional_param('cmid', null, PARAM_INT)

STEP 2: Attempt Object Creation
  ├─ quiz_create_attempt_handling_errors($attemptid, $cmid)
  ├─ Load attempt from database (mdl_quiz_attempts)
  ├─ Load quiz configuration (mdl_quiz)
  ├─ Load quiz module (mdl_course_modules)
  └─ Create quiz_attempt object

STEP 3: Authentication & Authorization Checks
  ├─ require_login() → Check user is logged in
  ├─ Check attempt belongs to logged-in user
  ├─ Check user has 'mod/quiz:attempt' capability
  ├─ Check access rules (time limits, IP restrictions, etc.)
  └─ Check if attempt is still open (not finished/overdue)

STEP 4: Attempt State Validation
  ├─ if (is_finished) → redirect to review_url()
  ├─ if (is_overdue) → redirect to summary_url()
  ├─ if (preflight_check_required) → redirect to start_attempt_url()
  └─ Continue if all checks pass

STEP 5: Page Validation
  ├─ Force page number into valid range
  ├─ Get list of question slots for this page
  ├─ if (empty slots) → throw error 'noquestionsfound'
  └─ Set current page in database

STEP 6: Setup Page Elements
  ├─ Load JavaScript (attempt form, auto-save)
  ├─ Setup navigation panel
  ├─ Get HTML head contributions (CSS, JS)
  └─ Determine next page ID

// ============================================
// 3. DATABASE QUERIES
// ============================================

READ FROM:
├─ mdl_quiz_attempts
│  └── SELECT * FROM mdl_quiz_attempts WHERE id = {$attemptid}
│
├─ mdl_quiz
│  └── SELECT * FROM mdl_quiz WHERE id = {quiz->id}
│
├─ mdl_quiz_slots
│  └── SELECT * FROM mdl_quiz_slots WHERE quizid = {quizid} AND page = {page}
│
├─ mdl_question
│  └── SELECT * FROM mdl_question WHERE id IN (slot questions)
│
└─ mdl_question_attempts
   └── SELECT * FROM mdl_question_attempts WHERE questionusageid = {usageid}

WRITE TO:
└─ mdl_quiz_attempts
   └── UPDATE mdl_quiz_attempts SET currentpage = {page} WHERE id = {attemptid}

// ============================================
// 4. OUTGOING RESPONSE (HTML OUTPUT)
// ============================================

RESPONSE STRUCTURE:
└─ HTML Page with:
   ├─ Page Header
   │  └─ Title: "{Quiz Name} - Page {X} of {Y}"
   │
   ├─ Navigation Panel (Left side)
   │  ├─ Question overview
   │  ├─ Question status (attempted/not attempted)
   │  └─ Page navigation buttons
   │
   ├─ Main Content Area
   │  ├─ Question Text
   │  ├─ Question Type Renderer
   │  │  └─ For CodeRunner: Code editor + test results
   │  ├─ Answer Input (varies by question type)
   │  ├─ Feedback (if show_feedback = true)
   │  └─ Question Controls (Submit, etc.)
   │
   ├─ Footer with Navigation Buttons
   │  ├─ "Previous page" button (if not first page)
   │  └─ "Next page" button (if not last page)
   │
   └─ JavaScript Module Initialization
      ├─ M.mod_quiz.init_attempt_form()
      ├─ M.mod_quiz.autosave (if enabled)
      └─ Question type-specific JS

// ============================================
// 5. RESPONSE DATA SENT TO BROWSER
// ============================================

echo $output->attempt_page(
    $attemptobj,       // Quiz attempt object
    $page,             // Current page number
    $accessmanager,    // Access control manager
    $messages,         // Any access warning messages
    $slots,            // Array of question slots for this page
    $id,               // (deprecated)
    $nextpage          // Next page number (-1 if last page)
);

// ============================================
// 6. KEY OBJECT STRUCTURES
// ============================================

ATTEMPTOBJ Properties:
{
  id: int,              // Attempt ID
  quiz: int,            // Quiz instance ID
  userid: int,          // User attempting the quiz
  attempt: int,         // Attempt number (1st, 2nd, etc.)
  currentpage: int,     // Current page number
  timestart: int,       // When attempt started (unix timestamp)
  timefinish: int,      // When attempt finished (0 if in progress)
  timemodified: int,    // Last update time
  sumgrades: float,     // Total marks achieved
  state: string         // 'inprogress' | 'overdue' | 'finished'
}

SLOT Structure (for each question on page):
{
  id: int,              // Slot ID
  quizid: int,          // Quiz ID
  page: int,            // Page number
  slot: int,            // Slot number on quiz
  questionid: int,      // Question ID
  maxmark: float,       // Maximum marks for this question
  displaynumber: string // Display as "1", "2", "Q1", etc.
}

VERSION:
- Moodle version: 4.3
- Quiz module API
