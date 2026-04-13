<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== CREATING CUSTOM TEMPLATE FOR Q20 ===\n\n";

$custom_template = <<<'TEMPLATE'
{{ STUDENT_ANSWER }}

SEPARATOR = "#<ab@17943918#@>#"

{% for TEST in TESTCASES %}
{% if TEST.stdin is not empty %}
__input_lines__ = """{{ TEST.stdin | e('py') }}""".split('\n')
__input_index__ = 0
__saved_input__ = input
def input(prompt=''):
    global __input_index__
    if __input_index__ < len(__input_lines__):
        line = __input_lines__[__input_index__]
        __input_index__ += 1
        print(line)
        return line
    return __saved_input__(prompt)

{{ TEST.testcode }}
{% else %}
{{ TEST.testcode }}
{% endif %}
{% if not loop.last %}
print(SEPARATOR)
{% endif %}
{% endfor %}
TEMPLATE;

$q20 = $DB->get_record('question_coderunner_options', ['questionid' => 20]);
$q20->template = $custom_template;
$DB->update_record('question_coderunner_options', $q20);

echo "✅ Custom template created and set on Q20!\n";
echo "Template length: " . strlen($custom_template) . "\n\n";

purge_all_caches();
echo "✅ Cache cleared!\n";
?>
