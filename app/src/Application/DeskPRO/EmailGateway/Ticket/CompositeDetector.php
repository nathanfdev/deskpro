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
 */

namespace Application\DeskPRO\EmailGateway\Ticket;

use Application\DeskPRO\EmailGateway\Reader\AbstractReader;
use Application\DeskPRO\Entity\Ticket;
use Orb\Log\Logger;
use Orb\Util\Util;

class CompositeDetector implements TicketDetectorInterface, BounceAwareInterface, TacPersonDetectorInterface
{
	/**
	 * @var \Application\DeskPRO\EmailGateway\Ticket\TicketDetectorInterface[]
	 */
	private $detectors = array();

	/**
	 * @var bool
	 */
	private $is_bounce_mode = false;

	/**
	 * @var \Application\DeskPRO\EmailGateway\Ticket\TicketDetectorInterface[]
	 */
	private $matched_detectors = array();

	/**
	 * @var \Application\DeskPRO\Entity\Ticket[]
	 */
	private $matched_tickets = array();

	/**
	 * @var \Orb\Log\Logger
	 */
	private $logger;


	/**
	 * @param TicketDetectorInterface $detector
	 */
	public function addDetector(TicketDetectorInterface $detector)
	{
		$this->detectors[] = $detector;
		if (method_exists($detector, 'setLogger')) {
			$detector->setLogger($this->getLogger());
		}
	}


	/**
	 * Runs detectors against a reader
	 *
	 * @param AbstractReader $reader
	 */
	private function runDetectors(AbstractReader $reader)
	{
		$reader_id = spl_object_hash($reader);

		foreach ($this->detectors as $detector) {
			if ($this->is_bounce_mode && $detector instanceof BounceAwareInterface) {
				$detector->enableBouncedMode();
			}

			$t = $detector->findExistingTicket($reader);
			if ($t) {
				$this->matched_detectors[$reader_id] = $detector;
				$this->matched_tickets[$reader_id]   = $t;
				$this->getLogger()->logInfo(sprintf("[CompositeDetector] %s: Found Ticket #%d", Util::getBaseClassname($detector), $t->id));
				return;
			} else {
				$this->getLogger()->logInfo(sprintf("[CompositeDetector] %s: No match", Util::getBaseClassname($detector)));
			}
		}

		$this->matched_detectors[$reader_id] = false;
		$this->matched_tickets[$reader_id]   = false;
	}


	/**
	 * Reset the saved detector state
	 */
	public function reset()
	{
		$this->matched_detectors = array();
		$this->matched_tickets   = array();
		$this->is_bounce_mode    = false;
	}


	/**
	 * @param AbstractReader $reader
	 * @return \Application\DeskPRO\EmailGateway\Ticket\TicketDetectorInterface|null
	 */
	public function getMatchedDetector(AbstractReader $reader)
	{
		$reader_id = spl_object_hash($reader);
		if (!isset($this->matched_detectors[$reader_id])) {
			$this->runDetectors($reader);
		}

		if ($this->matched_detectors[$reader_id] === false) {
			return null;
		}

		return $this->matched_detectors[$reader_id];
	}


	/**
	 * {@inheritDoc}
	 */
	public function enableBouncedMode()
	{
		$this->is_bounce_mode = true;
	}


	/**
	 * {@inheritDoc}
	 */
	public function findExistingTicket(AbstractReader $reader)
	{
		$this->getMatchedDetector($reader);

		$reader_id = spl_object_hash($reader);
		if ($this->matched_tickets[$reader_id] === false) {
			return null;
		}

		return $this->matched_tickets[$reader_id];
	}


	/**
	 * {@inheritDoc}
	 */
	public function findExistingPerson(Ticket $ticket, AbstractReader $reader)
	{
		$detector = $this->getMatchedDetector($reader);

		if (!$detector) {
			return null;
		}

		return $detector->findExistingPerson($ticket, $reader);
	}


	/**
	 * {@inheritDoc}
	 */
	public function canAddUnknownPerson(Ticket $ticket, AbstractReader $reader)
	{
		$detector = $this->getMatchedDetector($reader);
		if (!$detector) {
			return false;
		}

		return $detector->canAddUnknownPerson($ticket, $reader);
	}


	/**
	 * {@inheritDoc}
	 */
	public function findTacPerson(AbstractReader $reader)
	{
		$detector = $this->getMatchedDetector($reader);
		if (!$detector || !($detector instanceof TacPersonDetectorInterface)) {
			return null;
		}

		return $detector->findTacPerson($reader);
	}


	/**
	 * Set the logger
	 * @param \Orb\Log\Logger $logger
	 */
	public function setLogger(Logger $logger)
	{
		$this->logger = $logger;
	}


	/**
	 * @return \Orb\Log\Logger
	 */
	public function getLogger()
	{
		if (!$this->logger) {
			$this->logger = new Logger();
		}

		return $this->logger;
	}
}