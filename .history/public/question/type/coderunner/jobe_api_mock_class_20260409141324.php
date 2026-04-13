<?php
/**
 * CodeRunner Mock Jobe API - Class Library
 * A mock Jobe server implementation for local CodeRunner testing
 * File: public/question/type/coderunner/jobe_api_mock_class.php
 */

defined('MOODLE_INTERNAL') || die();

class jobe_api_mock {
    /**
     * Run tests against the code
     */
    public static function run_tests($testcases, $code, $language = 'python3', $jobeapikey = '') {
        $response = array(
            'status' => 0,
            'cputime' => 0.01,
            'walltime' => 0.02,
            'testresults' => array(),
            'stdout' => '',
            'stderr' => '',
            'returncode' => 0,
            'error' => ''
        );
        
        if (empty($code)) {
            $response['error'] = 'Empty code';
            return $response;
        }
        
        // Mock execution: return test results
        if (is_array($testcases)) {
            foreach ($testcases as $test) {
                $response['testresults'][] = (object)array(
                    'status' => 0,  // PASS
                    'output' => isset($test->output) ? $test->output : 'Test passed',
                    'input' => isset($test->input) ? $test->input : '',
                    'expected' => isset($test->expected_output) ? $test->expected_output : ''
                );
            }
        }
        
        $response['stdout'] = "All tests passed\n";
        return $response;
    }
    
    /**
     * Run code and return output
     */
    public static function run_code($code, $input = '', $language = 'python3', $timeout = 10) {
        $response = array(
            'status' => 0,
            'cputime' => 0.01,
            'walltime' => 0.02,
            'stdout' => 'Code executed successfully',
            'stderr' => '',
            'returncode' => 0,
            'error' => ''
        );
        
        if (empty($code)) {
            $response['error'] = 'Empty code';
            return $response;
        }
        
        return $response;
    }
    
    /**
     * Get list of supported languages
     */
    public static function get_languages() {
        return array(
            'python3' => array('name' => 'Python 3', 'version' => '3.9'),
            'java' => array('name' => 'Java', 'version' => '11'),
            'cpp' => array('name' => 'C++', 'version' => '11'),
            'c' => array('name' => 'C', 'version' => '11')
        );
    }
    
    /**
     * Get Jobe server URL
     */
    public static function get_jobe_server_url() {
        return 'http://localhost';
    }
}

?>
