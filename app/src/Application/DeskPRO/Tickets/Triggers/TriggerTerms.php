<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Triggers;

use Application\DeskPRO\Criteria\CriteriaTermInterface;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Tickets\Triggers\Terms\TriggerTermComposite;
use Application\DeskPRO\Tickets\Triggers\Terms\TriggerTermInterface;
use DeskPRO\Kernel\KernelErrorHandler;
use Orb\Types\JsonObjectSerializable;

/**
 * This is a wrapper around a TriggerTermComposite that is able to serialize.
 * Used as the serialized object in TicketTrigger records.
 *
 * While any `TriggerTermInterface` can be used with he trigger system, we can only actually
 * *save* the term to the db if it also implements the standard CriteriaTermInterface which defines
 * a standard interface for getting a term name and options (so we can recreate a term object again).
 */
class TriggerTerms implements \Serializable, TriggerTermInterface, JsonObjectSerializable
{
	/**
	 * @var TriggerTermComposite
	 */
	private $criteria;

	/**
	 * @var TermFactory
	 */
	private $term_factory;

	public function __construct()
	{
		$this->criteria = new TriggerTermComposite();
		$this->criteria->setOperator(TriggerTermComposite::OP_OR);
		$this->term_factory = new TermFactory();
	}


	/**
	 * @param TriggerTermInterface $term
	 * @throws \InvalidArgumentException
	 */
	public function addTerm(TriggerTermInterface $term)
	{
		if (!($term instanceof CriteriaTermInterface) && !($term instanceof TriggerTermComposite)) {
			$class_name = get_class($term);
			throw new \InvalidArgumentException("TriggerCriteria can only manage terms terms that implement CriteriaTermInterface. Invalid class: $class_name");
		}
		$this->criteria->add($term);
	}


	/**
	 * @param array $term_info
	 * @throws \InvalidArgumentException
	 */
	public function addTermFromArray(array $term_info)
	{
		if (isset($term_info['set_terms'])) {
			$composite = new TriggerTermComposite(array(), TriggerTermComposite::OP_AND);
			foreach ($term_info['set_terms'] as $ti) {
				$t = $this->getTermFromArray($ti);
				$composite->add($t);
			}

			$this->addTerm($composite);
		} else {
			$term = $this->getTermFromArray($term_info);
			$this->addTerm($term);
		}
	}


	/**
	 * @param array $term_info
	 * @throws \InvalidArgumentException
	 */
	private function getTermFromArray(array $term_info)
	{
		return $this->term_factory->createFromArray($term_info);
	}


	/**
	 * @param Ticket $ticket
	 * @param ExecutorContextInterface $ticket_changelog
	 * @return bool
	 */
	public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $context)
	{
		return $this->criteria->isTriggerMatch($ticket, $context);
	}


	/**
	 * @return array
	 */
	public function exportToArray()
	{
		$data = array();

		$data['version']  = 1;
		$data['terms'] = array();
		foreach ($this->criteria->getAll() as $criteria) {
			if ($criteria instanceof TriggerTermComposite) {
				$set_terms = array();
				foreach ($criteria->getAll() as $set_criteria) {
					if (!($set_criteria instanceof CriteriaTermInterface)) {
						continue;
					}

					$set_terms[] = array(
						'type'    => $set_criteria->getTermType(),
						'op'      => $set_criteria->getTermOperator(),
						'options' => $set_criteria->getTermOptions()->all()
					);
				}

				if ($set_terms) {
					$data['terms'][] = array(
						'set_terms' => $set_terms
					);
				}
			} else {
				if (!($criteria instanceof CriteriaTermInterface)) {
					continue;
				}

				$data['terms'][] = array(
					'type'    => $criteria->getTermType(),
					'op'      => $criteria->getTermOperator(),
					'options' => $criteria->getTermOptions()->all()
				);
			}
		}

		return $data;
	}


	/**
	 * @param array $data
	 */
	public function importFromArray(array $data)
	{
		foreach ($data['terms'] as $term_info) {
			$this->addTermFromArray($term_info);
		}
	}


	/**
	 * @return string
	 */
	public function exportToJson()
	{
		return json_encode($this->exportToArray());
	}


	/**
	 * @return string
	 */
	public function serialize()
	{
		return $this->exportToJson();
	}


	/**
	 * @return array
	 */
	public function serializeJsonArray()
	{
		return $this->exportToArray();
	}


	/**
	 * @param array $data
	 * @return TriggerTerms
	 */
	public static function unserializeJsonArray(array $data)
	{
		$obj = new self();
		foreach ($data['terms'] as $term_info) {
			try {
				$obj->addTermFromArray($term_info);
			} catch (\Exception $e) {
				if (!empty($term_info['type'])) {
					KernelErrorHandler::logException($e, false, md5('triggerterm_' . $term_info['type']));
				}
			}
		}

		return $obj;
	}


	/**
	 * @param string $data
	 */
	public function unserialize($data)
	{
		$data = json_decode($data, true);
		$this->__construct();

		foreach ($data['terms'] as $term_info) {
			try {
				$this->addTermFromArray($term_info);
			} catch (\Exception $e) {
				if (!empty($term_info['type'])) {
					KernelErrorHandler::logException($e, false, md5('triggerterm_' . $term_info['type']));
				}
			}
		}
	}
}