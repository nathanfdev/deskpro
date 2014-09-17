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
use Application\DeskPRO\App\Native\InstallerHandler\NoopInstallerHandler;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\AppInstance;
use Application\DeskPRO\Entity\AppPackage;
use Application\DeskPRO\Entity\Usersource;
use Doctrine\ORM\EntityManager;

class InstanceUpdater
{
	/**
	 * @var AppManager
	 */
	private $manager;

	/**
	 * @var \Application\DeskPRO\Entity\AppInstance
	 */
	private $app;

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @param AppManager $manager
	 * @param AppPackage $package
	 * @param EntityManager $em
	 */
	public function __construct(AppManager $manager, AppInstance $app, EntityManager $em)
	{
		$this->manager = $manager;
		$this->app     = $app;
		$this->em      = $em;
	}


	/**
	 * @param string $title
	 * @param array $settings
	 * @param DeskproContainer $container
	 * @return AppInstance
	 */
	public function update($title, array $settings, DeskproContainer $container)
	{
		$settings = InstanceInstaller::readAppSettings($this->app->package, $settings);
		$context = $this->createInstallContext($this->app->package, $this->app, $settings, $container);
		$handler = $this->createInstallHandler();

		$settings = $handler->processSettings($context, $settings);
		$this->app->setSettings($settings ? : array());
		$this->app->title = $title ? : $this->app->package->title;

		$this->em->persist($this->app);
		$this->em->flush();

		$handler->updateSettings($context);
	}


	/**
	 * @param AppPackage       $package
	 * @param AppInstance      $app
	 * @param array            $settings
	 * @param DeskproContainer $container
	 * @return InstallerContext
	 * @throws \UnexpectedValueException
	 */
	protected function createInstallContext(AppPackage $package, AppInstance $app, array $settings, DeskproContainer $container)
	{
		if ($package->native_name) {
			$native_app = $this->manager->getNativeApp($app);
			$usersource = null;

			if ($package->isUsersource()) {
				$q = $this->em->createQuery('
				SELECT us
				FROM DeskPRO:Usersource us
				WHERE us.app = :app
				');
				$q->setParameter('app', $app);
				$usersource = $q->getOneOrNullResult();

				if (!$usersource) {
					throw new \UnexpectedValueException('a usersource app instance MUST have a usersource pointing to it, app.id=' . $app->id . ' does not!');
				}
			}

			return new InstallerContext($container, $native_app, $settings, $usersource);
		}

		return new InstallerContext($container, null, $settings);
	}


	/**
	 * Native apps have their own install handler (usually), but we always return the NoopInstallerHandler so we always have a handler
	 *
	 * @return Native\InstallerHandler\InstallerHandlerInterface
	 */
	protected function createInstallHandler()
	{
		if ($this->app->package->native_name) {
			$native_app = $this->manager->getNativeApp($this->app);
			if ($class = $native_app->getConfig()->getInstallerHandlerClass()) {
				return new $class($this->app->package['settings_def']);
			}
		}

		return new NoopInstallerHandler();
	}
}
