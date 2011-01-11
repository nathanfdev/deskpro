<?php

namespace Application\DevBundle\Controller;

use \Application\DeskPRO\Build\VersionReader;
use \Application\DeskPRO\Build\Upgrader;

use \Application\DeskPRO\App;

class InstallController extends \Application\DeskPRO\HttpKernel\Controller\Controller
{
	public function indexAction()
	{
		$config_contents = @file_get_contents(DP_ROOT . '/config.php');
		
		return $this->render('DevBundle:Install:index.php', array(
			'config_contents' => $config_contents
		));
	}

	public function checkAction()
	{
		$checks = array(
			'php_version' => true,
			'ext_pdo_mysql' => true,
			'ext_intl' => true,
			'writable_cache' => true,
			'writable_logs' => true,
			'config' => true
		);
		$checks_msg = array();

		if (!version_compare(PHP_VERSION, '5.3.2', '>=')) {
			$checks['php_version'] = false;
		}

		if (!extension_loaded('pdo') OR !extension_loaded('pdo_mysql') OR !class_exists('PDO')) {
			$checks['ext_pdo_mysql'] = false;
		}
		if (!extension_loaded('intl') OR !class_exists('Locale')) {
			$checks['ext_intl'] = false;
		}
		if (!is_writable(DP_ROOT.'/sys/cache')) {
			$checks['writable_cache'] = false;
		}
		if (!is_writable(DP_ROOT.'/sys/logs')) {
			$checks['writable_logs'] = false;
		}

		if ($checks['ext_pdo_mysql']) {
			try {
				$db = $this->get('database_connection');
				$db->connect();
			} catch (\PDOException $e) {
				$checks['config'] = false;
				$checks_msg['config'] = $e->getMessage();
			}
		}


		$is_error = (\array_search(false, $checks) !== false);

		return $this->render('DevBundle:Install:check.php', array(
			'checks' => $checks,
			'checks_msg' => $checks_msg,
			'is_error' => $is_error
		));
	}

	public function createTablesAction()
	{
		$db = $this->container->get('database_connection');
		$db->connect();

		#------------------------------
		# Generate the schema based off our entities
		#------------------------------

		// Get the SQL
		$em = $this->container->get('doctrine.orm.entity_manager');
		$metadata = $em->getMetadataFactory()->getAllMetadata();
		$tool = new \Doctrine\ORM\Tools\SchemaTool($em);
		$all_sql = $tool->getCreateSchemaSql($metadata);

		return $this->render('DevBundle:Install:create-tables.php', array(
			'db' => $db,
			'all_sql' => $all_sql
		));
	}

	public function createDataAction()
	{
		$output = new \Application\DeskPRO\Build\Output();
		$output->html = true;
		
		$this->em = App::getOrm();

		$error = false;
		ob_start();
		try {
			$this->em->beginTransaction();
			$this->_createMiscData($output);
			$this->_createUsergroups($output);
			$this->_createNewUser($output);
			$this->em->commit();
		} catch (Exception $e) {
			$error = $e->getMessage();
			$this->em->rollback();
		}
		$results = ob_get_clean();

		return $this->render('DevBundle:Install:create-data.php', array(
			'error' => $error,
			'results' => $results
		));
	}

	protected function _createMiscData($output)
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

	protected function _createUsergroups($output)
	{
		// Guests
		$group = new \Application\DeskPRO\Entity\Usergroup();
		$group->fromArray(array(
			'title' => 'Guests',
			'permissions' => array()
		));
		$this->em->persist($group);
		$this->em->flush();

		$output->write("Created Guests usergroup #{$group['id']}");

		// Users
		$group = new \Application\DeskPRO\Entity\Usergroup();
		$group['title'] = 'Users';
		$this->em->persist($group);
		$this->em->flush();

		$output->write("Created Users usergroup #{$group['id']}");

		// Techs
		$group = new \Application\DeskPRO\Entity\Usergroup();
		$group->fromArray(array(
			'title' => 'Technicians',
			'permissions' => array()
		));
		$this->em->persist($group);
		$this->em->flush();

		$output->write("Created Technicians usergroup #{$group['id']}");

		// Admins
		$this->admin_group = $group = new \Application\DeskPRO\Entity\Usergroup();
		$group->fromArray(array(
			'title' => 'Administrators',
			'permissions' => array()
		));
		$this->em->persist($group);
		$this->em->flush();

		$output->write("Created Aministrators usergroup #{$group['id']}");
	}

	protected function _createNewUser($output)
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

		$output->write("\n<info>Admin Person #{$person['id']} was created:\n\tEmail: admin@example.com\n\tPassword: pass</info>");
	}
}