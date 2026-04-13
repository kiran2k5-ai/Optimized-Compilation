<?php
/**
 * CODE VALIDATION & SECURITY CHECK
 * Prevents submission of code with dangerous imports/functions
 */

class CodeValidator {
    
    // Safe Python modules - whitelist
    const SAFE_MODULES = [
        're',          // Regular expressions
        'string',      // String operations
        'math',        // Math functions
        'random',      // Random number generation
        'datetime',    // Date/time operations
        'json',        // JSON parsing
        'collections', // Data structures (collections)
        'itertools',   // Iterator tools
        'functools',   // Function tools
        'operator',    // Operators
    ];
    
    // Dangerous modules - blacklist
    const DANGEROUS_MODULES = [
        'os',           // OS commands
        'subprocess',   // Process execution
        'sys',          // System access
        'socket',       // Network socket
        'requests',     // HTTP requests
        'urllib',       // URL operations
        '__main__',     // Main execution
        'ctypes',       // C library access
        'threading',    // Thread creation
        'multiprocessing', // Process spawning
        'code',         // Code compilation
        'pickle',       // Object serialization
        'marshal',      // Internal object serialization
    ];
    
    // Dangerous functions - blacklist
    const DANGEROUS_FUNCTIONS = [
        'exec',         // Execute Python code
        'eval',         // Evaluate expression
        'compile',      // Compile code
        'open',         // File operations
        'input',        // Standard input (can be blocked)
        '__import__',   // Dynamic imports
        'globals',      // Access global scope
        'locals',       // Access local scope
        'vars',         // Access variables
        'dir',          // List object attributes
        'getattr',      // Get object attributes
        'setattr',      // Set object attributes
        'delattr',      // Delete attributes
        'isinstance',   // Type checking (usually safe)
        'issubclass',   // Type checking (usually safe)
        'type',         // Get type (usually safe)
        'super',        // Access parent class (usually safe)
    ];
    
    /**
     * Validate student code for security issues
     * 
     * @param string $code Python code to validate
     * @return array ['is_valid' => bool, 'errors' => [], 'warnings' => []]
     */
    public static function validate($code) {
        $errors = [];
        $warnings = [];
        
        // Check 1: Empty code
        if (empty(trim($code))) {
            $errors[] = 'Code cannot be empty';
            return [
                'is_valid' => false,
                'errors' => $errors,
                'warnings' => $warnings
            ];
        }
        
        // Check 2: Code size limits
        if (strlen($code) > 100000) {
            $errors[] = 'Code is too large (max 100KB)';
        }
        
        // Check 3: Dangerous imports
        $dangerous_imports = self::check_dangerous_imports($code);
        if (!empty($dangerous_imports)) {
            foreach ($dangerous_imports as $import) {
                $errors[] = "Cannot import '$import' module - not allowed";
            }
        }
        
        // Check 4: Dangerous functions
        $dangerous_functions = self::check_dangerous_functions($code);
        if (!empty($dangerous_functions)) {
            foreach ($dangerous_functions as $func) {
                $errors[] = "Cannot use '$func()' function - not allowed";
            }
        }
        
        // Check 5: Shell commands
        $shell_attempts = self::check_shell_commands($code);
        if (!empty($shell_attempts)) {
            foreach ($shell_attempts as $attempt) {
                $errors[] = "Shell command execution detected: $attempt";
            }
        }
        
        // Check 6: Suspicious patterns
        $suspicious = self::check_suspicious_patterns($code);
        if (!empty($suspicious)) {
            foreach ($suspicious as $pattern) {
                $warnings[] = "Suspicious code pattern: $pattern";
            }
        }
        
        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
            'code_size' => strlen($code),
            'lines' => substr_count($code, "\n") + 1
        ];
    }
    
    /**
     * Check for dangerous module imports
     */
    private static function check_dangerous_imports($code) {
        $dangerous = [];
        
        foreach (self::DANGEROUS_MODULES as $module) {
            // Pattern: import module
            if (preg_match("/\bimport\s+" . preg_quote($module) . "\b/i", $code)) {
                $dangerous[] = $module;
                continue;
            }
            
            // Pattern: from module import ...
            if (preg_match("/\bfrom\s+" . preg_quote($module) . "\s+import/i", $code)) {
                $dangerous[] = $module;
                continue;
            }
            
            // Pattern: __import__('module')
            $pattern = "/__import__\\s*\\(\\s*['\"]" . preg_quote($module) . "['\"]\\s*\\)/i";
            if (preg_match($pattern, $code)) {
                $dangerous[] = $module;
            }
        }
        
        return array_unique($dangerous);
    }
    
    /**
     * Check for dangerous function calls
     */
    private static function check_dangerous_functions($code) {
        $dangerous = [];
        
        foreach (self::DANGEROUS_FUNCTIONS as $func) {
            // Pattern: function_name(
            if (preg_match("/\b$func\s*\(/i", $code)) {
                $dangerous[] = $func;
            }
        }
        
        return array_unique($dangerous);
    }
    
    /**
     * Check for shell command attempts
     */
    private static function check_shell_commands($code) {
        $attempts = [];
        
        // os.system
        if (preg_match('/os\s*\.\s*system\s*\(/i', $code)) {
            $attempts[] = 'os.system()';
        }
        
        // os.popen
        if (preg_match('/os\s*\.\s*popen\s*\(/i', $code)) {
            $attempts[] = 'os.popen()';
        }
        
        // os.exec
        if (preg_match('/os\s*\.\s*exec\s*\(/i', $code)) {
            $attempts[] = 'os.exec()';
        }
        
        // subprocess.run
        if (preg_match('/subprocess\s*\.\s*\w+\s*\(/i', $code)) {
            $attempts[] = 'subprocess.*()';
        }
        
        return array_unique($attempts);
    }
    
    /**
     * Check for suspicious code patterns
     */
    private static function check_suspicious_patterns($code) {
        $suspicious = [];
        
        // Very long strings (potential obfuscation)
        if (preg_match('/["\']{1}[a-zA-Z0-9+\/=]{1000,}["\']{1}/', $code)) {
            $suspicious[] = 'Very long string (possible obfuscation)';
        }
        
        // Hex encoding (possible obfuscation)
        if (preg_match('/\\\\x[0-9a-f]{2}/i', $code)) {
            $suspicious[] = 'Hex-encoded strings (possible obfuscation)';
        }
        
        // Unicode encoding (possible obfuscation)
        if (preg_match('/\\\\u[0-9a-f]{4}/i', $code)) {
            $suspicious[] = 'Unicode-encoded strings (possible obfuscation)';
        }
        
        // Nested function calls (excessive complexity)
        $nesting = preg_match_all('/\(/', $code);
        if ($nesting > 50) {
            $suspicious[] = 'Very deeply nested code (excessive complexity)';
        }
        
        return array_unique($suspicious);
    }
}

// ===== USAGE EXAMPLE =====
/*
// When student submits code:
$submitted_code = $_POST['answer'] ?? '';
$validation_result = CodeValidator::validate($submitted_code);

if (!$validation_result['is_valid']) {
    // Reject submission
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Code validation failed',
        'errors' => $validation_result['errors']
    ]);
    exit;
}

if (!empty($validation_result['warnings'])) {
    // Log warnings for admin review
    log_code_submission_warning($userid, $validation_result['warnings']);
}

// Code is safe, proceed with execution
execute_code($submitted_code);
*/
?>
