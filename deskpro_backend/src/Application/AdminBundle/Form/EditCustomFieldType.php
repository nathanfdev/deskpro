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

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Orb\Util\Arrays;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class EditCustomFieldType extends AbstractType
{
	protected $field_save;

	public function __construct($field_save)
	{
		$this->field_save = $field_save;
	}

	public function buildForm(FormBuilder $builder, array $options)
	{
		$builder->add('title', 'text');
		$builder->add('handler_class', 'hidden');

		$builder->add('custom_form_html', 'textarea', array('required' => false));
		$builder->add('custom_display_html', 'textarea', array('required' => false));

		if ($this->field_save->getAdminHandler()) {
			$this->field_save->getAdminHandler()->buildForm($builder, $options, $this);
		}
	}
}