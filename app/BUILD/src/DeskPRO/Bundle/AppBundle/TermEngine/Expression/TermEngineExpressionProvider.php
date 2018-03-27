<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Expression;

use Orb\Util\Arrays;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

/**
 * Class TermEngineExpressionProvider.
 */
class TermEngineExpressionProvider implements ExpressionFunctionProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new ExpressionFunction(
                'flatten_array',
                function ($arguments, $input) {
                    return sprintf('(\Orb\Util\Arrays::flatten(%1$s))', $input);
                },
                function ($arguments, $input) {
                    return Arrays::flatten($input);
                }
            ),
            new ExpressionFunction(
                'check_contains',
                function ($arguments, $input, $op) {

                    // get any args, since we allow arbitrary args
                    $args = func_get_args();
                    $arg_num = func_num_args();
                    $contains = [];
                    for ($i = 0; $i < $arg_num; ++$i) {
                        if ($i > 1) {
                            $contains[] = $args[$i];
                        }
                    }

                    return sprintf(
                        '$helper_pool->getHelper(\'method_check\')->checkContains(%1$s, %1$s, %1$s)',
                        $input,
                        $op,
                        var_export($contains, true)
                    );
                },
                function ($arguments, $input, $op) {

                    // get any args, since we allow arbitrary args
                    $args = func_get_args();
                    $arg_num = func_num_args();
                    $contains = [];
                    for ($i = 0; $i < $arg_num; ++$i) {
                        if ($i > 2) {
                            $contains[] = $args[$i];
                        }
                    }

                    return $arguments['helper_pool']->getHelper('method_check')->checkContains($input, $op, $contains);
                }
            ),
            new ExpressionFunction(
                'check_traverse',
                function ($arguments, $input, $prop_name, $op, $target) {
                    return sprintf(
                        '$helper_pool->getHelper(\'method_check\')->checkTraverse(%s, %s, %s, %s)',
                        $input,
                        $prop_name,
                        $op,
                        $target
                    );
                },
                function ($arguments, $input, $op) {
                    return $arguments['helper_pool']->getHelper('method_check')->checkContains($input, $op);
                }
            ),
            new ExpressionFunction(
                'custom_field_check',
                function ($arguments, $ticket, $field_id, $op, $values, $input) {
                    return sprintf(
                        '$helper_pool->getHelper(\'method_check\')->checkCustomField(%1$s, %1$s, %1$s, %1$s)',
                        $arguments['ticket'],
                        var_export($field_id, true),
                        var_export($op, true),
                        var_export($values, true),
                        var_export($input, true)
                    );
                },
                function ($arguments, $ticket, $field_id, $op, $values, $input) {
                    return $arguments['helper_pool']->getHelper('method_check')->checkCustomField(
                        $arguments['ticket'],
                        $field_id,
                        $op,
                        $values,
                        $input
                    );
                }
            ),
        ];
    }
}
