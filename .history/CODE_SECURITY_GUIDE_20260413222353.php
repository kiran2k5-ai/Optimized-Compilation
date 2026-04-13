<?php
/**
 * STUDENT CODE TAMPERING & ATTACK PROTECTION
 * Handles code validation, tampering detection, and malicious code prevention
 */

echo "=== CODE SECURITY & TAMPERING PROTECTION ===\n\n";

// ===== THREAT SCENARIOS =====
echo "THREAT SCENARIO 1: Student Modifies Code Before Submission\n";
echo "Problem: Student submits different code than they wrote in editor\n";
echo "Attack: Change code to execute dangerous commands\n";
echo "Solution: Hash code + server-side validation\n\n";

echo "THREAT SCENARIO 2: External Attacker Submits Malicious Code\n";
echo "Problem: Attacker sends malicious code via API\n";
echo "Attack: Code injection, command execution, server compromise\n";
echo "Solution: Code pattern analysis + sandboxing\n\n";

echo "THREAT SCENARIO 3: Code Injection via Input\n";
echo "Problem: Attacker uses stdin input to inject code/commands\n";
echo "Attack: Bypass security, escape sandbox\n";
echo "Solution: Input validation + size limits\n\n";

echo "THREAT SCENARIO 4: Infinite Loop / Resource Exhaustion\n";
echo "Problem: Code runs forever, consuming CPU/memory\n";
echo "Attack: Denial of Service (DoS)\n";
echo "Solution: Execution timeout + resource limits\n\n";

// ===== DETECTION & BLOCKING RULES =====
echo "=== CODE PATTERN DETECTION (DANGEROUS KEYWORDS) ===\n\n";

$dangerous_patterns = [
    // Code execution
    'exec' => 'Execute shell commands',
    'system' => 'Execute system commands',
    'passthru' => 'Execute external programs',
    'shell_exec' => 'Execute shell command',
    'proc_open' => 'Open process',
    'popen' => 'Open pipe',
    
    // File system access
    'file_get_contents' => 'Read files',
    'file_put_contents' => 'Write files',
    'unlink' => 'Delete files',
    'rmdir' => 'Delete directories',
    'mkdir' => 'Create directories',
    
    // Database access
    'mysql_query' => 'Direct SQL execution',
    'mysqli_query' => 'Direct SQL execution',
    'PDO::exec' => 'Direct SQL execution',
    
    // Import/require
    '__import__' => 'Import Python modules',
    'importlib' => 'Import Python modules',
    'eval' => 'Evaluate code',
    'compile' => 'Compile code',
    
    // Network access
    'urllib' => 'Network requests',
    'requests' => 'Network requests',
    'socket' => 'Raw socket access',
    'subprocess' => 'Subprocess execution',
    
    // OS access
    'os.system' => 'OS system calls',
    'os.popen' => 'OS pipe',
    '__builtins__' => 'Access builtins',
];

foreach ($dangerous_patterns as $pattern => $risk) {
    echo "  ❌ $pattern - $risk\n";
}

echo "\n";
?>
