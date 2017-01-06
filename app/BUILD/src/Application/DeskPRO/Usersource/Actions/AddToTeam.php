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

namespace Application\DeskPRO\Usersource\Actions;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\EntityRepository\AgentTeam;

class AddToTeam extends AbstractAction
{
    protected $teamId;

    public function getData()
    {
        return $this->teamId;
    }

    public function setData($value)
    {
        if (!is_numeric($value)) {
            throw new \Exception('Invalid data type for AddToTeam usersource action');
        }

        $this->teamId = (int) $value;
    }

    protected function doHandle(DeskproContainer $container, Person $person, array $rawInput)
    {
        /** @var AgentTeam $teamsRep */
        $teamsRep = $container->getEm()->getRepository('DeskPRO:AgentTeam');
        $teams    = $teamsRep->getTeamsFromIds([$this->getData()]);
        if ($team = reset($teams)) {
            $person->addTeam($team);
        }
    }
}
