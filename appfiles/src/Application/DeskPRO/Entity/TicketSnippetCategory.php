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

use \Orb\Util\Arrays;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Application\DeskPRO\Tickets\TicketActions\ActionsFactory;
use Application\DeskPRO\Tickets\TicketActions\ActionsCollection;

/**
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\TicketSnippetCategory")
 * @orm:Table(name="ticket_snippet_categories")
 */
class TicketSnippetCategory extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 */
	protected $id = null;
	
	/**
	 * Who created the cat
	 *
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $person = null;

	/**
	 * Teams who can use this snippet
	 *
	 * @var Doctrine\Common\Collections\ArrayCollection
	 * @orm:ManyToMany(targetEntity="AgentTeam", cascade={"persist", "remove", "merge"})
     * @orm:JoinTable(name="ticket_snippetcat_to_team", joinColumns={@orm:JoinColumn(name="team_id", referencedColumnName="id", onDelete="cascade")}, inverseJoinColumns={@orm:JoinColumn(name="team_id", referencedColumnName="id", onDelete="cascade")})
	 */
	protected $agent_teams = null;

	/**
	 * Everyone can see it?
	 *
	 * @var bool
	 * @orm:Column(name="is_global", type="boolean")
	 */
	protected $is_global = false;

	/**
	 * @var string
	 * @orm:Column(name="title", type="string", length=255)
	 */
	protected $title;

	public function __construct()
	{
		$this->agent_teams = new \Doctrine\Common\Collections\ArrayCollection();
	}
}