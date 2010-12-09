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

/**
 * Ticket queues
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\TicketQueue")
 * @orm:Table(name="ticket_queues")
 */
class TicketQueue extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * @var int
	 * @orm:Column(name="person_id", type="integer")
	 */
	protected $person_id = null;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person = null;

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
	 * @orm:Column(name="is_global", type="boolean")
	 */
	protected $is_global = false;

	/**
	 * @var string
	 * @orm:Column(name="terms", type="array")
	 */
	protected $terms;

	/**
	 * Results from the last search
	 * @var array
	 */
	protected $_results = null;



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
			$data = $term;
			unset($data['rule_type'], $data['op']);

			if (count($data) == 1) {
				$data = array_pop($data);
			}

			$searcher->addTerm($term['rule_type'], $term['op'], $data);
		}

		return $searcher;
	}


	public function getResults()
	{
		if ($this->_results !== null) return $this->_results;

		$searcher = $this->getSearcher();

		//TODO remove when ready for real searches
		$searcher->enableArchiveSearch();

		$this->_results = $searcher->getMatches();
		
		return $this->_results;
	}

	public function getResultsCount()
	{
		return count($this->getResults());
	}
}