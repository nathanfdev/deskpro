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

namespace Application\AdminBundle\Form;

use \Application\DeskPRO\Entity\CustomDefAbstract;

class EditField extends \Orb\Form\Field\Form
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