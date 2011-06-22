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
use Application\DeskPRO\UI\RuleBuilder;

/**
 * Ticket queues
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\TicketFilter")
 * @orm:Table(name="ticket_filters",
 *     uniqueConstraints={@orm:UniqueConstraint(name="sys_name_unique", columns={"sys_name"})}
 * )
 */
class TicketFilter extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * Always who created the filter. Or if its not a team or global,
	 * also means the person it belongs to.
	 * 
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person = null;

	/**
	 * If this is a team filter, the team it belongs to.
	 *
	 * @var \Application\DeskPRO\Entity\AgentTeam
	 * @orm:ManyToOne(targetEntity="AgentTeam")
	 * @orm:JoinColumn(name="agent_team_id", referencedColumnName="id")
	 */
	protected $agent_team = null;

	/**
	 * @var bool
	 * @orm:Column(name="is_global", type="boolean")
	 */
	protected $is_global = false;

	/**
	 * @var string
	 * @orm:Column(name="title", type="string", length=255)
	 */
	protected $title;

	/**
	 * @var bool
	 * @orm:Column(name="is_enabled", type="boolean")
	 */
	protected $is_enabled = true;

	/**
	 * @var bool
	 * @orm:Column(name="sys_name", type="string", length="50", nullable=true)
	 */
	protected $sys_name = null;

	/**
	 * @var string
	 * @orm:Column(name="terms", type="array")
	 */
	protected $terms;

	/**
	 * @var string
	 * @orm:Column(name="group_by", type="string", length=255)
	 */
	protected $group_by = '';

	/**
	 * @var string
	 * @orm:Column(name="order_by", type="string", length=255)
	 */
	protected $order_by = '';

	/**
	 * Results from the last search
	 * @var array
	 */
	protected $_results = null;

	public function getPersonId()
	{
		if ($this->person === null) {
			return 0;
		}

		return $this->person['id'];
	}

	public function setPersonId($id)
	{
		if ($id) {
			$this->person = App::getEntityRepository('DeskPRO:Person')->find($id);
		} else {
			$this->person = null;
		}
	}

	public function getAgentTeamId()
	{
		if (!$this->agent_team) {
			return 0;
		}
		return $this->agent_team['id'];
	}

	public function setAgentTeamId($id)
	{
		if ($id) {
			$agent_team = App::getOrm()->getRepository('DeskPRO:AgentTeam')->find($id);
			$this['agent_team'] = $agent_team;
		} else {
			$this['agent_team'] = null;
		}
	}



	/**
	 * Reset results so next calls will re-do the search.
	 */
	public function resetResults()
	{
		$this->_results = null;
	}



	/**
	 * Get the searcher for this.
	 *
	 * @return Application\DeskPRO\Searcher\TicketSearch
	 */
	public function getSearcher()
	{
		$searcher = new \Application\DeskPRO\Searcher\TicketSearch();
		foreach ($this->terms as $term) {
			$searcher->addTerm($term['type'], $term['op'], $term['options']);
		}

		return $searcher;
	}


	/**
	 * Get an array of criteria phrases.
	 *
	 * @return array
	 */
	public function getSummaryParts()
	{
		return $this->getSearcher()->getSummary();
	}


	/**
	 * Explain criteria in the filter. Ex: Agent is Unassigned, Category is None
	 *
	 * @return string
	 */
	public function getSummaryPhrase()
	{
		return implode(', ', $this->getSummaryParts());
	}


	public function getResults(Person $person = null)
	{
		if ($this->_results !== null) return $this->_results;

		$searcher = $this->getSearcher();

		// TODO make person be passed in directly to this method
		if (!$person) {
			$person = App::getCurrentPerson();
		}
		$searcher->setPerson(App::getCurrentPerson());

		$order_by = $person->getPref('agent.ui.ticket-filter-order-by.' . $this->id);
		if (!$order_by AND $this->order_by) {
			$order_by = $this->order_by;
		}

		if ($order_by) {
			$searcher->setOrderByCode($order_by);
		}

		$this->_results = $searcher->getMatches();

		return $this->_results;
	}

	public function getResultsCount()
	{
		return count($this->getResults());
	}
}