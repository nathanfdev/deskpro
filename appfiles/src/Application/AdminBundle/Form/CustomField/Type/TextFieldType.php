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

class TextFieldType extends CustomFieldTypeAbstract
{
	protected function buildCustomFieldForm(FormBuilder $builder, array $options)
	{
		$builder->add('min_length', 'text', array('required' => false));
		$builder->add('max_length', 'text', array('required' => false));
		$builder->add('regex', 'text', array('required' => false));
	}

	public function getDefaultOptions(array $options)
	{
		return array(
			'data_class' => 'Application\\AdminBundle\\Form\\CustomField\\Model\\TextField',
		);
	}
}
