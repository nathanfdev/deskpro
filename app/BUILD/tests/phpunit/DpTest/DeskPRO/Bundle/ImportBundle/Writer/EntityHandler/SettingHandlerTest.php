<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
