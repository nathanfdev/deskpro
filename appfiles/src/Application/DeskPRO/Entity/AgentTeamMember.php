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

namespace Application\DeskPRO\Entity;

use \Application\DeskPRO\App;

use Orb\Util\Strings;
use Orb\Util\Arrays;

use \Application\DeskPRO\Entity;

/**
 * An agent team is a group of agents. Similar to usergroups but for agents.
 *
 * @orm:Entity
 * @orm:Table(name="agent_team_members2")
 */
class AgentTeamMember extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id
	 * @orm:ManyToOne(targetEntity="Person")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $agent;


	/**
	 * @var string
	 * @orm:Id
	 * @orm:ManyToOne(targetEntity="AgentTeam")
	 * @orm:JoinColumn(name="team_id", referencedColumnName="id")
	 */
	protected $team;
}