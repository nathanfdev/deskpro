<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Tickets
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Tickets;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

use \Orb\Util\Arrays;

/**
 * An object that can list changes done to a ticket, compatible with the translator.
 * This is used in the 'updated' email notification to list changes.
 *
 * <code>
 * {% for change in changes %)
 *     {{phrase(change.field)}}: Changed from {{phrase(change.old)}} to {{phrase(change.new)}}
 * {% endfor %}
 * </code>
 */
class TicketChangeSummary implements \IteratorAggregate
{
	protected $changes;

	public function __construct(TicketChangeTracker $tracker)
	{
		// Already done
		if ($this->changes !== null) return;

		$this->changes = array();

		foreach ($tracker->getAllChangedProperties() as $prop => $info) {
			$old_val = $info['old'];
			$new_val = $info['new'];

			$info = false;

			switch ($prop) {
				case 'agent':
					$info = array(
						'field' => 'core_tickets.agent',
						'old' => $old_val['id'] ? App::findEntity('DeskPRO:Person', $old_val['id']) : 'core.unassigned',
						'new' => $new_val['id'] ? App::findEntity('DeskPRO:Person', $new_val['id']) : 'core.unassigned',
					);
					break;

				case 'category':
					$info = array(
						'field' => 'core_tickets.category',
						'old' => $old_val['id'] ? App::findEntity('DeskPRO:TicketCategory', $old_val['id']) : 'core.none',
						'new' => $new_val['id'] ? App::findEntity('DeskPRO:TicketCategory', $new_val['id']) : 'core.none',
					);
					break;

				case 'department':
					$info = array(
						'field' => 'core.department',
						'old' => $old_val['id'] ? App::findEntity('DeskPRO:Department', $old_val['id']) : 'core.none',
						'new' => $new_val['id'] ? App::findEntity('DeskPRO:Department', $new_val['id']) : 'core.none',
					);
					break;

				case 'priority':
					$info = array(
						'field' => 'core_tickets.priority',
						'old' => $old_val['id'] ? App::findEntity('DeskPRO:TicketPriority', $old_val['id']) : 'core.none',
						'new' => $new_val['id'] ? App::findEntity('DeskPRO:TicketPriority', $new_val['id']) : 'core.none',
					);
					break;

				case 'product':
					$info = array(
						'field' => 'core.product',
						'old' => $old_val['id'] ? App::findEntity('DeskPRO:Product', $old_val['id']) : 'core.none',
						'new' => $new_val['id'] ? App::findEntity('DeskPRO:Product', $new_val['id']) : 'core.none',
					);
					break;

				case 'status':
					$info = array(
						'field' => 'core_tickets.status',
						'old' => $old_val,
						'new' => $new_val,
					);
					break;

				case 'hidden_status':
					$info = array(
						'field' => 'cor_tickets.hidden_status',
						'old' => $old_val,
						'new' => $new_val,
					);
					break;
			}

			if ($info) {
				$info['field_type'] = $prop;
				$this->changes[] = $info;
			}
		}
	}


	public function getChanges()
	{
		return $changes;
	}

	public function getIterator()
	{
		return new \ArrayIterator($this->changes);
	}
}