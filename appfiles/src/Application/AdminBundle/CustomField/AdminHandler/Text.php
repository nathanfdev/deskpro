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

namespace Application\AdminBundle\CustomField\AdminHandler;

use Symfony\Component\Form\FormBuilder;

/**
 * Handles editing and creating text field definitions
 */
class Text extends AbstractAdminHandler
{
	public function buildForm(FormBuilder $builder, array $options, $formtype)
	{
		$opt_builder = $builder->create('options');

		$opt_builder->add('min_length', 'text');
		$opt_builder->add('max_length', 'text');
		$builder->add($opt_builder);
	}

	public function preSave($field_save)
	{
		foreach ($field_save->options as $k => $v) {
			$this->custom_def->setOption($k, $v);
		}
	}
}