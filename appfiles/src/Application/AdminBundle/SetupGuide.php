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

	public function __construct(DeskproContainer $container, $controller)
	{
		$this->container  = $container;
		$this->controller = $controller;
	}

	/**
	 * Check vars etc to see if we need to force-redirect a user somewhere
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
}
