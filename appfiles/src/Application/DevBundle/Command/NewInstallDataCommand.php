<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Commands
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
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
class NewInstallDataCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
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
		$this->_createMiscData($output);
		$this->_createUsergroups($output);
		$this->_createNewUser($output);
		$this->em->commit();

		// Write the version file which will be used by
		// upgrade scripts
		$vfile = DP_ROOT.'/sys/VERSION';
		$version = new \DateTime();
		$version = $version->format('Y-m-d H:i:s');
		if (!@file_put_contents($vfile, $version)) {
			$output->write("<warn>\n Error writing $vfile\nThis file must contain the value: $version\nIf it does not, upgrading will not work\n</warn>\n");
		}
	}

	protected function _createMiscData(OutputInterface $output)
	{
		// Department
		$dep_support = $ent = new \Application\DeskPRO\Entity\Department();
		$ent['title'] = 'Support';
		$this->em->persist($ent);

		$dep_sales = $ent = new \Application\DeskPRO\Entity\Department();
		$ent['title'] = 'Sales';
		$this->em->persist($ent);

		$dep_info = $ent = new \Application\DeskPRO\Entity\Department();
		$ent['title'] = 'Information';
		$this->em->persist($ent);

		// Product
		$ent = new \Application\DeskPRO\Entity\Product();
		$ent['title'] = 'DeskPRO';
		$this->em->persist($ent);

		$ent = new \Application\DeskPRO\Entity\Product();
		$ent['title'] = 'DeskPRO Live';
		$this->em->persist($ent);

		// Priority
		$ent = new \Application\DeskPRO\Entity\TicketPriority();
		$ent['title'] = 'Low';
		$ent['priority'] = 1;
		$this->em->persist($ent);

		$ent = new \Application\DeskPRO\Entity\TicketPriority();
		$ent['title'] = 'Medium';
		$ent['priority'] = 5;
		$this->em->persist($ent);

		$ent = new \Application\DeskPRO\Entity\TicketPriority();
		$ent['title'] = 'High';
		$ent['priority'] = 10;
		$this->em->persist($ent);

		// Category
		$this->em->flush();

		$ent = new \Application\DeskPRO\Entity\TicketCategory();
		$ent['title'] = 'How Do I';
		$ent['department'] = $dep_support;
		$this->em->persist($ent);

		$ent = new \Application\DeskPRO\Entity\TicketCategory();
		$ent['title'] = 'Troubleshooting';
		$ent['department'] = $dep_support;
		$this->em->persist($ent);

		$ent = new \Application\DeskPRO\Entity\TicketCategory();
		$ent['title'] = 'Other';
		$ent['department'] = $dep_support;
		$this->em->persist($ent);

		$ent = new \Application\DeskPRO\Entity\TicketCategory();
		$ent['title'] = 'General';
		$ent['department'] = $dep_sales;
		$this->em->persist($ent);

		$ent = new \Application\DeskPRO\Entity\TicketCategory();
		$ent['title'] = 'Feature Enquiry';
		$ent['department'] = $dep_sales;
		$this->em->persist($ent);

		$ent = new \Application\DeskPRO\Entity\TicketCategory();
		$ent['title'] = 'Licensing Enquiry';
		$ent['department'] = $dep_sales;
		$this->em->persist($ent);

		$ent = new \Application\DeskPRO\Entity\TicketCategory();
		$ent['title'] = 'Custom Programming';
		$ent['department'] = $dep_sales;
		$this->em->persist($ent);

		$this->em->flush();
	}

	protected function _createUsergroups(OutputInterface $output)
	{
		// Guests
		$group = new \Application\DeskPRO\Entity\Usergroup();
		$group->fromArray(array(
			'title' => 'Guests',
			'permissions' => array()
		));
		$this->em->persist($group);
		$this->em->flush();

		$output->writeln("Created Guests usergroup #{$group['id']}");

		// Users
		$group = new \Application\DeskPRO\Entity\Usergroup();
		$group['title'] = 'Users';
		$this->em->persist($group);
		$this->em->flush();

		$output->writeln("Created Users usergroup #{$group['id']}");

		// Techs
		$group = new \Application\DeskPRO\Entity\Usergroup();
		$group->fromArray(array(
			'title' => 'Technicians',
			'permissions' => array()
		));
		$this->em->persist($group);
		$this->em->flush();

		$output->writeln("Created Technicians usergroup #{$group['id']}");

		// Admins
		$this->admin_group = $group = new \Application\DeskPRO\Entity\Usergroup();
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
		// Organization
		$org = new \Application\DeskPRO\Entity\Organization();
		$org['name'] = 'ACME Corp';
		$this->em->persist($org);

		// Profile
		$person = new \Application\DeskPRO\Entity\Person();
		$person['password'] = 'pass';
		$person['first_name'] = 'John';
		$person['last_name'] = 'Doe';
		$person['name'] = 'John Doe';
		$person['is_contact'] = true;
		$person['is_user'] = true;
		$person['is_agent'] = true;

		$person->setOrganization($org, 'Marketing Manager');

		$email = new \Application\DeskPRO\Entity\PersonEmail();
		$email['email'] = 'admin@example.com';
		$email['is_validated'] = true;
		$person->addEmailAddress($email);

		$person->addUsergroup($this->admin_group);

		$this->em->persist($person);
		$this->em->flush();


		$output->writeln("\n<info>Admin Person #{$person['id']} was created:\n\tEmail: admin@example.com\n\tPassword: pass</info>");
	}
}