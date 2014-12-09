<?php

namespace DpIntegrationTests\DeskPRO\JIRA;

use Application\DeskPRO\App\AppManipulatorContext;
use Application\DeskPRO\App\Package\PackageInstaller;
use Application\DeskPRO\Service\JIRA;
use Doctrine\ORM\EntityRepository;

class APITest extends \DpIntegrationTestCase
{
    /**
     * @var EntityRepository
     */
    protected $rep;

    /**
     * @var JIRA
     */
    protected $service;

    public function runBefore()
    {
        $this->helper->enableDatabaseSet('FreshDb');
        $this->helper->loadFixtures('JIRA/AppData');
    }

    /**
     * @return JIRA
     */
    protected function js()
    {
        if (!$this->service) {
            $this->service = $this->helper->getSymfonyContainer()->get(JIRA::NAME);
        }
        return $this->service;
    }

    public function testGetMeta()
    {
        $meta = $this->js()->getMeta();
        $this->assertInstanceOf('Application\DeskPRO\JIRA\Meta', $meta);
    }
}
