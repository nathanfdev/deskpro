<?php

namespace DpTest\DeskPRO\Bundle\ImportBundle\Writer\Helper;

use Application\DeskPRO\Entity;
use DeskPRO\Bundle\ImportBundle\Writer\Helper\TextSnippetCategoryHelper;
use DpTest\DeskPRO\Bundle\ImportBundle\Writer\AbstractWriterTest;

/**
 * Class TextSnippetCategoryHelperTest.
 */
class TextSnippetCategoryHelperTest extends AbstractWriterTest
{
    /**
     * @var TextSnippetCategoryHelper
     */
    private $helper;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->helper = $this->getContainer()->get('dp.importer.writer.helper.text_snippet_category');

        $this->clearTable('object_lang');
        $this->clearTable('languages');
        $this->clearTable('text_snippet_categories');

        parent::setUp();
    }

    public function test_create_category_by_digit_oid()
    {
        $category = $this->helper->findOrCreateTextSnippetCategory(1);

        $this->assertNotNull($category);
        $this->assertNotNull($category->getId());
        $this->assertEquals('Category 1', $category->getTitleTranslations()[0]->getValue());
    }

    public function test_create_category_by_name()
    {
        $category = $this->helper->findOrCreateTextSnippetCategory('my_cat');

        $this->assertNotNull($category);
        $this->assertNotNull($category->getId());
        $this->assertEquals('my_cat', $category->getTitleTranslations()[0]->getValue());
    }

    public function test_get_exist_category_by_oid()
    {
        $category = $this->createCategory('my_cat');

        $importMap = new Entity\ImportMap();
        $importMap->setTypename('importer_text_snippet_category');
        $importMap->setNewId($category->getId());
        $importMap->setOldId(5);

        $this->em()->persist($importMap);
        $this->em()->flush();

        $foundCategory = $this->helper->findOrCreateTextSnippetCategory(5);
        $this->assertEquals($category->getId(), $foundCategory->getId());
    }

    public function test_get_exist_category_by_name()
    {
        $category      = $this->createCategory('my_cat');
        $foundCategory = $this->helper->findOrCreateTextSnippetCategory('my_cat');

        $this->assertEquals($category->getId(), $foundCategory->getId());
    }

    public function test_get_exist_category_by_auto_generated_name()
    {
        $category      = $this->createCategory('Category 1');
        $foundCategory = $this->helper->findOrCreateTextSnippetCategory('1');

        $this->assertEquals($category->getId(), $foundCategory->getId());
    }

    /**
     * @param string $name
     *
     * @return Entity\TextSnippetCategory
     */
    private function createCategory($name)
    {
        $category = new Entity\TextSnippetCategory();
        $category->setTypename('tickets');

        $objectLang = new Entity\ObjectLang();
        $objectLang->setObject($category);
        $objectLang->setPropName('title');
        $objectLang->setValue($name);

        $category->getObjectPropsTranslations()->add($objectLang);

        $this->em()->persist($category);
        $this->em()->flush();

        return $category;
    }
}
