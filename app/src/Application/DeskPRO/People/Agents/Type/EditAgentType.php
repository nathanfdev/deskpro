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
 */

namespace Application\DeskPRO\People\Agents\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EditAgentType extends AbstractType
{
	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('name', 'text', array('required' => true));
		$builder->add('override_name', 'text', array('required' => false));

		$builder->add('primary_phone_number_text', 'text', array('required' => false));

		$builder->add('emails', 'collection', array(
			'type'         => 'email',
			'allow_add'    => true,
			'allow_delete' => true,
			'invalid_message' => 'Invalid Email.',
		));

		$builder->add('zones', 'choice', array(
			'choices'  => array('admin' => 'admin', 'reports' => 'reports'),
			'multiple' => true,
			'required' => false,
		));

		$builder->add('teams', 'entity', array(
			'class'    => 'DeskPRO:AgentTeam',
			'required' => false,
			'multiple' => true,
			'invalid_message' => 'Invalid Team.',
		));

		$builder->add('agent_groups', 'entity', array(
			'class'         => 'DeskPRO:Usergroup',
			'required'      => false,
			'multiple'      => true,
			'query_builder' => function(EntityRepository $er) {
				return $er->createQueryBuilder('ug')->where('ug.is_agent_group = true');
			},
			'invalid_message' => 'Invalid Agent Group.',
		));

		$builder->add('primary_team', 'entity', array(
			'class'         => 'DeskPRO:AgentTeam',
			'required'      => false,
			'invalid_message' => 'Invalid Agent Team.',
		));

		$builder->add('notification_settings', 'collection');
	}


	/**
	 * @param OptionsResolverInterface $resolver
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class'         => 'Application\\DeskPRO\\People\\Agents\\EditAgent',
			'cascade_validation' => true,
		));
	}


	/**
	 * @return string
	 */
	public function getName()
	{
		return 'agent';
	}
}
