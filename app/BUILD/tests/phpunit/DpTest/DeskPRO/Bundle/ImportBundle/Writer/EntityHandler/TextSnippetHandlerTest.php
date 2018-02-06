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
 * Class TextSnippetHandlerTest.
 */
class TextSnippetHandlerTest extends AbstractEntityHandlerTest
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->clearTable('object_lang');
        $this->clearTable('languages');
        $this->clearTable('text_snippets');
        $this->clearTable('text_snippet_categories');

        parent::setUp();
    }

    public function test_validation()
    {
        $this->writer->writeModel(new Model\TextSnippet());
        $this->assertTrue($this->loggerHandler->hasErrorRecords());
    }

    public function test_create_and_update_entity()
    {
        $model = $this->createBaseModel();
        $this->writer->writeModel($model);
        $this->em()->clear();

        $entity = $this->getBaseEntity();
        $this->assertNotNull($entity);
        $this->assertNotNull($entity->getCategory());
        $this->assertNotNull($entity->getPerson());
        $this->assertNotNull($entity->getShortcutCode());
        $this->assertEquals('title_1', $entity->getTitleTranslations()[0]->getValue());
        $this->assertEquals('title_1', $entity->getSnippetTranslations()[0]->getValue());

        $model->getTitleTranslations()[0]->setValue('title_1_updated');
        $this->writer->writeModel($model);
        $this->em()->clear();

        $updatedEntity = $this->getBaseEntity();
        $this->assertNotNull($updatedEntity);
        $this->assertEquals('title_1_updated', $updatedEntity->getTitleTranslations()[0]->getValue());
        $this->assertEquals($entity->getId(), $updatedEntity->getId());
    }

    /**
     * @return Model\TextSnippet
     */
    private function createBaseModel()
    {
        $model = new Model\TextSnippet();
        $model->setOid(1);
        $model->setCategory(1);
        $model->setShortcutCode('code');
        $model->setPerson(1);

        $translation = new Model\Translation();
        $translation->setLanguage('eng');
        $translation->setValue('title_1');

        $model->addTitleTranslation($translation);
        $model->addSnippetTranslation($translation);

        return $model;
    }

    /**
     * @return Entity\TextSnippet
     */
    private function getBaseEntity()
    {
        return $this->em()->getRepository(Entity\TextSnippet::class)->findOneBy([]);
    }
}
