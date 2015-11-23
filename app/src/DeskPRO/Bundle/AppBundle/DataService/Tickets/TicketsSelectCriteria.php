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
namespace DeskPRO\Bundle\AppBundle\DataService\Tickets;

use DeskPRO\Bundle\AppBundle\Entity\TicketStar;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\Agent\AgentTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\CompositeTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\Person\PersonTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketFlagged\TicketFlaggedTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketLabel\TicketLabelTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketStatus\TicketStatusTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * TicketsSelectCriteria.
 *
 * This class is responsible for mapping of tickets search API GET parameters into Term used by the Term Engine. It
 * doesn't implement CriteriaInterface, because tickets search is a special case and happens via the Term Engine, not
 * via Doctrine.
 */
class TicketsSelectCriteria
{
    public static function createTerm(array $parameters)
    {
        $composite = new CompositeTerm([], TermInterface::OP_AND);

        foreach ($parameters as $param => $value) {
            switch ($param) {
                case 'labels':
                    $composite->addTerm(new TicketLabelTerm(['label' => $value[0], TermInterface::OP_IS]));
                    break;
                case 'star':
                    $composite->addTerm(new TicketFlaggedTerm(['flag' => TicketStar::idToColorName($value)]));
                    break;
                case 'status':
                    $composite->addTerm(new TicketStatusTerm(['status' => $value]));
                    break;
                case 'agent':
                    $composite->addTerm(new AgentTerm(['agent_ids' => [$value]]));
                    break;
                case 'person':
                    $composite->addTerm(new PersonTerm(['person_ids' => [$value]]));
                    break;
                case 'organization':
                    // ...
                    break;
                case 'problem':
                    // ...
                    break;
                default:
                    throw new \Exception("Unknown ticket filtering option $param");
            }
        }

        return $composite;
    }
}
