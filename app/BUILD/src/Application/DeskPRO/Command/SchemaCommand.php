<?php

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
            'audit' => $this->getContainer()->get('doctrine.orm.audit_entity_manager'),
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
