<?php
// This file is part of Moodle - http://moodle.org/

/**
 * CodeRunner lib.php - Moodle Plugin Hooks and Integration
 *
 * This file contains the plugin initialization functions and hooks
 * for integrating Pyodide with CodeRunner questions.
 *
 * @package    qtype_coderunner
 * @subpackage lib
 * @copyright  2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Define plugin capabilities
 *
 * @return array
 */
function qtype_coderunner_get_capabilities() {
    return array(
        'qtype/coderunner:execute' => array(
            'riskbitmask' => RISK_PERSONAL,
            'captype' => 'write',
            'contextlevel' => CONTEXT_COURSE,
            'archetypes' => array('teacher' => CAP_ALLOW, 'student' => CAP_ALLOW),
            'clonepermissionsfrom' => 'moodle/quiz:attempt'
        )
    );
}

/**
 * Plugin install/upgrade hook
 * Called when plugin is first installed or upgraded
 */
function xmldb_qtype_coderunner_install() {
    // Initialize default settings for Pyodide integration
    set_config('use_local_pyodide', 0, 'qtype_coderunner');
    set_config('pyodide_version', '0.23.0', 'qtype_coderunner');
    set_config('pyodide_timeout', 30, 'qtype_coderunner');
    set_config('pyodide_max_output', 1000000, 'qtype_coderunner');
    set_config('pyodide_debug', 0, 'qtype_coderunner');
    
    return true;
}

/**
 * Hook: extend_settings_navigation
 * Adds Pyodide settings to admin interface
 *
 * @param settings_navigation $settingsnav
 * @param navigation_node $coderunnernode
 * @return void
 */
function qtype_coderunner_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $coderunnernode) {
    global $PAGE;
    
    // Only on CodeRunner admin pages
    if ($PAGE->pagetype == 'admin-qtype-coderunner') {
        // Add Pyodide settings section
        $pyodide_settings = new admin_settingpage('qtype_coderunner_pyodide',
            get_string('pyodide_settings', 'qtype_coderunner')
        );
        
        // Enable/disable Pyodide
        $pyodide_settings->add(new admin_setting_configcheckbox(
            'qtype_coderunner/use_local_pyodide',
            get_string('enable_pyodide', 'qtype_coderunner'),
            get_string('enable_pyodide_desc', 'qtype_coderunner'),
            0
        ));
        
        // Pyodide version
        $pyodide_settings->add(new admin_setting_configtext(
            'qtype_coderunner/pyodide_version',
            get_string('pyodide_version', 'qtype_coderunner'),
            get_string('pyodide_version_desc', 'qtype_coderunner'),
            '0.23.0'
        ));
        
        // Execution timeout
        $pyodide_settings->add(new admin_setting_configtext(
            'qtype_coderunner/pyodide_timeout',
            get_string('pyodide_timeout', 'qtype_coderunner'),
            get_string('pyodide_timeout_desc', 'qtype_coderunner'),
            30,
            PARAM_INT
        ));
        
        // Max output size
        $pyodide_settings->add(new admin_setting_configtext(
            'qtype_coderunner/pyodide_max_output',
            get_string('pyodide_max_output', 'qtype_coderunner'),
            get_string('pyodide_max_output_desc', 'qtype_coderunner'),
            1000000,
            PARAM_INT
        ));
        
        // Debug mode
        $pyodide_settings->add(new admin_setting_configcheckbox(
            'qtype_coderunner/pyodide_debug',
            get_string('pyodide_debug', 'qtype_coderunner'),
            get_string('pyodide_debug_desc', 'qtype_coderunner'),
            0
        ));
    }
}

/**
 * Hook: add_element_to_form
 * Allows CodeRunner to inject additional form elements
 *
 * @param moodleform $mform The form being constructed
 * @param MoobaseController $question Question being edited
 * @return void
 */
function qtype_coderunner_add_element_to_form(&$mform, $question) {
    // Add Pyodide execution setting to question form
    $mform->addElement('header', 'pyodide_header',
        get_string('execution_settings', 'qtype_coderunner')
    );
    
    $mform->addElement('checkbox', 'use_pyodide',
        get_string('use_pyodide_for_this_question', 'qtype_coderunner')
    );
    $mform->setDefault('use_pyodide', 1);
    $mform->addHelpButton('use_pyodide', 'use_pyodide', 'qtype_coderunner');
    
    $mform->addElement('checkbox', 'pyodide_allow_offline',
        get_string('allow_offline_execution', 'qtype_coderunner')
    );
    $mform->setDefault('pyodide_allow_offline', 1);
}

