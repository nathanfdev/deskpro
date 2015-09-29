<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\TermEngine\Expression;

use Orb\Util\Arrays;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

class TermEngineExpressionProvider implements ExpressionFunctionProviderInterface
{
    /**
     * @return ExpressionFunction[] An array of Function instances
     */
    public function getFunctions()
    {
        return array(
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
                    $contains = array();
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
                    $contains = array();
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
        );
    }
}
