<?php
namespace DpFixtures\General;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Setting;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class SimpleSettingsData extends AbstractFixture
{
	public function load(ObjectManager $manager)
	{
		$setting = new Setting();
		$setting->name = '123key';
		$setting->value = 'val';
		$manager->persist($setting);

		$setting = new Setting();
		$setting->name = '456key';
		$setting->value = 'val2';
		$manager->persist($setting);

		$brand = new Brand();
		$brand->name = 'some_name';
		$manager->persist($brand);

		$setting = new Setting();
		$setting->name = '456key';
		$setting->value = 'brand-setting';
		$setting->brand = $brand;
		$manager->persist($setting);

		$manager->flush();
	}
}