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
namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\Helper;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\TermEngine\TermCompilerHelperInterface;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use Orb\Util\Arrays;

class MethodCheckHelper implements TermCompilerHelperInterface
{
    public function getId()
    {
        return 'method_check';
    }

    /**
     * Pass in as many arguments past $op as you want, all of them are flattened into a single array.
     *
     * @param $real_val
     * @param $op
     *
     * @return bool
     */
    public function checkContains($real_val, $op)
    {
        // get any args, since we allow arbitrary args
        $args     = func_get_args();
        $arg_num  = func_num_args();
        $contains = array();
        for ($i = 0; $i < $arg_num; ++$i) {
            if ($i > 1) {
                $contains[] = $args[$i];
            }
        }

        // now all args are in an array, flatten it
        $contains = Arrays::flatten($contains);

        if (strtolower($op) !== strtolower(TermInterface::OP_NOT)) {
            return in_array($real_val, $contains);
        }

        return !in_array($real_val, $contains);
    }

    protected function canTraverse($thing)
    {
        return (is_array($thing) || (is_object($thing) && $thing instanceof \Traversable));
    }

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
}
