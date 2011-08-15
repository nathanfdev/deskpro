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

use Doctrine\ORM\Mapping as ORM_Mapping;

use \Application\DeskPRO\App;
use Application\DeskPRO\UI\RuleBuilder;

/**
 * Ticket queues
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\TicketFilter")
 * @ORM_Mapping\Table(name="ticket_filters",
 *     uniqueConstraints={@ORM_Mapping\UniqueConstraint(name="sys_name_unique", columns={"sys_name"})}
 * )
 */
class TicketFilter extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 * 
	 */
	protected $id = null;

	/**
	 * Always who created the filter. Or if its not a team or global,
	 * also means the person it belongs to.
	 * 
	 * @var \Application\DeskPRO\Entity\Person
	 * @ORM_Mapping\ManyToOne(targetEntity="Person")
	 * @ORM_Mapping\JoinColumn(name="person_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $person = null;

	/**
	 * If this is a team filter, the team it belongs to.
	 *
	 * @var \Application\DeskPRO\Entity\AgentTeam
	 * @ORM_Mapping\ManyToOne(targetEntity="AgentTeam")
	 * @ORM_Mapping\JoinColumn(name="agent_team_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $agent_team = null;

	/**
	 * @var bool
	 * @ORM_Mapping\Column(name="is_global", type="boolean")
	 */
	protected $is_global = false;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="title", type="string", length=255)
	 */
	protected $title;

	/**
	 * @var bool
	 * @ORM_Mapping\Column(name="is_enabled", type="boolean")
	 */
	protected $is_enabled = true;

	/**
	 * @var bool
	 * @ORM_Mapping\Column(name="sys_name", type="string", length="50", nullable=true)
	 */
	protected $sys_name = null;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="terms", type="array")
	 */
	protected $terms;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="group_by", type="string", length=255)
	 */
	protected $group_by = '';

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="order_by", type="string", length=255)
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
		$user_searcher = new \Application\DeskPRO\Searcher\PersonSearch();
		$has_user_terms = false;

		foreach ($this->terms as $term) {
			if (strpos($term['type'], 'person_') === 0) {
				$user_searcher->addTerm($term['type'], $term['op'], $term['options']);
				$has_user_terms = true;
			} else {
				$searcher->addTerm($term['type'], $term['op'], $term['options']);
			}
		}

		if ($has_user_terms) {
			$searcher->setPersonSearch($user_searcher);
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

		// !TODO make person be passed in directly to this method
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