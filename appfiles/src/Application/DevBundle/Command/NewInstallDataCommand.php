<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Commands
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\DevBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;


/**
 * dpdev:new-install-data
 *
 * Inserts some default data for a fresh install to get you going.
 */
class NewInstallDataCommand extends \Symfony\Bundle\FrameworkBundle\Command\Command
{
	/**
	 * Entity manager
	 * @var DeskPRO\ORM\EntityManager
	 */
	protected $em;
	
	protected function configure()
	{
		$this->setDefinition(array(

		))->setName('dpdev:new-install-data');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->em = $this->container->get('doctrine.orm.entity_manager');

		$this->_createUsergroups($output);
		$this->_createNewUser($output);
	}

	protected function _createUsergroups(OutputInterface $output)
	{
		// Admins
		$group = $this->em->createEntity('CoreBundle:Usergroup');
		$group->fromArray(array(
			'title' => 'Administrators',
			'permissions' => array(
				'is_admin' => true,
				'is_tech' => true
			)
		));
		$this->em->persist($group);
		$this->em->flush();

		$output->writeln("Created Aministrators usergroup #{$group['id']}");

		// Techs
		$group = $this->em->createEntity('CoreBundle:Usergroup');
		$group->fromArray(array(
			'title' => 'Technicians',
			'permissions' => array(
				'is_tech' => true
			)
		));
		$this->em->persist($group);
		$this->em->flush();

		$output->writeln("Created Technicians usergroup #{$group['id']}");

		// Guests
		$group = $this->em->createEntity('CoreBundle:Usergroup');
		$group->fromArray(array(
			'title' => 'Guests',
			'permissions' => array(
				'is_guest' => true
			)
		));
		$this->em->persist($group);
		$this->em->flush();

		$output->writeln("Created Guests usergroup #{$group['id']}");

		// Users
		$group = $this->em->createEntity('CoreBundle:Usergroup');
		$group['title'] = 'Users';
		$this->em->persist($group);
		$this->em->flush();

		$output->writeln("Created Users usergroup #{$group['id']}");
	}

	protected function _createNewUser(OutputInterface $output)
	{
		$em = $this->container->get('doctrine.orm.entity_manager');
		$em->beginTransaction();

		// Profile
		$person = $this->em->createEntity('CoreBundle:Person');
		$person['password'] = 'pass';
		$person['is_user'] = true;
		$em->persist($person);
		$em->flush();

		// The email addy
		$email = $this->em->createEntity('CoreBundle:PersonEmail');
		$email['email_address'] = 'admin@example.com';
		$email['is_validated'] = true;
		$email['person'] = $person;

		$em->persist($email);
		$em->persist($person);
		$em->flush();

		$group = $em->find('CoreBundle:Usergroup', 1);
		$person['usergroups']->add($group);

		$em->persist($person);
		$em->flush();
		$em->commit();

		$output->writeln("\n<info>Admin Person #{$person['id']} was created:\n\tEmail: admin@example.com\n\tPassword: pass</info>");
	}
}