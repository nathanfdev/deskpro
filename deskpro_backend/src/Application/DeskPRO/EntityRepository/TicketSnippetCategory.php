<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;

use Application\DeskPRO\Entity\Person as PersonEntity;

use Orb\Util\Arrays;

class TicketSnippetCategory extends \Doctrine\ORM\EntityRepository
{
	public function getCatsForAgent(PersonEntity $agent)
	{
		$agent->loadHelper('AgentTeam');
		$agent_teams = $agent->getAgentTeamIds();

		$dql = "
			SELECT c
			FROM DeskPRO:TicketSnippetCategory c
			WHERE
				c.person = ?1
				OR c.is_global = true
			ORDER BY c.title ASC
		";

		$coll = $this->getEntityManager()->createQuery($dql)
			->setParameter(1, $agent)
			->execute();

		return $coll;
	}
}