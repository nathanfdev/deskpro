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

namespace Application\AgentBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class NewOrganization extends AbstractType
{
	public function buildForm(FormBuilder $builder, array $options)
    {
		$builder->add('name', 'text', array('required' => false));

		$builder->add('labels', 'collection', array(
			'type' => 'text',
			'required' => false,
			'allow_add' => true,
			'allow_delete' => true
		));
		$builder->add('usergroup_ids', 'collection', array(
			'type' => 'text',
			'required' => false,
			'allow_add' => true,
			'allow_delete' => true
		));
    }

	public function getDefaultOptions(array $options)
	{
		return array(
			'data_class' => 'Application\\AgentBundle\\Form\\Model\\NewPerson',
		);
	}

    public function getName()
    {
        return 'neworg';
    }
}
