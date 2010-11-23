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
	 * @var Doctrine\ORM\EntityManager
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

		$this->em->beginTransaction();
		$this->_createUsergroups($output);
		$this->_createNewUser($output);
		$this->em->commit();
	}

	protected function _createUsergroups(OutputInterface $output)
	{
		// Guests
		$group = new \Application\CoreBundle\Entity\Usergroup();
		$group->fromArray(array(
			'title' => 'Guests',
			'permissions' => array()
		));
		$this->em->persist($group);
		$this->em->flush();

		$output->writeln("Created Guests usergroup #{$group['id']}");

		// Users
		$group = new \Application\CoreBundle\Entity\Usergroup();
		$group['title'] = 'Users';
		$this->em->persist($group);
		$this->em->flush();

		$output->writeln("Created Users usergroup #{$group['id']}");

		// Techs
		$group = new \Application\CoreBundle\Entity\Usergroup();
		$group->fromArray(array(
			'title' => 'Technicians',
			'permissions' => array()
		));
		$this->em->persist($group);
		$this->em->flush();

		$output->writeln("Created Technicians usergroup #{$group['id']}");

		// Admins
		$group = new \Application\CoreBundle\Entity\Usergroup();
		$group->fromArray(array(
			'title' => 'Administrators',
			'permissions' => array()
		));
		$this->em->persist($group);
		$this->em->flush();

		$output->writeln("Created Aministrators usergroup #{$group['id']}");
	}

	protected function _createNewUser(OutputInterface $output)
	{
		// Profile
		$person = new \Application\CoreBundle\Entity\Person();
		$person['password'] = 'pass';
		$person['is_contact'] = true;
		$person['is_user'] = true;
		$person['is_tech'] = true;

		$email = new \Application\CoreBundle\Entity\PersonEmail();
		$email['email'] = 'admin@example.com';
		$email['is_validated'] = true;
		$person->addEmailAddress($email);

		$usergroup = $this->em->find('CoreBundle:Usergroup', 4);
		$person->addUsergroup($usergroup);

		$this->em->persist($person);
		$this->em->flush();

		$output->writeln("\n<info>Admin Person #{$person['id']} was created:\n\tEmail: admin@example.com\n\tPassword: pass</info>");
	}
}