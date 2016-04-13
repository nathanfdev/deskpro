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

namespace Application\DeskPRO\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SchemaCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('dp:schema');
        $this->addOption('update', null, InputOption::VALUE_NONE, 'Runs the various changes to bring schema up to date');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        set_time_limit(0);

        $do_execute = $input->getOption('update');

        foreach ([
            'default' => $this->getContainer()->get('doctrine.orm.default_entity_manager'),
            'sys' => $this->getContainer()->get('doctrine.orm.system_entity_manager'),
        ] as $dbId => $em) {
            $schemadiff = \Application\DeskPRO\ORM\Util\Util::getUpdateSchemaSql($em);
            if ($schemadiff) {
                $db = $em->getConnection();
                foreach ($schemadiff as $line) {
                    $line = "/*db:$dbId*/ ".$line;
                    $output->writeln($line.';');

                    if ($do_execute) {
                        $t1 = microtime(true);

                        try {
                            $db->exec($line);
                            $t2 = microtime(true);
                            $output->writeln(sprintf('<info>-> Okay (%.4fs)</info>', $t2 - $t1));
                        } catch (\Exception $e) {
                            $output->writeln("<warning>Error: Update failed: {$e->getMessage()}</warning>");
                            $output->writeln('<warning>Retry with FOREIGN_KEY_CHECKS off...</warning>');

                            // If it failed, log the error and force it with FK checks off
                            try {
                                $db->exec('SET FOREIGN_KEY_CHECKS = 0');
                                $db->exec($line);
                                $db->exec('SET FOREIGN_KEY_CHECKS = 1');
                                $t2 = microtime(true);
                                $output->writeln(sprintf('<info>-> Retry okay (%.4fs)</info>', $t2 - $t1));
                            } catch (\Exception $e) {
                                $output->writeln("<warning>-> Retry failed: {$e->getMessage()}</warning>");
                                $output->writeln('Aborting...');
                                break;
                            }
                        }
                    }
                }
            }
        }
    }
}
