<?php
/**
 * SECURITY VULNERABILITIES ANALYSIS & FIXES
 * CodeRunner + Jobe API Security Report
 */

echo "=== CODERUNNER SECURITY VULNERABILITIES ===\n\n";

$vulnerabilities = [
    1 => [
        'name' => 'INSECURE FILE PERMISSIONS',
        'severity' => 'HIGH',
        'location' => 'config.php, public/jobe/index.php',
        'problem' => 'Files are world-writable (0666). Anyone can modify or execute malicious code.',
        'evidence' => 'File permissions: 0666 (should be 0644 or 0440)',
        'risk' => 'Attacker could modify Jobe API or database credentials',
        'fix' => 'Change permissions to 0644 (read-only for others)'
    ],
    2 => [
        'name' => 'NO INPUT VALIDATION/SANITIZATION',
        'severity' => 'CRITICAL',
        'location' => 'public/jobe/index.php - Python sourcecode execution',
        'problem' => 'User-submitted Python code is executed directly without any validation',
        'evidence' => 'sourcecode from POST is passed directly to proc_open()',
        'risk' => 'Code injection - students can execute ANY system commands',
        'fix' => 'Add code sandboxing, resource limits, or use isolated Python environment'
    ],
    3 => [
        'name' => 'NO AUTHENTICATION/AUTHORIZATION',
        'severity' => 'HIGH',
        'location' => 'public/jobe/index.php',
        'problem' => 'Jobe API accepts requests from anyone without API key or authentication',
        'evidence' => 'No API key check, no user verification',
        'risk' => 'External attackers can execute code via Jobe API',
        'fix' => 'Add API key validation, IP whitelisting, or Moodle session verification'
    ],
    4 => [
        'name' => 'EMPTY DATABASE PASSWORD',
        'severity' => 'HIGH',
        'location' => 'config.php',
        'problem' => 'Database has no password (only OK for development)',
        'evidence' => "\$CFG->dbpass = '';",
        'risk' => 'Easy database compromise, data theft, data manipulation',
        'fix' => 'Set strong database password (16+ chars, letters+numbers+symbols)'
    ],
    5 => [
        'name' => 'NO HTTPS/SSL ENCRYPTION',
        'severity' => 'HIGH',
        'location' => 'Apache configuration, config.php',
        'problem' => 'All data transmitted in plain text (http only)',
        'evidence' => 'wwwroot = http://localhost (no https)',
        'risk' => 'Network sniffing can capture credentials, student code, grades',
        'fix' => 'Install SSL certificate, configure Apache for HTTPS'
    ],
    6 => [
        'name' => 'NO RESOURCE LIMITS',
        'severity' => 'MEDIUM',
        'location' => 'public/jobe/index.php',
        'problem' => 'No timeout, memory limit, or CPU limit on executed code',
        'evidence' => 'No timeout, memory_limit, or process resource controls',
        'risk' => 'DoS attack - infinite loops crash server, consume memory',
        'fix' => 'Add timeout (ulimit), memory limits, and CPU time limits'
    ],
    7 => [
        'name' => 'NO RATE LIMITING',
        'severity' => 'MEDIUM',
        'location' => 'public/jobe/index.php',
        'problem' => 'No limit on how many requests per user/IP',
        'evidence' => 'No throttling or request counting',
        'risk' => 'Brute force attacks, spam requests, resource exhaustion',
        'fix' => 'Add rate limiting: max 10 submissions per minute'
    ],
    8 => [
        'name' => 'NO DISABLE_FUNCTIONS',
        'severity' => 'HIGH',
        'location' => 'PHP configuration',
        'problem' => 'All dangerous PHP functions are enabled',
        'evidence' => 'disable_functions: None',
        'risk' => 'Dangerous functions like exec(), system(), passthru() are available',
        'fix' => 'Disable dangerous functions in php.ini'
    ],
    9 => [
        'name' => 'CORS OPEN TO ALL',
        'severity' => 'MEDIUM',
        'location' => 'public/jobe/index.php line 9',
        'problem' => "Access-Control-Allow-Origin: * allows any website to call API",
        'evidence' => "header('Access-Control-Allow-Origin: *')",
        'risk' => 'Cross-site request forgery (CSRF), unauthorized API calls',
        'fix' => 'Set CORS to specific domain only (localhost)'
    ],
    10 => [
        'name' => 'NO LOGGING OR AUDIT TRAIL',
        'severity' => 'MEDIUM',
        'location' => 'public/jobe/index.php',
        'problem' => 'No logging of who executed what code and when',
        'evidence' => 'No log file creation or logging',
        'risk' => 'Cannot detect malicious code execution or debug issues',
        'fix' => 'Log all code executions with timestamp, user, code snippet'
    ]
];

foreach ($vulnerabilities as $id => $vuln) {
    echo "[$id] {$vuln['name']} [" . strtoupper($vuln['severity']) . "]\n";
    echo "    Location: {$vuln['location']}\n";
    echo "    Problem: {$vuln['problem']}\n";
    echo "    Risk: {$vuln['risk']}\n";
    echo "    Fix: {$vuln['fix']}\n\n";
}

echo "\n=== PRIORITY FIXES ===\n";
echo "1. FIX CRITICAL: Add input validation & sandboxing for code execution\n";
echo "2. FIX HIGH: Add authentication to Jobe API (API key check)\n";
echo "3. FIX HIGH: Set strong database password\n";
echo "4. FIX HIGH: Fix file permissions (0644)\n";
echo "5. FIX HIGH: Enable HTTPS/SSL\n";
echo "6. FIX MEDIUM: Add resource limits (timeout, memory, CPU)\n";
echo "7. FIX MEDIUM: Add rate limiting\n";
echo "8. FIX MEDIUM: Fix CORS settings\n";
echo "9. FIX MEDIUM: Add logging/audit trail\n";
echo "10. FIX MEDIUM: Disable dangerous PHP functions\n";
?>
