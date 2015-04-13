<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at https://www.deskpro.com/eula/                            |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TicketChecker\TermCompiler;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\Dumper\PhpCheck;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\AbstractPhpTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketParticipantTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

class PhpTicketParticipantTermCompiler extends AbstractPhpTermCompiler
{
    /**
     * Take a term and return a PhpCheck representing the term's query conditions.
     *
     * @param TermInterface $term
     * @return PhpCheck
     */
    protected function doCompile(TermInterface $term)
    {
        $op = $term->getOp();
        $ids = $term->getOption('person_ids');

        $use_ids = array();
        foreach ($ids as $id) {
            if ($id == TicketParticipantTerm::ID_ME) {
                $id = '$this->evaluateExpression(\'agent.getId()\')';
            } else {
                $id = (int)$id;
            }
            if ($id) {
                $use_ids[] = $id;
            }
        }

        $sub_checks = array();
        foreach ($use_ids as $id) {
            $sub_checks[] = $this->makeSubCheck($op, $id);
        }

        $check_code = '';
        $check_code .= '$check = (';
        if (count($sub_checks)) {
            $check_code .= implode($this->isOp($op, TermInterface::OP_IS) ? ' || ' : ' && ', $sub_checks);
        } else {
            // no ids were in the array, treat as asking for "no participants"
            $check_code .= '$check = ';
            if ($this->isOp($op, TermInterface::OP_IS)) {
                $check_code .= '!'; // IS no participants
            }
            $check_code .= 'count($ticket->getParticipantPeopleIds())';
        }
        $check_code .= ');';

        return new PhpCheck($check_code);
    }

    protected function makeSubCheck($op, $id)
    {
        $check = '(';
        $check .= '$ticket->hasParticipantPerson(' . $id . ')';
        $check .= ' ';
        if ($this->isOp($op, TermInterface::OP_NOT)) {
            $check .= '!=';
        } else {
            $check .= '==';
        }
        $check .= ' true';
        $check .= ')';

        return $check;
    }
}
