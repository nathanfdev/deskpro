<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\DevBundle\Command;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Person;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DevTestCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dpdev:test');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        echo __FILE__;
        echo "\n";

        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');

        $entityRepo = $em->getRepository(Person::class);

        $testEntity = $entityRepo->find(1);
//        $testEntity = new AgentTeam();
        // $2a$11$nHDBAi4smPbykQ07RzON3Owiyo/ZFHXM1Ud7VxM6u1vcEau16bJfC
        $testEntity->password = 'ranneft';
        $em->persist($testEntity);
//        $em->remove($testEntity);
        $em->flush($testEntity);

        return 0;
    }
}
