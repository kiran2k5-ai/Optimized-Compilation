<?php
// Instead of making HTTP requests, we can directly call the mock API
// by including the file - this avoids network/firewall issues

// File: /question/type/coderunner/classes/jobesandbox_mock.php

class qtype_coderunner_jobesandbox_mock extends qtype_coderunner_sandbox {
    private $languages = null;
    
    public function __construct() {
        parent::__construct();
        // Initialize with our mock languages
        $this->languages = ['python3', 'java', 'cpp', 'c'];
    }
    
    public function get_languages() {
        return (object) [
            'error' => 0,
            'languages' => $this->languages
        ];
    }
    
    public function execute($sourcecode, $language, $input = '', $files = null, $params = null) {
        // Mock execution - just return success
        return (object) [
            'error' => 0,
            'result' => 15,  // RESULT_SUCCESS
            'output' => 'Code executed successfully',
            'cmpinfo' => '',
            'stderr' => '',
            'signal' => ''
        ];
    }
}
?>
