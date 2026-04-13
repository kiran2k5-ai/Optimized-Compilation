<?php
// CodeRunner Jobe Server Configuration Script
// File: public/question/type/coderunner/setup_jobe_mock.php
// Run this via browser: http://localhost/question/type/coderunner/setup_jobe_mock.php

// Get correct path to config.php
$config_path = dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php';
require_once($config_path);
require_login();

echo "<h1>CodeRunner - Jobe Mock Configuration</h1>";

// Only allow admin users
if (!is_siteadmin()) {
    die("Admin access required!");
}

// Update Moodle config to use localhost jobe mock
$configs = array(
    'jobe_host' => 'localhost',
    'jobe_port' => '80',
    'jobe_apikey' => '',
    'jobe_sandbox_class' => 'qtype_coderunner_jobesandbox',
);

foreach ($configs as $name => $value) {
    set_config($name, $value, 'qtype_coderunner');
    echo "✓ Set qtype_coderunner/$name = $value<br/>";
}

// Update all existing CodeRunner questions to use jobe sandbox
global $DB;

$sql = "UPDATE {question_coderunner_options} 
        SET sandbox = 'jobe', 
            grader = 'EBGrader.php',
            cputimelimitsecs = 10,
            memlimitmb = 200
        WHERE coderunnertype LIKE '%python%'";

$count = $DB->execute($sql);
echo "✓ Updated CodeRunner questions: $count affected<br/>";

// Verify configuration
$host = get_config('qtype_coderunner', 'jobe_host');
$port = get_config('qtype_coderunner', 'jobe_port');
echo "<h2>Current Configuration:</h2>";
echo "Jobe Host: $host<br/>";
echo "Jobe Port: $port<br/>";

// Test connection to mock API
echo "<h2>Testing Mock API:</h2>";
$test_code = "print('Hello from mock Jobe')";
$url = "http://$host:$port/question/type/coderunner/jobe_api_mock.php";
echo "Testing: $url<br/>";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array(
    'language' => 'python3',
    'code' => $test_code,
    'stdin' => ''
)));

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code == 200) {
    echo "✓ Mock API is responding!<br/>";
    echo "<pre>" . $response . "</pre>";
} else {
    echo "✗ Mock API not responding (HTTP $http_code)<br/>";
}

echo "<h2>Setup Complete!</h2>";
echo "<a href='/question/type/coderunner/test_question.php'>Create Test Question</a>";
?>
