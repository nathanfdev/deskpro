<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Tickets
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Tickets\TicketChangeInspector;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketFilter;
use Application\DeskPRO\Entity\ClientMessage;

use Application\DeskPRO\Tickets\TicketChangeTracker;
use Application\DeskPRO\Tickets\TicketChangeInspector\DetectFilterMatches;

use Orb\Log\Logger;

class ListUpdater
{
	/**
	 * @var \TicketChangeTracker\DeskPRO\Tickets\TicketListener
	 */
	protected $tracker;

	/**
	 * @var \Application\DeskPRO\Tickets\TicketChangeInspector\DetectFilterMatches
	 */
	protected $filter_detector;

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $em;

	public function __construct(TicketChangeTracker $tracker, DetectFilterMatches $filter_detector)
	{
		$this->tracker = $tracker;
		$this->filter_detector = $filter_detector;
		$this->em = App::getOrm();
	}

	/**
	 * Runs the checks, inserts the client messages if there are any.
	 */
	public function run()
	{
		$this->tracker->logMessage('[ListUpdater] run');

		$filter_changes = $this->filter_detector->getFilterMatches();
		$ticket_id = $this->tracker->getTicket()->id;

		$online_agents = $this->em->getRepository('DeskPRO:Person')->getActiveAgents(true);

		$count_adds = 0;
		$count_dels = 0;

		$this->em->beginTransaction();
		try {
			foreach ($filter_changes as $change_info) {
				$filter = $change_info['filter'];

				foreach ($change_info['add'] as $agent) {
					if (!isset($online_agents[$agent->id])) continue;

					$count_adds++;

					$cm = new ClientMessage();
					$cm->fromArray(array(
						'channel' => 'agent.filter-update',
						'data' => array(
							'ticket_id'  => $ticket_id,
							'filter_id'  => $filter['id'],
							'op' => 'add'
						),
						'for_person' => $agent,
						'created_by_client' => 'sys'
					));
					$this->em->persist($cm);
				}
				foreach ($change_info['del'] as $agent) {
					if (!isset($online_agents[$agent->id])) continue;

					$count_dels++;

					$cm = new ClientMessage();
					$cm->fromArray(array(
						'channel' => 'agent.filter-update',
						'data' => array(
							'ticket_id'  => $ticket_id,
							'filter_id'  => $filter['id'],
							'op' => 'del'
						),
						'for_person' => $agent,
						'created_by_client' => 'sys'
					));
					$this->em->persist($cm);
				}
			}

			$this->em->flush();
			$this->em->commit();
		} catch (\Exception $e) {
			$this->em->rollback();
			throw $e;
		}

		$this->tracker->logMessage("[ListUpdater] Done with $count_adds adds and $count_dels dels messages sent");
	}
}
