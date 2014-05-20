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
 * @subpackage
 */

namespace Application\InstallBundle\Upgrade\Build;

use Application\DeskPRO\Entity\AppInstance;

class Build1398788011 extends AbstractBuild
{
	public function run()
	{
		$plugins = $this->getUpgradeData('201404', 'plugins') ?: array();

		$enabled_plugins = array();
		foreach ($plugins as $p) {
			$enabled_plugins[$p['id']] = $p['id'];
		}

		$this->out("Convert settings into new apps");
		$manager  = $this->container->getAppManager();
		$em       = $this->container->getEm();

		#-------------------------
		# Google Analytics
		#-------------------------

		if ($ga = $this->container->getSetting('core.ga_property_id')) {
			$this->out("Installing Google Analytics");
			$package = $manager->getPackage('deskpro_googleanalytics');
			$app = new AppInstance();
			$app->package = $package;
			$app->title = $package->title;
			$app->setSettings(array('ga_property_id' => $ga));
			$em->persist($app);
			$em->flush();
		}

		#-------------------------
		# Gravatar
		#-------------------------

		if ($this->container->getSetting('core.use_gravatar')) {
			$this->out("Installing Gravatar");
			$package = $manager->getPackage('deskpro_gravatar');
			$app = new AppInstance();
			$app->package = $package;
			$app->title = $package->title;
			$app->setSettings(array());
			$em->persist($app);
			$em->flush();
		}

		#-------------------------
		# Magento
		#-------------------------

		if ($this->container->getSetting('Magento.api_key') && isset($enabled_plugins['Magento'])) {
			$this->out("Installing Magento");

			$settings = array(
				'url'      => $this->container->getSetting('Magento.url'),
				'api_user' => $this->container->getSetting('Magento.api_user'),
				'api_key'  => $this->container->getSetting('Magento.api_key'),
				'widget_ticket'     => true,
				'widget_profile'    => true,
				'enable_usersource' => false, // will be imported next step when importing usersources
				'enable_sso'        => false
			);

			$package = $manager->getPackage('deskpro_magento');
			$app = new AppInstance();
			$app->package = $package;
			$app->title = $package->title;
			$app->setSettings($settings);
			$em->persist($app);
			$em->flush();
		}

		#-------------------------
		# MS Translator
		#-------------------------

		if ($this->container->getSetting('MicrosoftTranslator.client_id') && isset($enabled_plugins['MicrosoftTranslator'])) {
			$this->out("Installing MS Translator");
			$package = $manager->getPackage('deskpro_ms_translator');
			$app = new AppInstance();
			$app->package = $package;
			$app->title = $package->title;
			$app->setSettings(array(
				'client_id'     => $this->container->getSetting('MicrosoftTranslator.client_id'),
				'client_secret' => $this->container->getSetting('MicrosoftTranslator.client_secret'),
			));
			$em->persist($app);
			$em->flush();
		}

		#-------------------------
		# SalesForce
		#-------------------------

		if ($this->container->getSetting('Salesforce.api_user') && isset($enabled_plugins['Salesforce'])) {
			$this->out("Installing SalesForce");
			$package = $manager->getPackage('deskpro_salesforce');
			$app = new AppInstance();
			$app->package = $package;
			$app->title = $package->title;
			$app->setSettings(array(
				'api_user'           => $this->container->getSetting('Salesforce.api_user'),
				'api_password'       => $this->container->getSetting('Salesforce.api_password'),
				'api_security_token' => $this->container->getSetting('Salesforce.api_security_token'),
				'widget_ticket'      => true,
				'widget_profile'     => true,
			));
			$em->persist($app);
			$em->flush();
		}

		#-------------------------
		# Share Widget
		#-------------------------

		if ($this->container->getSetting('core.show_share_widget')) {
			$this->out("Installing ShareWidget");
			$package = $manager->getPackage('deskpro_sharewidget');
			$app = new AppInstance();
			$app->package = $package;
			$app->title = $package->title;
			$app->setSettings(array(
				'show_share_facebook' => (bool)$this->container->getSetting('core.show_share_facebook'),
				'show_share_twitter'  => (bool)$this->container->getSetting('core.show_share_twitter'),
				'show_share_gplus'    => (bool)$this->container->getSetting('core.show_share_gplus'),
				'show_share_linkedin' => (bool)$this->container->getSetting('core.show_share_linkedin'),
			));
			$em->persist($app);
			$em->flush();
		}
	}
}