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

namespace Application\DeskPRO\Tickets\TicketMerge\Property;


use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\Entity\CustomDataTicket;

use Orb\Util\Arrays;

/**
 * Merges custom fields
 */
class CustomField extends PropertyAbstract
{
	/**
	 * @var \Application\DeskPRO\Entity\CustomDefTicket
	 */
	protected $field;

	public function setField(CustomDefTicket $field)
	{
		$this->field = $field;
	}

	public function merge()
	{
		if ($this->strategy == self::STRATEGY_LEFT) {
			return;
		}

		// No children means its a simple field (text input etc)
		if (!count($this->field->children)) {
			if ($this->strategy == self::STRATEGY_RIGHT) {
				$other_exist = $this->other_ticket->getCustomDataForField($this->field);
				if ($other_exist) {

					$this->ticket->removeCustomDataForField($this->field);

					$other_exist->ticket = $this->ticket;
					$this->ticket->custom_data->add($other_exist);
				}
			}

		// Children means we can potentially merge selections
		} else {
			if ($this->strategy == self::STRATEGY_COMBINE) {
				foreach ($this->field->children as $child) {
					// Ignore if left already has a value
					$exist = $this->ticket->getCustomDataForField($child);
					if ($exist) {
						continue;
					}

					$other_exist = $this->other_ticket->getCustomDataForField($child);
					if ($other_exist) {
						$other_exist->ticket = $this->ticket;
						$this->ticket->custom_data->add($child);
					}
				}
			} elseif ($this->strategy == self::STRATEGY_RIGHT) {

				// Take right ones over left ones
				foreach ($this->field->children as $child) {
					$other_exist = $this->other_ticket->getCustomDataForField($child);
					if ($other_exist) {

						$other_exist->ticket = $this->ticket;
						$this->ticket->custom_data->add($other_exist);
					}
				}
			}
		}
	}
}