<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AdminBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AdminBundle\Form;

use \Symfony\Component\Form;
use \Application\DeskPRO\Entity\TicketTrigger;

class EditTicketTriggerForm extends \Symfony\Component\Form\Form
{
	/**
	 * @var Application\DeskPRO\Entity\TicketTrigger
	 */
	protected $trigger;

	protected function configure()
	{
		$this->addRequiredOption('trigger');

		$this->trigger = $this->getOption('trigger');

		$this->add(new Form\TextField('title'));
		$this->add(new Form\HiddenField('event_trigger'));

		if (strpos($this->trigger['event_trigger'], 'time_') === 0) {
			$this->add(new Form\HiddenField('event_trigger_option'));
		}
	}
}