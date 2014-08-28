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
 * @category Entities
 */

namespace Application\DeskPRO\App;

use Application\DeskPRO\App\Native\InstallerHandler\InstallerContext;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\AppInstance;
use Application\DeskPRO\Entity\AppPackage;
use Doctrine\ORM\EntityManager;

class InstanceInstaller
{
	/**
	 * @var AppManager
	 */
	private $manager;

	/**
	 * @var \Application\DeskPRO\Entity\AppPackage
	 */
	private $package;

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @param AppManager $manager
	 * @param AppPackage $package
	 * @param EntityManager $em
	 */
	public function __construct(AppManager $manager, AppPackage $package, EntityManager $em)
	{
		$this->manager = $manager;
		$this->package = $package;
		$this->em      = $em;
	}


	/**
	 * @param string $title
	 * @param array $settings
	 * @param DeskproContainer $container
	 * @return AppInstance
	 */
	public function install($title, array $settings, DeskproContainer $container)
	{
		$app = new AppInstance();
		$app->package = $this->package;
		$app->title = $title ?: $this->package->title;

		// Need to persist now so we have an actual app record
		// (the id may be used in the installer)
		$this->em->persist($app);
		$this->em->flush();

		$context = null;
		$handler = null;

		if ($this->package->native_name) {
			$native_app = $this->manager->getNativeApp($app);
			$class = $native_app->getConfig()->getInstallerHandlerClass();
			if ($class) {
				$context = new InstallerContext($container, $native_app, $settings);
				$handler = new $class($this->package['settings_def']);
			}
		}

		$settings = self::readAppSettings($this->package, $settings);
		if ($handler) {
			$settings = $handler->processSettings($context, $settings);
		}
		$app->setSettings($settings ?: array());

		$this->em->persist($app);
		$this->em->flush();

		if ($handler) {
			$handler->install($context);
		}

		return $app;
	}


	/**
	 * @param AppPackage $package
	 * @param array $settings_form
	 * @return array
	 */
	public static function readAppSettings(AppPackage $package, array $settings_form)
	{
		$settings = array();
		foreach ($package->settings_def as $setting_def) {
			$value = isset($settings_form[$setting_def['name']]) ? $settings_form[$setting_def['name']] : null;
			if (!is_scalar($value)) {
				$value = null;
			}

			if ($value !== null) {
				switch ($setting_def) {
					case 'choice':
						$found = false;
						if (isset($setting_def['options'])) {
							foreach ($setting_def['options'] as $opt) {
								if ($opt['value'] == $value) {
									$found = true;
									break;
								}
							}
						}
						if (!$found) {
							$value = null;
						}
						break;

					case 'checkbox':
						if ($value === true || $value === 1 || $value === "1" || $value === "true") {
							$value = true;
						} else {
							$value = false;
						}
						break;
				}
			}

			if ($value === null && isset($setting_def['default_value'])) {
				$value = $setting_def['default_value'];
			}

			if ($value !== null) {
				$settings[$setting_def['name']] = $value;
			}
		}

		return $settings;
	}
}