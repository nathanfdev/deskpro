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
 */

namespace Application\DeskPRO\Command;

use Application\DeskPRO\App\Package\Package;
use Application\DeskPRO\App\Package\PackageInstaller;
use Application\DeskPRO\Entity\AppInstance;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class TestCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('dp:test');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		/** @var \Application\DeskPRO\DependencyInjection\DeskproContainer $container */
		$container = $this->getContainer();

		$container->getDb()->executeUpdate("DELETE FROM app_instances WHERE package_name = 'com.deskpro.apps.hipchat'");
		$container->getDb()->executeUpdate("DELETE FROM app_packages WHERE name = 'com.deskpro.apps.hipchat'");
		$package = new Package(DP_ROOT.'/apps/deskpro_hipchat');

		$installer = new PackageInstaller($container->getEm(), $container->getBlobStorage(), $container->getImagine());
		$installer->installPackage($package);

		return;
		$container->getDb()->executeUpdate("DELETE FROM app_instances WHERE package_name = 'com.deskpro.apps.test'");
		$container->getDb()->executeUpdate("DELETE FROM app_packages WHERE name = 'com.deskpro.apps.test'");
		$package = new Package(DP_ROOT.'/apps/TestApp');

		$installer = new PackageInstaller($container->getEm(), $container->getBlobStorage(), $container->getImagine());
		$installer->installPackage($package);

		$package = $container->getEm()->getRepository('DeskPRO:AppPackage')->findOneBy(array('name' => 'com.deskpro.apps.test'));

		$app = new AppInstance();
		$app->package = $package;
		$app->title = $package->title;
		$container->getEm()->persist($app);
		$container->getEm()->flush();

		echo "\n";
	}
}
