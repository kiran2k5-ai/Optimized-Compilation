<?php
define('CLI_SCRIPT', true);
require_once('config.php');

global $DB;

echo "Processing adhoc tasks...\n";

// Get stuck tasks
$tasks = $DB->get_records('task_adhoc', [], 'id ASC');

foreach ($tasks as $task) {
    echo "Task {$task->id}: {$task->component} - {$task->classname}\n";
    
    // Try to execute the task
    try {
        $classname = $task->classname;
        if (class_exists($classname)) {
            $obj = new $classname();
            $obj->execute();
            
            // Mark as complete
            $DB->delete_records('task_adhoc', ['id' => $task->id]);
            echo "✓ Task {$task->id} completed!\n";
        } else {
            echo "✗ Class not found: $classname\n";
        }
    } catch (Exception $e) {
        echo "✗ Error: " . $e->getMessage() . "\n";
    }
}

echo "\nDone!\n";
?>
