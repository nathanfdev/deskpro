<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Command;

use Application\DeskPRO\App;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DbCollationChangeCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('dp:db-collation-change');
        $this->addOption('collation', null, InputOption::VALUE_REQUIRED, 'The collation to change to (must be UTF-8)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $collation = $input->getOption('collation');
        if (!$collation || !preg_match('/^utf8_([a-z0-9_]+)_ci$/', $collation)) {
            $output->writeln('No --collation argument given or not a utf8_xxx_ci type.');

            return 1;
        }

        $start = microtime(true);
        $db    = App::getDb();

        set_time_limit(0);

        if (file_exists(dp_get_tmp_dir().'/db-collation-status.txt')) {
            @unlink(dp_get_tmp_dir().'/db-collation-status.txt');
        }
        $write_status = function ($table, $type = 'table') use ($collation) {
            $fp = @fopen(dp_get_tmp_dir().'/db-collation-status.txt', 'w');
            if (!$fp) {
                return false;
            }

            $time = time();
            if (!@fwrite($fp, "[$time|$collation]$type:$table")) {
                return false;
            }
            @fclose($fp);

            return true;
        };

        if (!$write_status('')) {
            $output->writeln("Couldn't write to status file.");

            return 2;
        }
        @chmod(dp_get_tmp_dir().'/db-collation-status.txt', 0777);

        $db->executeQuery('SET foreign_key_checks = 0');

        $tables = $db->fetchAll('SHOW TABLE STATUS');
        foreach ($tables as $table) {
            echo str_pad("Updating $table[Name]...", 50)."\r";
            $write_status($table['Name']);

            $changes = [];
            if ($table['Collation'] != $collation) {
                $changes[] = "DEFAULT CHARACTER SET utf8 COLLATE $collation";
            }

            $columns = $db->fetchAll("SHOW FULL COLUMNS FROM `$table[Name]`");
            foreach ($columns as $column) {
                if (!empty($column['Collation']) && $column['Collation'] != $collation) {
                    $def = "$column[Type] "
                        ." CHARACTER SET utf8 COLLATE $collation "
                        .($column['Null'] == 'NO' ? ' NOT NULL ' : ' NULL ')
                        .($column['Default'] !== null ? ' DEFAULT '.$db->quote($column['Default']) : '')
                        .($column['Extra'] ? " $column[Extra] " : '')
                        .($column['Comment'] ? ' COMMENT '.$db->quote($column['Comment']) : '');
                    $changes[] = "CHANGE  `$column[Field]` `$column[Field]` $def";
                }
            }

            if ($changes) {
                $db->executequery("
                    ALTER TABLE `$table[Name]` ".implode(', ', $changes)
                );
            }
        }

        $db->executeQuery('SET foreign_key_checks = 1');

        App::getContainer()->getSettingsHandler()->setSetting('core.db_collation', $collation);
        @unlink(dp_get_tmp_dir().'/db-collation-status.txt');

        echo str_pad('', 50)."\r";
        $output->writeln(sprintf('Completed in %.4f seconds.', microtime(true) - $start));
    }
}
