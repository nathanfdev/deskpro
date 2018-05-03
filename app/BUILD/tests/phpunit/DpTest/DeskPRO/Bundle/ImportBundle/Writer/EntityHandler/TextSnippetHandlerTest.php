<?php

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
