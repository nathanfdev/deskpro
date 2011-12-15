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
		$builder->add('field_type', 'choice', array('choices' => array(
			'select' => 'Select box (single selection)',
			'multi_select' => 'Mutli-Select box (multiple selection)',
			'radio' => 'Radio buttons (single selection)',
			'checkbox' => 'Checkboxes (multiple selection)',
		)));

		$builder->add('choices', 'collection', array(
			'type' => 'hidden',
			'required' => true,
			'allow_add' => true,
			'allow_delete' => true
		));

		$builder->add('min_length', 'text', array('required' => false));
		$builder->add('max_length', 'text', array('required' => false));
	}

	public function getDefaultOptions(array $options)
	{
		return array(
			'data_class' => 'Application\\AdminBundle\\Form\\CustomField\\Model\\ChoiceField',
		);
	}
}
