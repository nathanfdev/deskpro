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

class TicketSnippet extends \Doctrine\ORM\EntityRepository
{
	public function getSnippetsForAgent(PersonEntity $agent)
	{
		$agent->loadHelper('AgentTeam');
		$agent_teams = $agent->getAgentTeamIds();

		$dql = "
			SELECT s
			FROM DeskPRO:TicketSnippet s
			WHERE
				s.person = ?1
				OR s.is_global = true
		";

		$coll = $this->getEntityManager()->createQuery($dql)
			->setParameter(1, $agent)
			->execute();

		if (!$coll) return array();

		return $this->groupSnippetCollection($coll);
	}

	public function groupSnippetCollection($collection)
	{
		$ret = Arrays::groupItems($collection, 'category');
		ksort($ret, SORT_STRING);

		return $ret;
	}
}