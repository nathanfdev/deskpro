<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\Helper;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class PhpStringHelper.
 */
class PhpStringHelper extends AbstractPhpHelper
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'string';
    }

    /**
     * @param $field_name
     * @param $op
     * @param array $strings
     * @param bool  $wildcard_prefix
     * @param bool  $wildcard_postfix
     *
     * @return PhpCheck
     */
    public function buildQueryPart($field_name, $op, array $strings, $wildcard_prefix = false, $wildcard_postfix = false)
    {
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
