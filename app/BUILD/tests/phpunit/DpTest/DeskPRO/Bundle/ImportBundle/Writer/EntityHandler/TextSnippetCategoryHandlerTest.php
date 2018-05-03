<?php

namespace DpTest\DeskPRO\Bundle\ImportBundle\Writer\EntityHandler;

use Application\DeskPRO\Entity;
use DeskPRO\Bundle\ImportBundle\Model;

/**
 * Class TextSnippetCategoryHandlerTest.
 */
class TextSnippetCategoryHandlerTest extends AbstractEntityHandlerTest
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->clearTable('object_lang');
        $this->clearTable('languages');
        $this->clearTable('text_snippet_categories');

        parent::setUp();
    }

    public function test_validation()
    {
        $this->writer->writeModel(new Model\TextSnippetCategory());
        $this->assertTrue($this->loggerHandler->hasErrorRecords());
    }

    public function test_create_and_update_entity()
    {
        $model = $this->createBaseModel();
        $this->writer->writeModel($model);
        $this->em()->clear();

        $entity = $this->getBaseEntity();
        $this->assertNotNull($entity);
        $this->assertEquals('cat_1', $entity->getTitleTranslations()[0]->getValue());

        $model->getTitleTranslations()[0]->setValue('cat_1_updated');
        $this->writer->writeModel($model);
        $this->em()->clear();

        $updatedEntity = $this->getBaseEntity();
        $this->assertNotNull($updatedEntity);
        $this->assertEquals('cat_1_updated', $updatedEntity->getTitleTranslations()[0]->getValue());
        $this->assertEquals($entity->getId(), $updatedEntity->getId());
    }

    public function test_full_params()
    {
        $model = $this->createBaseModel();
        $model->setPerson('1');
        $model->setIsGlobal(true);
        $model->setTypename('chat');

        $this->writer->writeModel($model);
        $this->em()->clear();

        $entity = $this->getBaseEntity();
        $this->assertNotNull($entity);
        $this->assertEquals('chat', $entity->getTypename());
        $this->assertTrue($entity->getIsGlobal());
        $this->assertNotNull($entity->getPerson());
        $this->assertEquals('cat_1', $entity->getTitleTranslations()[0]->getValue());
    }

    /**
     * @return Model\TextSnippetCategory
     */
    private function createBaseModel()
    {
        $model = new Model\TextSnippetCategory();
        $model->setOid(1);
        $model->setTypename('tickets');

        $translation = new Model\Translation();
        $translation->setLanguage('eng');
        $translation->setValue('cat_1');

        $model->addTitleTranslation($translation);

        return $model;
    }

    /**
     * @return Entity\TextSnippetCategory
     */
    private function getBaseEntity()
    {
        return $this->em()->getRepository(Entity\TextSnippetCategory::class)->findOneBy([]);
    }
}
