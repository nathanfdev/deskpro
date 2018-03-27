<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\Helper;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class PhpDateHelper.
 */
class PhpDateHelper extends AbstractPhpHelper
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'date';
    }

    /**
     * @param $field_name
     * @param $op
     * @param \DateTime      $date1
     * @param \DateTime|null $date2
     * @param bool           $ignore_time
     *
     * @throws \Exception
     *
     * @return PhpCheck
     */
    public function buildQueryPart($field_name, $op, \DateTime $date1, \DateTime $date2 = null, $ignore_time = false)
    {
        if (TermInterface::OP_RANGE == $op) {
            $expression = sprintf(
                '%s >= :date1 and %s <= :date2',
                $field_name,
                $field_name
            );
        } elseif (TermInterface::OP_NOT_RANGE == $op) {
            $expression = sprintf(
                '%s < :date1 or %s > :date2',
                $field_name,
                $field_name
            );
        } else {
            switch ($op) {
                case TermInterface::OP_IS:
                    $expression_op = '==';
                    break;
                case TermInterface::OP_NOT:
                    $expression_op = '!=';
                    break;
                case TermInterface::OP_GT:
                    $expression_op = '>';
                    break;
                case TermInterface::OP_GTE:
                    $expression_op = '>=';
                    break;
                case TermInterface::OP_LT:
                    $expression_op = '<';
                    break;
                case TermInterface::OP_LTE:
                    $expression_op = '<=';
                    break;
                default:
                    throw new \Exception('Uknown operation: '.$op);
            }

            $expression = sprintf(
                '%s %s :date1',
                $field_name,
                $expression_op
            );
        }

        return new PhpCheck(
                $expression,
            [
                'date1' => $date1,
                'date2' => $date2,
            ]
        );
    }
}
