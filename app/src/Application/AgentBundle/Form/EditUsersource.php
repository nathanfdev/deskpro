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
 * @subpackage AgentBundle
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
