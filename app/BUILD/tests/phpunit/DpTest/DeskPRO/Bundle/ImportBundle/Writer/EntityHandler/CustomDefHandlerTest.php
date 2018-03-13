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
 * Class CustomDefTest.
 */
class CustomDefHandlerTest extends AbstractEntityHandlerTest
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->clearTable('custom_def_ticket');
        $this->clearTable('custom_def_people');
        $this->clearTable('custom_def_article');
        $this->clearTable('custom_def_feedback');
        $this->clearTable('custom_def_organizations');
        $this->clearTable('custom_def_chat');

        parent::setUp();
    }

    /**
     * @dataProvider customDefProvider
     *
     * @param string $modelClass
     */
    public function test_validation($modelClass)
    {
        $this->writer->writeModel(new $modelClass());
        $this->assertTrue($this->loggerHandler->hasErrorRecords());
    }

    /**
     * @dataProvider customDefProvider
     *
     * @param string $modelClass
     * @param string $entityClass
     */
    public function test_create_entity($modelClass, $entityClass)
    {
        /** @var Model\AbstractCustomDef $model */
        $model = new $modelClass();
        $model->setOid(1);
        $model->setTitle('test_def');
        $model->setWidgetType('text');

        $this->writer->writeModel($model);

        /** @var Entity\CustomDefAbstract $expectedDef */
        $expectedDef = $this->em()->getRepository($entityClass)->findOneBy([
            'title'  => 'test_def',
            'parent' => null,
        ]);
        $expectedImportMap = $this->em()->getRepository(Entity\ImportMap::class)->findOneBy([
            'typename' => $this->get('dp.importer.writer.mapper.import_map')->getImportMapKey($model),
            'old_id'   => 1,
        ]);

        $this->assertNotNull($expectedDef);
        $this->assertNotNull($expectedImportMap);
        $this->assertEquals($expectedDef->getId(), $expectedImportMap->getNewId());
    }

    /**
     * @dataProvider customDefProvider
     *
     * @param string $modelClass
     * @param string $entityClass
     */
    public function test_update_entity($modelClass, $entityClass)
    {
        /** @var Model\AbstractCustomDef $model */
        $model = new $modelClass();
        $model->setOid(1);
        $model->setTitle('test_def');
        $model->setWidgetType('text');

        $this->writer->writeModel($model);

        /** @var Entity\CustomDefAbstract $createdDef */
        $createdDef = $this->em()->getRepository($entityClass)->findOneBy([
            'title'  => 'test_def',
            'parent' => null,
        ]);

        $this->assertNotNull($createdDef);
        $model->setTitle('test_def_updated');

        $this->writer->writeModel($model);
        $this->em()->clear();

        /* @var Entity\CustomDefAbstract $updatedDef */
        $updatedDef = $this->em()->getRepository($entityClass)->findOneBy([
            'title'  => 'test_def_updated',
            'parent' => null,
        ]);

        $this->assertNotNull($updatedDef);
        $this->assertEquals($createdDef->getId(), $updatedDef->getId());
    }

    /**
     * @dataProvider customDefProvider
     *
     * @param string $modelClass
     * @param string $entityClass
     */
    public function test_check_entity_props($modelClass, $entityClass)
    {
        /** @var Model\AbstractCustomDef $model */
        $model = new $modelClass();
        $model->setOid(1);
        $model->setTitle('test_def');
        $model->setDescription('test_def_description');
        $model->setWidgetType('textarea');
        $model->setDefaultValue('some text');
        $model->setOptions([
            'required'       => true,
            'agent_required' => false,
        ]);

        $this->writer->writeModel($model);

        /** @var Entity\CustomDefAbstract $expectedDef */
        $expectedDef = $this->em()->getRepository($entityClass)->findOneBy([
            'title'  => 'test_def',
            'parent' => null,
        ]);

        $this->assertNotNull($expectedDef);
        $this->assertEquals($model->getTitle(), $expectedDef->getTitle());
        $this->assertEquals($model->getDescription(), $expectedDef->getDescription());
        $this->assertEquals(Entity\CustomDefAbstract::HANDLER_CLASS_TEXTAREA, $expectedDef->getHandlerClass());
        $this->assertTrue($expectedDef->isAgentField());
        $this->assertTrue($expectedDef->isUserEnabled());
        $this->assertTrue($expectedDef->isEnabled());
        $this->assertEquals('some text', $expectedDef->getDefaultValue());
        $this->assertTrue($expectedDef->getOption('required'));
        $this->assertFalse($expectedDef->getOption('agent_required'));
    }

    /**
     * @dataProvider customDefProvider
     *
     * @param string $modelClass
     * @param string $entityClass
     */
    public function test_check_multichoice_field_options($modelClass, $entityClass)
    {
        /** @var Model\AbstractCustomDef $model */
        $model = new $modelClass();
        $model->setOid(1);
        $model->setTitle('test_def');
        $model->setWidgetType('multichoice');
        $model->setOptions([
            'required'       => true,
            'agent_required' => false,
        ]);

        $this->writer->writeModel($model);

        /** @var Entity\CustomDefAbstract $expectedDef */
        $expectedDef = $this->em()->getRepository($entityClass)->findOneBy([
            'title'  => 'test_def',
            'parent' => null,
        ]);

        $this->assertNotNull($expectedDef);
        $this->assertTrue($expectedDef->getOption('multiple'));
        $this->assertFalse($expectedDef->getOption('expanded'));
        $this->assertTrue($expectedDef->getOption('required'));
        $this->assertFalse($expectedDef->getOption('agent_required'));
    }

    /**
     * @dataProvider customDefProvider
     *
     * @param string $modelClass
     * @param string $entityClass
     */
    public function test_check_checkbox_field_options($modelClass, $entityClass)
    {
        /** @var Model\AbstractCustomDef $model */
        $model = new $modelClass();
        $model->setOid(1);
        $model->setTitle('test_def');
        $model->setWidgetType('checkbox');
        $model->setOptions([
            'required'       => true,
            'agent_required' => false,
        ]);

        $this->writer->writeModel($model);

        /** @var Entity\CustomDefAbstract $expectedDef */
        $expectedDef = $this->em()->getRepository($entityClass)->findOneBy([
            'title'  => 'test_def',
            'parent' => null,
        ]);

        $this->assertNotNull($expectedDef);
        $this->assertTrue($expectedDef->getOption('multiple'));
        $this->assertTrue($expectedDef->getOption('expanded'));
        $this->assertTrue($expectedDef->getOption('required'));
        $this->assertFalse($expectedDef->getOption('agent_required'));
    }

    /**
     * @dataProvider customDefProvider
     *
     * @param string $modelClass
     * @param string $entityClass
     */
    public function test_check_radio_field_options($modelClass, $entityClass)
    {
        /** @var Model\AbstractCustomDef $model */
        $model = new $modelClass();
        $model->setOid(1);
        $model->setTitle('test_def');
        $model->setWidgetType('radio');
        $model->setOptions([
            'required'       => true,
            'agent_required' => false,
        ]);

        $this->writer->writeModel($model);

        /** @var Entity\CustomDefAbstract $expectedDef */
        $expectedDef = $this->em()->getRepository($entityClass)->findOneBy([
            'title'  => 'test_def',
            'parent' => null,
        ]);

        $this->assertNotNull($expectedDef);
        $this->assertFalse($expectedDef->getOption('multiple'));
        $this->assertTrue($expectedDef->getOption('expanded'));
        $this->assertTrue($expectedDef->getOption('required'));
        $this->assertFalse($expectedDef->getOption('agent_required'));
    }

    /**
     * @dataProvider customDefProvider
     *
     * @param string $modelClass
     * @param string $entityClass
     */
    public function test_create_choice_field_with_choice_defs($modelClass, $entityClass)
    {
        // Create choice hierarchy
        $choice1 = new Model\CustomDefChoice();
        $choice1->setTitle('Choice 1');

        $choice1a = new Model\CustomDefChoice();
        $choice1a->setTitle('Choice 1a');
        $choice1->addChoice($choice1a);

        $choice2 = new Model\CustomDefChoice();
        $choice2->setTitle('Choice 2');

        /** @var Model\AbstractCustomDef $model */
        $model = new $modelClass();
        $model->setOid(1);
        $model->setTitle('test_def');
        $model->setWidgetType('choice');
        $model->addChoice($choice1);
        $model->addChoice($choice2);

        $this->writer->writeModel($model);

        /** @var Entity\CustomDefAbstract $createdDef */
        $createdDef = $this->em()->getRepository($entityClass)->findOneBy([
            'title'  => 'test_def',
            'parent' => null,
        ]);

        $this->assertNotNull($createdDef);
        $this->assertEquals(3, $createdDef->getChildren()->count());

        $choiceDef1 = $createdDef->getChildren()[0];
        $choiceDef2 = $createdDef->getChildren()[1];
        $choiceDef3 = $createdDef->getChildren()[2];

        $this->assertEquals('Choice 1', $choiceDef1->getTitle());
        $this->assertNull($choiceDef1->getOption('parent_id'));
        $this->assertEquals('Choice 1a', $choiceDef2->getTitle());
        $this->assertEquals($choiceDef1->getId(), $choiceDef2->getOption('parent_id'));
        $this->assertEquals('Choice 2', $choiceDef3->getTitle());
        $this->assertNull($choiceDef3->getOption('parent_id'));

        // update choice hierarchy
        $choice1 = new Model\CustomDefChoice();
        $choice1->setTitle('Choice 1');

        $choice1b = new Model\CustomDefChoice();
        $choice1b->setTitle('Choice 1b');
        $choice1->addChoice($choice1b);

        $choice3 = new Model\CustomDefChoice();
        $choice3->setTitle('Choice 3');

        /** @var Model\AbstractCustomDef $model */
        $model = new $modelClass();
        $model->setOid(1);
        $model->setTitle('test_def');
        $model->setWidgetType('choice');
        $model->addChoice($choice1);
        $model->addChoice($choice3);

        $this->writer->writeModel($model);
        $this->em()->clear();

        /** @var Entity\CustomDefAbstract $updatedDef */
        $updatedDef = $this->em()->getRepository($entityClass)->findOneBy([
            'title'  => 'test_def',
            'parent' => null,
        ]);

        $this->assertNotNull($updatedDef);
        $this->assertEquals(3, $updatedDef->getChildren()->count());
        $this->assertEquals('Choice 1', $updatedDef->getChildren()[0]->getTitle());
        $this->assertEquals('Choice 1b', $updatedDef->getChildren()[1]->getTitle());
        $this->assertEquals('Choice 3', $updatedDef->getChildren()[2]->getTitle());

        $this->assertEquals($createdDef->getId(), $updatedDef->getId());
        $this->assertEquals($choiceDef1->getId(), $updatedDef->getChildren()[0]->getId());
        $this->assertNotEquals($choiceDef2->getId(), $updatedDef->getChildren()[1]->getId());
        $this->assertNotEquals($choiceDef3->getId(), $updatedDef->getChildren()[2]->getId());
    }

    /**
     * @return array
     */
    public function customDefProvider()
    {
        return [
            [Model\TicketCustomDef::class, Entity\CustomDefTicket::class],
            [Model\PersonCustomDef::class, Entity\CustomDefPerson::class],
            [Model\ArticleCustomDef::class, Entity\CustomDefArticle::class],
            [Model\FeedbackCustomDef::class, Entity\CustomDefFeedback::class],
            [Model\OrganizationCustomDef::class, Entity\CustomDefOrganization::class],
            [Model\ChatCustomDef::class, Entity\CustomDefChat::class],
        ];
    }
}
