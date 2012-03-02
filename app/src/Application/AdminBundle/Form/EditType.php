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

namespace Application\AdminBundle\Form;

use Application\DeskPRO\Entity\CustomDefAbstract;

class EditType extends \Orb\Form\Field\Form
{
	/**
	 * @var Application\DeskPRO\Entity\CustomDefAbstract
	 */
	protected $custom_def;

	protected function init()
	{
		if (!$this->hasOption('custom_def') OR !($this->getOption('custom_def') instanceof CustomDefAbstract)) {
			throw new \InvalidArgumentException('Options must include a form_field item');
		}

		$this->custom_def = $this->getOption('custom_def');

		$f_group_props = new \Orb\Form\Field\FieldGroup(array('name' => 'field_properties'));
		$this->addField($f_group_props);

		// Title
		$f = new \Orb\Form\Field\Text(array('name' => 'title'));
		$f->setData($this->custom_def['title']);
		$f_group_props->addField($f);
	}
}
