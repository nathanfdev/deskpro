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

namespace Application\AdminBundle\Form\CustomField\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ChoiceFieldType extends CustomFieldTypeAbstract
{
	protected function buildCustomFieldForm(FormBuilder $builder, array $options)
	{
		$builder->add('multiple', 'checkbox', array('required' => false));
		$builder->add('expanded', 'checkbox', array('required' => false));

		$builder->add('choices', 'collection', array(
			'type' => 'hidden',
			'required' => true,
			'allow_add' => true,
			'allow_delete' => true
		));
	}

	public function getDefaultOptions(array $options)
	{
		return array(
			'data_class' => 'Application\\AdminBundle\\Form\\CustomField\\Model\\ChoiceField',
		);
	}
}
