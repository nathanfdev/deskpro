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

class NewTicket extends AbstractType
{
	public function buildForm(FormBuilder $builder, array $options)
    {
		#------------------------------
		# User fields
		#------------------------------

		$user_builder = $builder->create('person', 'form', array('data_class' => 'Application\\AgentBundle\\Form\\Model\\NewTicketPerson'));
		$user_builder->add('id', 'hidden');
		$user_builder->add('email_address', 'text', array('required' => false));
		$user_builder->add('organization', 'text', array('required' => false));
		$user_builder->add('organization_position', 'text', array('required' => false));

		$builder->add($user_builder);

		#------------------------------
		# Ticket fields
		#------------------------------

		$builder->add('subject', 'text');
		$builder->add('notify_template', 'hidden');
		$builder->add('message', 'textarea');

		$builder->add('department_id', 'text');
		$builder->add('status', 'text');
		$builder->add('agent_id', 'text', array('required' => false));
		$builder->add('agent_team_id', 'text', array('required' => false));

		$builder->add('category_id', 'text', array('required' => false));
		$builder->add('priority_id', 'text', array('required' => false));
		$builder->add('workflow_id', 'text', array('required' => false));
		$builder->add('product_id', 'text', array('required' => false));

		$builder->add('new_parts', 'collection', array(
			'type' => 'hidden',
			'required' => false,
			'allow_add' => true,
			'allow_delete' => true
		));

		$builder->add('attach', 'collection', array(
			'type' => 'hidden',
			'required' => false,
			'allow_add' => true,
			'allow_delete' => true
		));
    }

	public function getDefaultOptions(array $options)
	{
		return array(
			'data_class' => 'Application\\AgentBundle\\Form\\Model\\NewTicket',
		);
	}

    public function getName()
    {
        return 'newticket';
    }
}