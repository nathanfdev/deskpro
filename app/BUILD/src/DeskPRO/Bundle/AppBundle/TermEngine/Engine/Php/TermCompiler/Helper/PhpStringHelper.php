<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\Helper;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

class PhpStringHelper extends AbstractPhpHelper
{
    /**
     * An identifier for this helper.
     *
     * @return string
     */
    public function getId()
    {
        return 'string';
    }

    public function buildQueryPart($field_name, $op, array $strings, $wildcard_prefix = false, $wildcard_postfix = false)
    {
        $expression = '';

        $strings = array_unique(array_map('strval', $strings));

        if ($wildcard_prefix || $wildcard_postfix) {
            if (TermInterface::OP_IS === $op) {
                $op = TermInterface::OP_HAS;
            } elseif (TermInterface::OP_NOT === $op) {
                $op = TermInterface::OP_NOT_HAS;
            }
        } elseif (TermInterface::OP_HAS === $op || TermInterface::OP_NOT_HAS === $op) {
            $wildcard_postfix = $wildcard_prefix = true;
        }

        switch ($op) {
            case TermInterface::OP_NOT:
                $sql_op = ' != ';
                break;
            case TermInterface::OP_HAS:
                $sql_op = ' LIKE ';
                break;
            case TermInterface::OP_NOT_HAS:
                $sql_op = ' NOT LIKE ';
                break;
            case TermInterface::OP_IS:
            default:
                $sql_op = ' = ';
                break;
        }

        $parts = $values = [];
        foreach ($strings as $k => $string) {
            $sub_expression = '';

            // All regex-based matching logic.
            if ($wildcard_prefix || $wildcard_postfix
                || TermInterface::OP_HAS === $op || TermInterface::OP_NOT_HAS === $op) {
                $regex = preg_quote($string);
                if ($wildcard_prefix) {
                    $regex .= '$';
                }
                if ($wildcard_postfix) {
                    $regex = '^'.$regex;
                }

                $sub_expression = sprintf(
                    '(%s matches "/%s/")',
                    $field_name,
                    $regex
                );

                if (TermInterface::OP_NOT_HAS === $op) {
                    $sub_expression = 'not '.$sub_expression;
                }
            } else {
                $real_op = '==';
                if (TermInterface::OP_IS === $op) {
                    $real_op = '==';
                } elseif (TermInterface::OP_NOT) {
                    $real_op = '!=';
                }

                $sub_expression = sprintf(
                    '(%s %s :string%d)',
                    $field_name,
                    $real_op,
                    $k
                );
                $values['string'.$k] = $string;
            }

            $parts[] = $sub_expression;
        }

        $and_or     = (TermInterface::OP_NOT === $op || TermInterface::OP_NOT_HAS === $op) ? ' and ' : ' or ';
        $expression = implode($and_or, $parts);

        return new PhpCheck(
            $expression,
            $values
        );
    }
}
