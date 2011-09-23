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

class EditAgentType extends AbstractType
{
	public function buildForm(FormBuilder $builder, array $options)
	{
		$builder->add('first_name', 'text');
		$builder->add('last_name', 'text');
		$builder->add('password', 'password', array('required' => false));
		$builder->add('email', 'text');

		$zones = array('access_agent' => 'Agent', 'access_admin' => 'Admin', 'access_reports' => 'Reports', 'access_billing' => 'Billing');
		$builder->add('access_zones', 'choice', array(
			'choices' => $zones,
			'expanded' => true,
			'multiple' => true
		));

		$usergroup_names = App::getEntityRepository('DeskPRO:Usergroup')->getAgentUsergroupNames();
		if ($usergroup_names) {
			$builder->add('usergroups', 'choice', array(
				'choices' => $usergroup_names,
				'expanded' => true,
				'multiple' => true
			));
		}

		$team_names = App::getEntityRepository('DeskPRO:AgentTeam')->getTeamNames();
		$builder->add('agent_teams', 'choice', array(
			'choices' => $team_names,
			'expanded' => true,
			'multiple' => true
		));

		$department_names = App::getEntityRepository('DeskPRO:Department')->getFullDepartmentNames();
		$builder->add('allowed_departments', 'choice', array(
			'choices' => $department_names,
			'expanded' => true,
			'multiple' => true
		));
	}

	public function getName()
	{
		return 'agent';
	}
}
