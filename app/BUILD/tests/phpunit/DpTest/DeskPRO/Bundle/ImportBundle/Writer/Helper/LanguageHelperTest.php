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
        $this->assertEquals('eng', $language->getLangCode());
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
        $this->assertEquals('eng', $language->getLangCode());
    }

    /**
     * @return array
     */
    public function languageTitleProvider()
    {
        return [
            ['eng'],
            ['English'],
            ['en_US'],
            ['default'],
        ];
    }
}
