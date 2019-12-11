<?php
define('PHP_CS_SRC_DIR', realpath(__DIR__ . '/../app/BUILD'));

$header = '';

$finder = PhpCsFixer\Finder::create()
    ->exclude('vendor')
    ->exclude('vendor-src')
    ->exclude('config_new')
    ->exclude('opcache-gui.php')
    ->in(PHP_CS_SRC_DIR)
;

return PhpCsFixer\Config::create()
    ->setUsingCache(true)
    ->setRules(array(
        'binary_operator_spaces' => ['align_double_arrow' => true, 'align_equals' => true,],
        'braces' => true,
        'concat_space' => ['spacing' => 'none'],
        'no_empty_statement' => true,
        'elseif' => true,
        'encoding' => true,
        'single_blank_line_at_eof' => true,
        'no_extra_blank_lines' => true,
        'function_typehint_space' => true,
        'no_spaces_after_function_name' => true,
        'function_declaration' => true,
        'header_comment' => ['header' => $header],
        'include' => true,
        'indentation_type' => true,
        'blank_line_after_namespace' => true,
        'blank_line_before_statement' => true,
        'lowercase_constants' => true,
        'lowercase_keywords' => true,
        'method_argument_space' => true,
        'trailing_comma_in_multiline_array' => true,
        'no_leading_namespace_whitespace' => true,
        'new_with_braces' => true,
        'no_blank_lines_after_class_opening' => true,
        'no_blank_lines_after_phpdoc' => true,
        'object_operator_without_whitespace' => true,
        'ordered_imports' => true,
        'phpdoc_order' => true,
        'no_spaces_inside_parenthesis' => true,
        'no_closing_tag' => true,
        'single_line_after_imports' => true,
        'phpdoc_add_missing_param_annotation' => true,
        'phpdoc_indent' => true,
        'phpdoc_no_empty_return' => true,
        'phpdoc_no_package' => true,
        'phpdoc_separation' => true,
        'phpdoc_to_comment' => true,
        'phpdoc_trim' => true,
        'phpdoc_var_without_name' => true,
        'no_leading_import_slash' => true,
        'full_opening_tag' => true,
        'no_short_echo_tag' => true,
        'no_trailing_comma_in_singleline_array' => true,
        'single_blank_line_before_namespace' => true,
        'single_line_after_imports' => true,
        'space_after_semicolon' => true,
        'cast_spaces' => true,
        'standardize_not_equals' => true,
        'ternary_operator_spaces' => true,
        'no_trailing_whitespace' => true,
        'no_trailing_whitespace_in_comment' => true,
        'no_unused_imports' => true,
        'visibility_required' => true,
        'array_syntax' => ['syntax' => 'short'],
    ))
    ->setFinder($finder)
;
