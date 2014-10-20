<?php

namespace DpIntegrationTests\DeskPRO\NewSettings\Loader;

use Application\DeskPRO\EntityRepository\Brand as BrandRepo;
use Application\DeskPRO\Brand\BrandStack;

class BrandSettingsTest extends \DpIntegrationTestCase
{
	/**
	 * @var BrandRepo
	 */
	private $brands_repo;

	/**
	 * @var BrandStack
	 */
	private $stack;

	public function runBefore()
	{
		$this->helper->enableDatabaseSet('EmptyDB');
		$this->helper->loadFixtures('General/SimpleSettingsData');
		$this->brands_repo = $this->helper->getSymfonyContainer()->getEm()->getRepository('DeskPRO:Brand');
		$this->stack = $this->helper->getSymfonyContainer()->getSystemService('brand_stack');
	}

	public function testPoppinInAndOutOfBrands()
	{
		$brand1 = $this->brands_repo->find(1);
		$brand2 = $this->brands_repo->find(2);

		$container = $this->stack->push($brand1);
		$this->assertEquals('brand-setting', $container->getSetting('456key'));
		$this->assertSame($brand1, $container->getBrand());

		$container = $this->stack->push($brand2);
		$this->assertNotEquals('brand-setting', $container->getSetting('456key'));
		$this->assertEquals('second brand', $container->getSetting('456key'));
		$this->assertSame($brand2, $container->getBrand());

		$this->stack->pop();
		$container = $this->stack->getActive();
		$this->assertEquals('brand-setting', $container->getSetting('456key'));
		$this->assertSame($brand1, $container->getBrand());
	}
}