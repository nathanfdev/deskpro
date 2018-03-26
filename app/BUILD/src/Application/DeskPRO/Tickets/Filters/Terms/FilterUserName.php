<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Filters\Terms;

use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\CheckedOptionsArray;

/**
 * Filters based on ticket user name.
 *
 * @option string name
 */
class FilterUserName extends AbstractFilterTerm
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('name');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterQuery(ExecutorContextInterface $context = null)
    {
        $options     = $this->getTermOptions();
        $check_value = $options['name'];
        $like_value  = null;

        switch ($this->getTermOperator()) {
            case self::OP_IS:
            case self::OP_NOT:
                break;
            case self::OP_NOT:
            case self::OP_CONTAINS:
                $like_value = $check_value;
                $like_value = str_replace('%', '%%', $like_value);
                $like_value = str_replace('_', '__', $like_value);
                $like_value = '%'.$like_value.'%';
                break;
            default:
                throw new \InvalidArgumentException("Invalid operator: {$this->getTermOperator()}");
        }

        $query = new FilterQuery();

        foreach (['name', 'first_name', 'last_name'] as $k => $field_name) {
            switch ($this->getTermOperator()) {
                case self::OP_IS:
                case self::OP_NOT:
                    $use_op = $this->getTermOptions() == self::OP_NOT ? '!=' : '=';
                    $query->orWhere("$field_name $use_op {param.str$k}");
                    $query->setParameter('str'.$k, $check_value);
                    break;
                case self::OP_NOT:
                case self::OP_CONTAINS:
                    $use_op = $this->getTermOptions() == self::OP_NOT ? 'NOT LIKE' : 'LIKE';
                    $query->orWhere("$field_name $use_op {param.str$k}");
                    $query->setParameter('str'.$k, $like_value);
                    break;
            }
        }

        return $query;
    }
}
