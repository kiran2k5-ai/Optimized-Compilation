<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== TESTING JSON OUTPUT TEMPLATE ===\n\n";

$q20 = $DB->get_record('question_coderunner_options', ['questionid' => 20]);
echo "Template:\n" . substr($q20->template, 0, 200) . "...\n\n";

// Simulate what will happen for Test 1
$test = $DB->get_record('question_coderunner_tests', ['id' => 1, 'questionid' => 20]);

// Build the code that will be executed
$template = $q20->template;
$template = str_replace('{{ TEST.stdin | e(\'py\') }}', addslashes($test->stdin), $template);
$template = str_replace('{{ TEST.expected | e(\'py\') }}', addslashes($test->expected), $template);
$template = str_replace('{{ STUDENT_ANSWER }}', $q20->answer, $template);
$template = str_replace('{{ TEST.testcode }}', '', $template);

echo "Generated code for Test 1:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo $template . "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Execute it
$ch = curl_init('http://127.0.0.1/jobe/index.php/restapi/runs');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'run_spec' => [
        'language_id' => 'python3',
        'sourcecode' => $template,
        'stdin' => ''
    ]
]));

$resp = curl_exec($ch);
curl_close($ch);

$result = json_decode($resp, true);

echo "Mock API Response:\n";
echo "  Outcome: {$result['outcome']} (15 = success)\n";
echo "  Raw stdout: " . json_encode($result['stdout']) . "\n";
echo "  Stderr: " . json_encode($result['stderr']) . "\n\n";

// Check if stdout is valid JSON
$output_trimmed = trim($result['stdout']);
$parsed = json_decode($output_trimmed);

if ($parsed !== null && isset($parsed->fraction)) {
    echo "✅ SUCCESS! Output is valid JSON with fraction:\n";
    echo "   " . json_encode($parsed, JSON_PRETTY_PRINT) . "\n";
} else {
    echo "❌ FAILED! Output is not JSON with fraction:\n";
    echo "   Got: $output_trimmed\n";
}
?>
