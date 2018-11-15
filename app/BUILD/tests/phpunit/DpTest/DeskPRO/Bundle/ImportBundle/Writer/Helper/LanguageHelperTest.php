<?php

namespace DpTest\DeskPRO\Bundle\ImportBundle\Writer\Helper;

use Application\DeskPRO\Languages\LangPackInfo;
use DeskPRO\Bundle\ImportBundle\Writer\Helper\LanguageHelper;
use DpTest\DeskPRO\Bundle\ImportBundle\Writer\AbstractWriterTest;

/**
 * Class LanguageHelper.
 */
class LanguageHelperTest extends AbstractWriterTest
{
    /**
     * @var LanguageHelper
     */
    private $helper;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->helper = $this->getContainer()->get('dp.importer.writer.helper.language');
        $this->clearTable('languages');

        parent::setUp();
    }

    public function test_language_not_supported()
    {
        $this->assertNull($this->helper->findOrCreateLanguage(''));
        $this->assertNull($this->helper->findOrCreateLanguage('unknown'));
    }

    /**
     * @param string $title
     *
     * @dataProvider languageTitleProvider
     */
    public function test_install_new_language($title)
    {
        $language = $this->helper->findOrCreateLanguage($title);

        $this->assertNotNull($language);
        $this->assertEquals('English', $language->getTitle());
    }

    /**
     * @param string $title
     *
     * @dataProvider languageTitleProvider
     */
    public function test_find_language($title)
    {
        $langPacks = new LangPackInfo();
        $this->em()->persist($langPacks->newLanguageEntity('default'));
        $this->em()->flush();

        $language = $this->helper->findOrCreateLanguage($title);

        $this->assertNotNull($language);
        $this->assertEquals('English', $language->getTitle());
    }

    /**
     * @return array
     */
    public function languageTitleProvider()
    {
        return [
            ['English'],
            ['en-US'],
            ['default'],
        ];
    }
}
