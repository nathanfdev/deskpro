<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\CoreBundle\Entity;

/**
 * Ticket queues
 *
 * @orm:Entity
 * @orm:Table(name="ticket_queues")
 */
class TicketQueue extends \DeskPRO\Domain\DomainObject
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
	 * @var \Application\CoreBundle\Entity\Person
	 * @orm:OneToOne(targetEntity="Person")
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
	 * Get the searcher for this.
	 * 
	 * @return DeskPRO\Searcher\TicketSearch
	 */
	public function getSearcher()
	{
		$searcher = new \DeskPRO\Searcher\TicketSearch();

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
}