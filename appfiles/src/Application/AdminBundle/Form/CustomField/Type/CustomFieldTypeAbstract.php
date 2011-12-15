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

abstract class CustomFieldTypeAbstract extends AbstractType
{
	public function buildForm(FormBuilder $builder, array $options)
    {
		#------------------------------
		# Basic fields
		#------------------------------

		$builder->add('title', 'text', array('required' => true));
		$builder->add('handler_class', 'hidden', array('required' => true));
		$builder->add('validation_type', 'hidden', array('required' => false));

		$builder->add('required', 'checkbox', array('required' => false));
		$builder->add('custom_css_classname', 'text', array('required' => false));

		$this->buildCustomFieldForm($builder, $options);
    }

	protected function buildCustomFieldForm(FormBuilder $builder, array $options) {}

    public function getName()
    {
        return 'fielddef';
    }
}
