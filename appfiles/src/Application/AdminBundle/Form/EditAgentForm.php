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

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

use \Orb\Util\Arrays;

use \Symfony\Component\Form;

class EditAgentForm extends \Symfony\Component\Form\Form
{
	protected function configure()
	{
		$this->add(new Form\TextField('first_name'));
		$this->add(new Form\TextField('last_name'));
		$this->add(new Form\PasswordField('password'));
		$this->add(new Form\TextField('email'));

		$zones = array('access_agent' => 'Agent', 'access_admin' => 'Admin', 'access_reports' => 'Reports', 'access_billing' => 'Billing');
		$this->add(new Form\ChoiceField('access_zones', array(
			'choices' => $zones,
			'expanded' => true,
			'multiple' => true
		)));

		$usergroup_names = App::getEntityRepository('DeskPRO:Usergroup')->getAgentUsergroupNames();
		$this->add(new Form\ChoiceField('usergroups', array(
			'choices' => $usergroup_names,
			'expanded' => true,
			'multiple' => true
		)));

		$team_names = App::getEntityRepository('DeskPRO:AgentTeam')->getTeamNames();
		$this->add(new Form\ChoiceField('agent_teams', array(
			'choices' => $team_names,
			'expanded' => true,
			'multiple' => true
		)));

		$department_names = App::getEntityRepository('DeskPRO:Department')->getFullDepartmentNames();
		$this->add(new Form\ChoiceField('allowed_departments', array(
			'choices' => $department_names,
			'expanded' => true,
			'multiple' => true
		)));
	}
}