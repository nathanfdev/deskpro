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
		$group['is_admin'] = true;
		$group['is_tech'] = true;
		$group['title'] = 'Administrators';
		$this->em->persist($group);
		$this->em->flush();

		$output->writeln("Created Aministrators usergroup #{$group['id']}");

		// Techs
		$group = $this->em->createEntity('CoreBundle:Usergroup');
		$group['is_tech'] = true;
		$group['title'] = 'Technicians';
		$this->em->persist($group);
		$this->em->flush();

		$output->writeln("Created Technicians usergroup #{$group['id']}");

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
		$profile = $this->em->createEntity('CoreBundle:Profile');
		$em->persist($profile);
		$em->flush();

		// The email addy
		$email = $this->em->createEntity('CoreBundle:ProfileEmail');
		$email['email_address'] = 'admin@example.com';
		$email['is_validated'] = true;
		$email['profile'] = $profile;

		$em->persist($email);
		$em->persist($profile);
		$em->flush();

		// The user
		$user = $this->em->createEntity('CoreBundle:User');
		$user['password'] = 'pass';

		$user['profile'] = $profile;

		$group = $em->find('CoreBundle:Usergroup', 1);
		$user['usergroups']->add($group);

		$em->persist($user);
		$em->flush();
		$em->commit();

		$output->writeln("\n<info>Admin user #{$user['id']} was created:\n\tEmail: admin@example.com\n\tPassword: pass</info>");
	}
}