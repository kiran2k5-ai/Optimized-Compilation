<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== VERIFYING TEMPLATE OUTPUT ===\n\n";

$q20 = $DB->get_record('question_coderunner_options', ['questionid' => 20]);

echo "Current Q20 settings:\n";
echo "  Grader: {$q20->grader}\n";
echo "  Is Combinator: {$q20->iscombinatortemplate}\n";
echo "  Template:\n";
echo $q20->template . "\n\n";

// Test the template
$test = $DB->get_record('question_coderunner_tests', ['id' => 1, 'questionid' => 20]);

// Build code
$code = $q20->template;
$code = str_replace('{{ TEST.stdin | e(\'py\') }}', $test->stdin, $code);
$code = str_replace('{{ STUDENT_ANSWER }}', $q20->answer, $code);

echo "Generated code for Test 1:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo $code;
echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Execute
$ch = curl_init('http://127.0.0.1/jobe/index.php/restapi/runs');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'run_spec' => [
        'language_id' => 'python3',
        'sourcecode' => $code,
        'stdin' => ''
    ]
]));

$resp = curl_exec($ch);
curl_close($ch);
$result = json_decode($resp, true);

echo "Mock API Output:\n";
echo "  Raw stdout: " . json_encode($result['stdout']) . "\n";
echo "  Trimmed: " . json_encode(trim($result['stdout'])) . "\n";
echo "  Expected: " . json_encode($test->expected) . "\n\n";

if (trim($result['stdout']) === trim($test->expected)) {
    echo "✅ Output matches expected!\n";
} else {
    echo "❌ Output doesn't match\n";
}
?>
