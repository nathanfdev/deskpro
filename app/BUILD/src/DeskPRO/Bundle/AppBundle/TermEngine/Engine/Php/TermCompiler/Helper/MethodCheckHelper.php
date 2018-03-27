<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\Helper;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\TermEngine\TermCompilerHelperInterface;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use Orb\Util\Arrays;

/**
 * Class MethodCheckHelper.
 */
class MethodCheckHelper implements TermCompilerHelperInterface
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'method_check';
    }

    /**
     * Pass in as many arguments past $op as you want, all of them are flattened into a single array.
     *
     * @param $realVal
     * @param $op
     *
     * @return bool
     */
    public function checkContains($realVal, $op)
    {
        // get any args, since we allow arbitrary args
        $args     = func_get_args();
        $arg_num  = func_num_args();
        $contains = [];
        for ($i = 0; $i < $arg_num; ++$i) {
            if ($i > 1) {
                $contains[] = $args[$i];
            }
        }

        // now all args are in an array, flatten it
        $contains = Arrays::flatten($contains);
        if (!is_array($realVal)) {
            $realVal = [$realVal];
        }

        if (strtolower($op) !== strtolower(TermInterface::OP_NOT)) {
            return array_intersect($realVal, $contains);
        }

        return !array_intersect($realVal, $contains);
    }

    /**
     * @param $val
     * @param $property_name
     * @param $op
     * @param $target
     *
     * @return bool
     */
    public function checkTraverse($val, $property_name, $op, $target)
    {
        if ($this->canTraverse($val)) {
            return false;
        }

        foreach ($val as $key => $value) {
            if ($key == $property_name) {
                if (TermInterface::OP_NOT === $op || TermInterface::OP_NOT_HAS === $op) {
                    return $value != $target;
                } else {
                    return $value == $target;
                }
            } elseif ($this->canTraverse($value)) {
                $result = $this->checkTraverse($value, $property_name, $op, $target);
                if ($result === false || $result === true) {
                    return $result;
                }
            }
        }

        return;
    }

    /**
     * @param Ticket $ticket
     * @param $field_id
     * @param $op
     * @param $values
     * @param $input
     *
     * @return bool
     */
    public function checkCustomField(Ticket $ticket, $field_id, $op, $values, $input)
    {
        $is = strtolower($op) === strtolower(TermInterface::OP_IS);

        if (!$ticket->hasCustomField($field_id)) {
            return !$is;
        }

        if (!$custom_data = $ticket->getCustomDataForField($field_id)) {
            return !$is;
        }

        if ($input) {
            if ($custom_data->getInput() == $input) {
                return $is;
            } else {
                return !$is;
            }
        }

        if (in_array($custom_data->getValue(), Arrays::flatten($values))) {
            return $is;
        } else {
            return !$is;
        }
    }

    /**
     * @param $thing
     *
     * @return bool
     */
    protected function canTraverse($thing)
    {
        return is_array($thing) || (is_object($thing) && $thing instanceof \Traversable);
    }
}
