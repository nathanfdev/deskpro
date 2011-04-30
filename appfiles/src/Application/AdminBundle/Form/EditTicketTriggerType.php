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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class EditTicketTriggerType extends AbstractType
{
	/**
	 * @var Application\DeskPRO\Entity\TicketTrigger
	 */
	protected $trigger;

	public function __construct($trigger)
	{
		$this->trigger = $trigger;
	}

	public function buildForm(FormBuilder $builder, array $options)
	{
		$builder->add('title', 'text');
		$builder->add('event_trigger', 'hidden');

		if (strpos($this->trigger['event_trigger'], 'time_') === 0) {
			$builder->add('event_trigger_option', 'hidden');
		}
	}
}