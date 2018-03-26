<?php

namespace DpTest\DeskPRO\Bundle\ApiBundle\ApiDoc;

use Application\DeskPRO\Entity\Usergroup;
use DeskPRO\Bundle\ApiBundle\Command\ApiDocCheckCommand;
use DpTest\ApiTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class ApiDocValidationTest.
 */
class ApiDocValidationTest extends ApiTestCase
{
    public function setUp()
    {
        $this->installDataSet('empty', true, true);

        $em = $this->getEntityManager();

        $everyone = new Usergroup();
        $everyone->setSysName(Usergroup::EVERYONE);
        $everyone->setTitle(Usergroup::EVERYONE);
        $em->persist($everyone);

        $registered = new Usergroup();
        $registered->setSysName(Usergroup::REGISTERED);
        $registered->setTitle(Usergroup::REGISTERED);
        $em->persist($registered);
        $em->flush();
    }

    public function test_api_docs()
    {
        $application = new Application($this->getApiKernel());
        $application->add(new ApiDocCheckCommand());

        $this->get('deskpro.feature_flags')->_setSettingsResolver($this->get('settings_resolver'));

        $command = $application->find('dpdev:apidoc-check');
        $command->setApplication($application);
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
        ]);
//        echo ($commandTester->getDisplay());
        $this->assertContains('All fine', $commandTester->getDisplay());
    }
}
