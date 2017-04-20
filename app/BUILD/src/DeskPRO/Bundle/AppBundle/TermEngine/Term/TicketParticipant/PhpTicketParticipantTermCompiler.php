<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketParticipant;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\AbstractPhpTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class PhpTicketParticipantTermCompiler.
 */
class PhpTicketParticipantTermCompiler extends AbstractPhpTermCompiler
{
    /**
     * {@inheritdoc}
     */
    protected function doCompile(TermInterface $term)
    {
        $op  = $term->getOp();
        $ids = $term->getOption('person_ids');

        $use_me  = false;
        $use_ids = [];
        foreach ($ids as $id) {
            if ($id == TicketParticipantTerm::ID_ME) {
                $use_me = 'agent.getId()';
            } else {
                $use_ids[] = (int) $id;
            }
        }

        if ($this->isOp($op, TermInterface::OP_IS)) {
            $prefix = '';
        } else {
            $prefix = 'not ';
        }

        if ($use_me && count($use_ids)) {
            $check = new PhpCheck($prefix.'ticket.hasAnyParticipantId([:ids, agent.getId()])');
            $check->setVariable('ids', $use_ids);
        } elseif ($use_me) {
            $check = new PhpCheck($prefix.'ticket.hasAnyParticipantId([agent.getId()])');
        } else {
            $check = new PhpCheck($prefix.'ticket.hasAnyParticipantId(:ids)');
            $check->setVariable('ids', $use_ids);
        }

        return $check;
    }
}
