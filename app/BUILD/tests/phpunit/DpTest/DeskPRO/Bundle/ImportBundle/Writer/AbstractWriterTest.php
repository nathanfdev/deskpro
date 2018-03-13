<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace DpTest\DeskPRO\Bundle\ImportBundle\Writer;

use Application\DeskPRO\Entity;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use DpTest\ApiTestCase;
use Monolog\Handler\TestHandler;

/**
 * Class AbstractWriterTest.
 */
abstract class AbstractWriterTest extends ApiTestCase
{
    /**
     * @var TestHandler
     */
    protected $loggerHandler;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->loggerHandler = new TestHandler();
        $this->getContainer()->get('dp.importer_logger')->pushHandler($this->loggerHandler);

        // clear db
        $this->clearTable('import_map');

        $brand = $this->getRepository(Entity\Brand::class)->findOneBy(['name' => 'default']);
        if (!$brand) {
            $themeSet = new ThemeSet();
            $themeSet->setThemeId('standard');
            $this->em()->persist($themeSet);

            $editThemeSet = new ThemeSet();
            $editThemeSet->setThemeId('standard');
            $this->em()->persist($editThemeSet);

            $brand = new Entity\Brand();
            $brand->setName('default');
            $brand->setThemeSet($themeSet);
            $brand->setEditThemeSet($editThemeSet);

            $this->em()->persist($brand);
            $this->em()->flush();
        }

        $this->em()->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        $this->em()->clear();
    }

    /**
     * @param string $tableName
     */
    protected function clearTable($tableName)
    {
        $this->em()->getConnection()->executeQuery('DELETE FROM '.$tableName);
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function em()
    {
        return $this->getEntityManager();
    }

    /**
     * @param string $title
     *
     * @return \Application\DeskPRO\Entity\Language|null
     */
    protected function findOrCreateLanguage($title)
    {
        return $this->getContainer()->get('dp.importer.writer.helper.language')->findOrCreateLanguage($title);
    }
}
