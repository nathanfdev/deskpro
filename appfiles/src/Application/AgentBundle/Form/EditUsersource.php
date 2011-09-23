<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AgentBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AgentBundle\Form;

use Application\DeskPRO\Entity\Usersource;

class EditUsersource extends \Orb\Form\Field\Form
{
	/**
	 * @var Application\DeskPRO\Entity\Usersource
	 */
	protected $usersource;

	protected function init()
	{
		if (!$this->hasOption('usersource') OR !($this->getOption('usersource') instanceof Usersource)) {
			throw new \InvalidArgumentException('Options must include a usersource item');
		}

		$this->usersource = $this->getOption('usersource');

		$this->addField(new \Orb\Form\Field\Hidden(array('name' => 'handler_class', 'data' => $this->usersource['handler_class'])));

		$f_group_props = new \Orb\Form\Field\FieldGroup(array('name' => 'basic_properties'));

		// Title
		$f = new \Orb\Form\Field\Hidden(array('name' => 'title'));
		$f_group_props->addField($f);

		$this->addField($f_group_props);
	}
}
