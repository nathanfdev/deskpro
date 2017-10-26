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
