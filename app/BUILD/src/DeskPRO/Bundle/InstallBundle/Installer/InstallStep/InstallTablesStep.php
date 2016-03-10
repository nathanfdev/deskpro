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

namespace DeskPRO\Bundle\InstallBundle\Installer\InstallStep;

use DeskPRO\Bundle\InstallBundle\InstallSession\InstallSession;
use DeskPRO\Bundle\InstallBundle\Schema\Exception\SchemaInstallException;
use DeskPRO\Bundle\InstallBundle\Schema\SchemaArray;
use DeskPRO\Bundle\InstallBundle\Schema\SchemaInstaller;
use Symfony\Component\Process\ProcessBuilder;

class InstallTablesStep extends AbstractStep
{
    public function run()
    {
        $this->writeBigTitle('Installing Database Schema');

        #------------------------------
        # Read schema
        #------------------------------

        $db_cache_dir          = $this->getContext()->getDpEnv()->getAppBaseKernelCacheDir();
        $default_db_cache_path = $db_cache_dir.DIRECTORY_SEPARATOR.'default_deskpro_schema.php';
        $system_db_cache_path  = $db_cache_dir.DIRECTORY_SEPARATOR.'system_deskpro_schema.php';

        $is_dev = $this->getSession()->getSource() === InstallSession::SOURCE_DEV;

        if (!$is_dev && file_exists($default_db_cache_path) && file_exists($system_db_cache_path)) {
            $default_db_schema = SchemaArray::createFromFile($default_db_cache_path);
            $system_db_schema  = SchemaArray::createFromFile($system_db_cache_path);
        } else {
            if (!$is_dev) {
                $this->writeln('<error>No schema file exists</error>');
                $this->writeln('Your installation is missing a critical file. Please re-download DeskPRO and try agian.');
                $this->markAsFailed();

                return;
            }

            $this->writeln('<info>Generating schema file</info>');
            $this->writeln('You are probably a developer. We will execute the schema file command for you:');

            $builder = new ProcessBuilder([
                $this->getSession()->getPaths()->php_path,
                $this->getContext()->getDpEnv()->getDpRoot().'/bin/console',
                'dpdev:gen:schema-files',
                '--barg',
                'is-building',
            ]);

            $proc = $builder->getProcess();
            $this->writeln('<info>$ '.$proc->getCommandLine().'</info>');
            $this->writeln('');
            $proc->run();

            if (!$proc->isSuccessful() || !file_exists($default_db_cache_path) || !file_exists($system_db_cache_path)) {
                $files = "$default_db_cache_path and $system_db_cache_path";
                $this->writeln("<error>Command failed to create $files. Try running it manually.</error>");
                $this->markAsFailed();

                return;
            }

            $default_db_schema = SchemaArray::createFromFile($default_db_cache_path);
            $system_db_schema  = SchemaArray::createFromFile($system_db_cache_path);
        }

        #------------------------------
        # DB connections
        #------------------------------

        try {
            $default_pdo = $this->getSession()->getDbInfo()->getPdo();
            $system_pdo  = $this->getSession()->getSystemDbInfo()->getPdo();
        } catch (\Exception $e) {
            $this->writeln('');
            $this->writeln('<error>There was an error while installing the database:</error>');
            $this->writeln('<info>'.$e->getMessage().'</info>');
            $this->markAsFailed();

            return;
        }

        #------------------------------
        # Install schemas
        #------------------------------
        $this->installSchema($default_pdo, $default_db_schema);
        $this->installSchema($system_pdo, $system_db_schema);

        $this->getSession()->enableFlag('install_tables_ok');
    }

    /**
     * @param \PDO        $pdo
     * @param SchemaArray $schema
     */
    private function installSchema(\PDO $pdo, SchemaArray $schema)
    {
        $schemaInstaller = new SchemaInstaller($schema);

        $progress = $this->createProgressBar(count($schema));
        $progress->setFormat('%current%/%max% [%bar%] %percent:3s%%');

        $progress->start();
        $progress->setRedrawFrequency(5);
        try {
            $schemaInstaller->installSchema($pdo, function ($info) use ($progress) {
                $progress->advance();
            });
        } catch (SchemaInstallException $e) {
            $this->writeln('');
            $this->writeln('<error>There was an error while installing the database:</error>');
            $this->writeln('<info>'.$e->getMessage().'</info>');
            $this->writeln('');
            $this->writeln('The query being executed:');
            $this->writeln('<info>'.$e->getQuery().'</info>');
            $this->markAsFailed();

            // if we have any successfully created tables, we need to unset the db var
            // because we cant resume a half-installed db
            try {
                $tables   = $pdo->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);
                $do_reset = !empty($tables);
            } catch (\Exception $e) {
                $do_reset = true;
            }

            if ($do_reset) {
                $this->getSession()->enableFlag('reset_db_details');
            }

            return;
        }
        $progress->finish();

        $this->writeln('');
        $this->writeln('Done');
        $this->writeln('');
    }

    public function isComplete()
    {
        return $this->getSession()->hasFlag('install_tables_ok');
    }
}
