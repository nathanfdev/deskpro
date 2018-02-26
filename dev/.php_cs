<?php
define('PHP_CS_SRC_DIR', realpath(__DIR__ . '/../app/BUILD'));

$year   = date('Y');
$header = <<< EOF
Deskpro (r) has been developed by Deskpro Ltd. https://www.deskpro.com/
a British company located in London, England.

All source code and content Copyright (c) $year, Deskpro Ltd.

The license agreement under which this software is released
can be found at https://www.deskpro.com/eula/

By using this software, you acknowledge having read the license
and agree to be bound thereby.

Please note that Deskpro is not free software. We release the full
source code for our software because we trust our users to pay us for
the huge investment in time and energy that has gone into both creating
this software and supporting our customers. By providing the source code
we preserve our customers' ability to modify, audit and learn from our
work. We have been developing Deskpro since 2001, please help us make it
another decade.

Like the work you see? Think you could make it better? We are always
looking for great developers to join us: http://www.deskpro.com/jobs/

~ Thanks, Everyone at Team Deskpro
EOF;

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