/**
 * Question execution handler
 * Intercepts question execution and routes to Pyodide if enabled
 *
 * @param string $language The programming language (e.g., 'python3')
 * @param string $code The source code to execute
 * @param string $input The input data for the program
 * @param int $timeout Maximum execution time in seconds
 * @return array Execution result with stdout, stderr, status
 */
function qtype_coderunner_execute_code($language, $code, $input = '', $timeout = 30) {
    // Check if Pyodide is enabled
    if (!get_config('qtype_coderunner', 'use_local_pyodide')) {
        // Use default Jobe execution
        return qtype_coderunner_jobe_execute($language, $code, $input, $timeout);
    }
    
    // Use Pyodide local execution
    return qtype_coderunner_pyodide_execute($language, $code, $input, $timeout);
}

/**
 * Execute code using local Pyodide
 *
 * @param string $language The programming language
 * @param string $code The source code
 * @param string $input The input data
 * @param int $timeout Maximum execution time
 * @return array Result array
 */
function qtype_coderunner_pyodide_execute($language, $code, $input = '', $timeout = 30) {
    // This is called from the AJAX endpoint
    // The actual execution happens in JavaScript in the browser
    
    // For server-side validation, we return a placeholder response
    return array(
        'status' => 0,
        'stdout' => '(execution in browser)',
        'stderr' => '',
        'time_limit_exceeded' => false,
        'signal' => 0
    );
}

/**
 * Execute code using Jobe server
 *
 * @param string $language The programming language
 * @param string $code The source code
 * @param string $input The input data
 * @param int $timeout Maximum execution time
 * @return array Result array
 */
function qtype_coderunner_jobe_execute($language, $code, $input = '', $timeout = 30) {
    // Call Jobe API for remote execution
    // Implementation depends on Jobe client library
    
    return array(
        'status' => 0,
        'stdout' => '',
        'stderr' => '',
        'time_limit_exceeded' => false,
        'signal' => 0
    );
}

/**
 * AJAX handler for code execution
 * Called from JavaScript when student clicks "Execute"
 *
 * @return void (echos JSON response)
 */
function qtype_coderunner_handle_execution_request() {
    require_login();
    
    // Get request JSON
    $json_input = file_get_contents('php://input');
    $request = json_decode($json_input, true);
    
    if (!$request) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Invalid request']);
        return;
    }
    
    // Validate required fields
    if (empty($request['language']) || empty($request['code'])) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Missing language or code']);
        return;
    }
    
    // Extract request parameters
    $language = $request['language'];
    $code = $request['code'];
    $input = $request['input'] ?? '';
    $timeout = $request['timeout'] ?? 30;
    
    // Validate language (security)
    $allowed_languages = ['python3', 'python', 'java', 'javascript', 'c'];
    if (!in_array($language, $allowed_languages)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Unsupported language']);
        return;
    }
    
    // Check code size (prevent DoS)
    if (strlen($code) > 1000000) {  // 1 MB limit
        http_response_code(413);
        echo json_encode(['ok' => false, 'error' => 'Code too large']);
        return;
    }
    
    try {
        // Execute the code
        $result = qtype_coderunner_execute_code($language, $code, $input, $timeout);
        
        // Log execution
        if (get_config('qtype_coderunner', 'pyodide_debug')) {
            error_log("Code execution: {$language}, code length: " . strlen($code));
        }
        
        // Return result
        echo json_encode([
            'ok' => true,
            'result' => $result
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'ok' => false,
            'error' => $e->getMessage()
        ]);
    }
}

/**
 * Hook: add_action_buttons
 * Adds custom buttons to the question form
 *
 * @param moodleform $mform The form object
 * @param string $page The current page
 * @return void
 */
function qtype_coderunner_form_question_add_action_buttons(&$mform, $page) {
    // Add test execution button
    $group = [];
    $group[] = $mform->createElement('button', 'test_execute_btn',
        get_string('test_code_locally', 'qtype_coderunner'),
        ['type' => 'button', 'onclick' => 'testExecuteLocally()']
    );
    $mform->addGroup($group, 'test_group', '', [' '], false);
}

/**
 * Initialize Pyodide environment on demand
 * Called when Pyodide execution is first needed
 *
 * @return bool Success
 */
