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

namespace Application\AdminBundle;

use Application\DeskPRO\App;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Orb\Util\Arrays;

class SetupGuide
{
	/**
	 * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
	 */
	protected $container;

	/**
	 * @var \Application\AdminBundle\Controller\AbstractController
	 */
	protected $controller;

	/**
	 * This is an array of id=>array(info)
	 *
	 * The ID is used in settings to store when something has been completed,
	 * and also used as a phrase ID in the templates to fetch the title of a step.
	 *
	 * @var array
	 */
	protected $tasks = array(
		'incoming_email'     => array('route' => 'admin_emailgateways'),
		'add_agents'         => array('route' => 'admin_agents_new'),
		//'custom_header'      => array('route' => 'admin_portal'),
		'add_ticketcategory' => array('route' => 'admin_ticketcats'),
		'add_ticketpriority' => array('route' => 'admin_ticketpris'),
		'add_ticketfield'    => array('route' => 'admin_customdeftickets')
	);

	public function __construct(DeskproContainer $container, $controller)
	{
		$this->container  = $container;
		$this->controller = $controller;

		foreach ($this->tasks as $id => &$task) {
			$task['id'] = $id;
		}
	}


	/**
	 * In the AbstractController the preaction calls this to see if we need to redirect the user
	 * forcefully based on install step.
	 */
	public function preActionHelper($action)
	{
		// Only care when they've already logged in
		if (!$this->container->getSession()->getPerson()->getId()) {
			return null;
		}

		// Dont care about ajax requests
		if ($this->container->getRequest()->isXmlHttpRequest()) {
			return null;
		}

		$step = (int)$this->container->getSetting('core.setup_initial');

		#------------------------------
		# Setup first
		#------------------------------

		if (!$step) {
			if (!($this->controller instanceof \Application\AdminBundle\Controller\SettingsController) || $action != 'quickSetupAction') {
				return $this->controller->redirectRoute('admin_welcome');
			}

		#------------------------------
		# SMTP next
		#------------------------------

		} elseif ($step < 10) {
			if (!($this->controller instanceof \Application\AdminBundle\Controller\EmailTransportsController) || ($action != 'setupAction' && $action != 'editAccountAction')) {
				return $this->controller->redirectRoute('admin_emailtrans_setup');
			}

		#------------------------------
		# License
		#------------------------------

		} elseif ($step < 20) {
			if (!($this->controller instanceof \Application\AdminBundle\Controller\LicenseController)) {
				return $this->controller->redirectRoute('admin_license');
			}

		#------------------------------
		# Cron
		#------------------------------

		} elseif ($step < 30) {
			if (!($this->controller instanceof \Application\AdminBundle\Controller\SettingsController) || $action != 'cronAction') {
				return $this->controller->redirectRoute('admin_settings_cron');
			}
		}

		return null;
	}


	/**
	 * This goes through the tasks and returns its info. If no more tasks are left to complete, then null is returned.
	 *
	 * @return null
	 */
	public function getNextTask()
	{
		foreach ($this->tasks as $t => $info) {
			if (!$this->container->getSetting('core.task_completed_' . $t)) {
				return $info;
			}
		}

		return null;
	}


	/**
	 * Percentage finished
	 *
	 * @return int
	 */
	public function getPercentComplete($skew = 6)
	{
		$done = 0;
		foreach ($this->tasks as $t => $info) {
			if ($this->container->getSetting('core.task_completed_' . $t)) {
				$done++;
			}
		}

		$total = count($this->tasks) + $skew;
		$done += $skew;

		return ceil(($done / $total)*100);
	}


	/**
	 * Check if a task is complete. If it is complete, then whatever status value
	 * set by that tasks controller will be returned (usually a timestamp).
	 *
	 * @param $id
	 * @return bool
	 */
	public function isTaskComplete($id)
	{
		$s = $this->container->getSetting('core.task_completed_' . $id);
		if ($s) {
			return $s;
		}

		return false;
	}


	/**
	 * Gets full array of tasks
	 *
	 * @return
	 */
	public function getTasks()
	{
		return $this->tasks;
	}
}
