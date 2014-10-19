<?php

namespace DpIntegrationTests\DeskPRO\Labels;

use Application\DeskPRO\EntityRepository\Setting as SettingRepo;
use Application\DeskPRO\EntityRepository\Brand as BrandRepo;
use Application\DeskPRO\NewSettings\SettingsResolver as Resolver;

class BrandSettingsTest extends \DpIntegrationTestCase
{
	/**
	 * @var BrandRepo
	 */
	private $brands_repo;

	/**
	 * @var SettingRepo
	 */
	private $rep;

	/**
	 * @var Resolver
	 */
	private $settings_resolver;

	public function runBefore()
	{
		$this->helper->enableDatabaseSet('EmptyDB');
		$this->helper->loadFixtures('General/SimpleSettingsData');
		$this->rep = $this->helper->getSymfonyContainer()->getEm()->getRepository('DeskPRO:Setting');
		$this->brands_repo = $this->helper->getSymfonyContainer()->getEm()->getRepository('DeskPRO:Brand');
		$this->settings_resolver = $this->helper->getSymfonyContainer()->getSystemService('settings_resolver');
	}

	public function testGlobalSettings()
	{
		$settings = $this->settings_resolver->getGlobalSettings();

		// "456 key" would be "brand-setting" if we fetched the brand 1 settingbag
		$this->assertEquals('val', $settings->get('123key'));
		$this->assertEquals('val2', $settings->get('456key'));
	}

	public function testBrandSettings()
	{
		$brand = $this->brands_repo->findOneBy(array('name' => 'some_name'));
		$settings = $this->settings_resolver->getBrandSettings($brand->id);

		// "456 key" would be "val2" if we fetched the global settingbag
		$this->assertEquals('val', $settings->get('123key'));
		$this->assertEquals('brand-setting', $settings->get('456key'));
	}
}