function qtype_coderunner_init_pyodide_environment() {
    // Check if Pyodide files exist
    $pyodide_dir = __DIR__ . '/';
    
    $required_files = array(
        'pyodide_executor.js',
        'enable_pyodide.php',
        'jobe_api_mock.php'
    );
    
    foreach ($required_files as $file) {
        if (!file_exists($pyodide_dir . $file)) {
            error_log("Pyodide file missing: {$file}");
            return false;
        }
    }
    
    // Set environment variable to indicate Pyodide is ready
    $_ENV['PYODIDE_READY'] = true;
    
    return true;
}

/**
 * Get Pyodide integration status
 *
 * @return array Status information
 */
function qtype_coderunner_get_pyodide_status() {
    $status = array(
        'enabled' => get_config('qtype_coderunner', 'use_local_pyodide'),
        'version' => get_config('qtype_coderunner', 'pyodide_version'),
        'timeout' => get_config('qtype_coderunner', 'pyodide_timeout'),
        'max_output' => get_config('qtype_coderunner', 'pyodide_max_output'),
        'debug' => get_config('qtype_coderunner', 'pyodide_debug'),
        'files_exist' => qtype_coderunner_init_pyodide_environment()
    );
    
    return $status;
}

/**
 * Record execution attempt for statistics/monitoring
 *
 * @param int $question_id The question ID
 * @param int $user_id The user ID
 * @param string $language The programming language
 * @param bool $success Whether execution succeeded
 * @param float $execution_time Time taken in seconds
 * @return bool
 */
function qtype_coderunner_log_execution($question_id, $user_id, $language, $success, $execution_time) {
    global $DB;
    
    try {
        $record = (object)[
            'questionid' => $question_id,
            'userid' => $user_id,
            'language' => $language,
            'success' => $success ? 1 : 0,
            'execution_time' => $execution_time,
            'timestamp' => time()
        ];
        
        // Create a log entry (you may need to create a corresponding table)
        // For now, just log to error log if debug is enabled
        if (get_config('qtype_coderunner', 'pyodide_debug')) {
            error_log("Execution logged: Q{$question_id}, U{$user_id}, {$language}, " .
                     ($success ? 'success' : 'failed') . ", {$execution_time}s");
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Failed to log execution: " . $e->getMessage());
        return false;
    }
}

/**
 * Plugin upgrade function
 *
 * @param int $oldversion The version we are upgrading from
 * @return bool
 */
function xmldb_qtype_coderunner_upgrade($oldversion) {
    global $DB;
    
    $dbman = $DB->get_manager();
    
    // Add upgrade logic here for different versions
    
    return true;
}

/**
 * Return plugin metadata for the version.php file
 *
 * @return array
 */
function qtype_coderunner_get_plugins_list() {
    return array(
        'qtype_coderunner' => 'CodeRunner with Pyodide Integration'
    );
}

/**
 * Hook: before_form_saved
 * Validate question before saving
 *
 * @param object $question The question object
 * @return array Errors if any
 */
function qtype_coderunner_before_form_saved(&$question) {
    $errors = array();
    
    // Validate Pyodide settings if enabled
    if (isset($question->use_pyodide) && $question->use_pyodide) {
        // Check if language is supported
        $supported = array('python3', 'python');
        if (!in_array($question->language, $supported)) {
            $errors['use_pyodide'] = 
                'Pyodide execution is only supported for Python questions';
        }
    }
    
    return $errors;
}

/**
 * Cron job for maintenance
 * Called periodically to clean up execution logs, etc.
 *
 * @return void
 */
function qtype_coderunner_cron() {
    global $DB;
    
    // Clean up old execution logs (older than 30 days)
    $cutoff_time = time() - (30 * 24 * 60 * 60);
    
    try {
        // Delete old log entries if logging table exists
        $DB->delete_records_select('qtype_coderunner_execlog',
            'timestamp < ?', [$cutoff_time]
        );
    } catch (Exception $e) {
        // Table may not exist, that's ok
    }
    
    if (get_config('qtype_coderunner', 'pyodide_debug')) {
        error_log("CodeRunner cron completed");
    }
}

/**
 * Called immediately after a question has been created or imported
 *
 * @param object $question The question object
 * @return void
 */
function qtype_coderunner_after_import_form_saved(&$question) {
    // Initialize Pyodide settings for imported questions
    if (!isset($question->use_pyodide)) {
        $question->use_pyodide = get_config('qtype_coderunner', 'use_local_pyodide');
    }
}
