<?php

namespace DpTest\DeskPRO\Bundle\ImportBundle\Writer\Helper;

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ObjectLang;
use DeskPRO\Bundle\ImportBundle\Model\Translation;
use DeskPRO\Bundle\ImportBundle\Writer\Helper\TranslationHelper;
use DpTest\DeskPRO\Bundle\ImportBundle\Writer\AbstractWriterTest;

/**
 * Class TranslationHelperTest.
 */
class TranslationHelperTest extends AbstractWriterTest
{
    /**
     * @var TranslationHelper
     */
    private $helper;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->helper = $this->getContainer()->get('dp.importer.writer.helper.translation');

        $this->clearTable('object_lang');
        $this->clearTable('languages');

        parent::setUp();
    }

    public function test_add_translations()
    {
        $titleTranslations   = [];
        $contentTranslations = [];

        $titleTranslation1 = new Translation();
        $titleTranslation1->setLanguage('en-US');
        $titleTranslation1->setValue('eng title');
        $titleTranslations[] = $titleTranslation1;

        $titleTranslation2 = new Translation();
        $titleTranslation2->setLanguage('fr');
        $titleTranslation2->setValue('fr title');
        $titleTranslations[] = $titleTranslation2;

        $contentTranslation1 = new Translation();
        $contentTranslation1->setLanguage('en-US');
        $contentTranslation1->setValue('eng content');
        $contentTranslations[] = $contentTranslation1;

        $entity = new Article();
        $this->helper->updateTranslations($titleTranslations, $entity, 'title');
        $this->helper->updateTranslations($contentTranslations, $entity, 'content');

        $this->em()->persist($entity);
        $this->em()->flush();

        $id = $entity->getId();
        $this->em()->clear();

        $entity = $this->em()->getRepository(Article::class)->find($id);
        $this->assertNotNull($entity);

        $this->assertCount(2, $entity->getTitleTranslations());
        $this->assertEquals('eng title', $entity->getTitleTranslations()[0]->getValue());
        $this->assertEquals('fr title', $entity->getTitleTranslations()[1]->getValue());

        $this->assertCount(1, $entity->getContentTranslations());
        $this->assertEquals('eng content', $entity->getContentTranslations()[0]->getValue());
    }

    public function test_update_translations()
    {
        // prepare entity
        $entity     = new Article();
        $objectLang = new ObjectLang();
        $objectLang->setLanguage($this->findOrCreateLanguage('en-US'));
        $objectLang->setPropName('title');
        $objectLang->setValue('en title');

        $entity->getObjectPropsTranslations()->add($objectLang);

        $this->em()->persist($entity);
        $this->em()->flush();

        $id = $entity->getId();
        $this->em()->clear();

        $entity = $this->em()->getRepository(Article::class)->find($id);
        $this->assertNotNull($entity);
        $this->assertCount(1,  $entity->getTitleTranslations());
        $this->assertEquals('en title', $entity->getTitleTranslations()[0]->getValue());

        // update translations
        $translations = [];

        $translation1 = new Translation();
        $translation1->setLanguage('it');
        $translation1->setValue('it title');
        $translations[] = $translation1;

        $translation2 = new Translation();
        $translation2->setLanguage('en-US');
        $translation2->setValue('en title edited');
        $translations[] = $translation2;

        $this->helper->updateTranslations($translations, $entity, 'title');

        $this->em()->persist($entity);
        $this->em()->flush();
        $this->em()->clear();

        $this->assertCount(2, $entity->getTitleTranslations());
        $this->assertEquals('en title edited', $entity->getTitleTranslations()[0]->getValue());
        $this->assertEquals('it title', $entity->getTitleTranslations()[1]->getValue());
    }
}
