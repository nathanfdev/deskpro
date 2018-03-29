<?php
define('PHP_CS_SRC_DIR', realpath(__DIR__ . '/../app/BUILD'));

$header = '';

Symfony\CS\Fixer\Contrib\HeaderCommentFixer::setHeader($header);

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->exclude('vendor')
    ->exclude('vendor-src')
    ->exclude('config_new')
    ->exclude('opcache-gui.php')
    ->in(PHP_CS_SRC_DIR)
;

return Symfony\CS\Config\Config::create()
    ->addCustomFixer(new Symfony\CS\Fixer\Contrib\HeaderCommentFixer())
    ->setUsingCache(true)
    ->fixers(array(
        'align_double_arrow',
        'align_equals',
        'braces',
        'concat_without_spaces',
        'duplicate_semicolon',
        'elseif',
        'encoding',
        'eof_ending',
        'extra_empty_lines',
        'function_call_space',
        'function_declaration',
        'header_comment',
        'include',
        'indentation',
        'line_after_namespace',
        'linefeed',
        'lowercase_constants',
        'lowercase_keywords',
        'method_argument_space',
        'multiline_array_trailing_comma',
        'multiple_use',
        'namespace_no_leading_whitespace',
        'new_with_braces',
        'no_blank_lines_after_class_opening',
        'no_empty_lines_after_phpdocs',
        'object_operator',
        'operators_spaces',
        'ordered_use',
        'phpdoc_order',
        'parenthesis',
        'php_closing_tag',
        'single_line_after_imports',
        'phpdoc_params',
        'phpdoc_indent',
        'phpdoc_no_empty_return',
        'phpdoc_no_package',
        'phpdoc_separation',
        'phpdoc_short_description',
        'phpdoc_to_comment',
        'phpdoc_trim',
        'phpdoc_var_without_name',
        'remove_leading_slash_use',
        'remove_lines_between_uses',
        'return',
        'short_tag',
        'single_array_no_trailing_comma',
        'single_blank_line_before_namespace',
        'single_line_after_imports',
        'spaces_before_semicolon',
        'spaces_cast',
        'standardize_not_equal',
        'ternary_spaces',
        'trailing_spaces',
        'unused_use',
        'visibility',
        'whitespacy_lines',
        'unused_use',
        'short_array_syntax',
    ))
    ->finder($finder)
;
