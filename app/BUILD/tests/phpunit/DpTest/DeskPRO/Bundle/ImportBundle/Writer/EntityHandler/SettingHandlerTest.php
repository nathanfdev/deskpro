<?php

namespace DpTest\DeskPRO\Bundle\ImportBundle\Writer\EntityHandler;

use Application\DeskPRO\Entity;
use DeskPRO\Bundle\ImportBundle\Model;

/**
 * Class SettingHandlerTest.
 */
class SettingHandlerTest extends AbstractEntityHandlerTest
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->clearTable('settings');
        parent::setUp();
    }

    public function test_validation()
    {
        $this->writer->writeModel(new Model\Setting());
        $this->assertTrue($this->loggerHandler->hasErrorRecords());
    }

    public function test_create_setting()
    {
        $model = new Model\Setting();
        $model->setName('setting_name');
        $model->setValue('1');

        $this->writer->writeModel($model);

        $entity = $this->getSettingEntity('setting_name');
        $this->assertNotNull($entity);
        $this->assertEquals('1', $entity->getValue());
    }

    public function test_update_setting()
    {
        $entity = new Entity\Setting();
        $entity->setName('setting_name');
        $entity->setValue('1');

        $this->em()->persist($entity);
        $this->em()->flush();
        $this->em()->clear();

        $model = new Model\Setting();
        $model->setName('setting_name');
        $model->setValue('2');

        $this->writer->writeModel($model);

        $entity = $this->getSettingEntity('setting_name');
        $this->assertNotNull($entity);
        $this->assertEquals('2', $entity->getValue());
    }

    /**
     * @param string $name
     *
     * @return Entity\Setting
     */
    private function getSettingEntity($name)
    {
        $this->em()->clear();

        return $this->em()->getRepository(Entity\Setting::class)->findOneBy(['name' => $name]);
    }
}
