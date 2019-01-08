<?php

namespace DpTest\DeskPRO\Bundle\ImportBundle\Writer;

use Application\DeskPRO\Entity;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use DeskPRO\Bundle\AppBundle\Entity\TicketStatus;
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

        $deletedStatus = $this->getRepository(TicketStatus::class)->findOneBy(['sysId' => TicketStatus::SYS_ID_DELETED]);
        if (!$deletedStatus) {
            $deletedStatus = new TicketStatus(TicketStatus::STATUS_TYPE_HIDDEN);
            $deletedStatus->setSysId(TicketStatus::SYS_ID_DELETED);
            $deletedStatus->setTitle('Deleted');
            $this->em()->persist($deletedStatus);
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
