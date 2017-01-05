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

namespace DeskPRO\Bundle\InstallBundle\Installer\InstallStep;

use DeskPRO\Bundle\InstallBundle\Installer\InstallProfile;
use DeskPRO\Bundle\InstallBundle\InstallSession\InstallSession;
use DeskPRO\Bundle\InstallBundle\Schema\Exception\SchemaInstallException;
use DeskPRO\Bundle\InstallBundle\Schema\SchemaArray;
use DeskPRO\Bundle\InstallBundle\Schema\SchemaInstaller;
use DeskPRO\Component\Util\StringUtils;
use Symfony\Component\Process\ProcessBuilder;

class InstallTablesStep extends AbstractStep
{
    /**
     * @var array
     */
    private $failedCachePaths = [];

    public function run()
    {
        $this->writeBigTitle('Installing Database Schema');

        //------------------------------
        // Read schema
        //------------------------------

        $dbCachePaths = [];
        foreach (InstallProfile::getDbs() as $dbKey) {
            $dbCachePaths[$dbKey] = $this->getCacheFileName($dbKey);
        }

        $isDev = $this->getSession()->getSource() === InstallSession::SOURCE_DEV;

        if (!$isDev && $this->checkCachePaths($dbCachePaths)) {
            $schemasInfo = $this->createSchemas($dbCachePaths);
        } else {
            if (!$isDev) {
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

            if (!$proc->isSuccessful() || !$this->checkCachePaths($dbCachePaths)) {
                $this->writeln(
                    sprintf(
                        '<error>Command failed to create %s. Try running it manually.</error>',
                        implode(', ', $this->failedCachePaths)
                    )
                );
                $this->markAsFailed();

                return;
            }
            $schemasInfo = $this->createSchemas($dbCachePaths);
        }

        //------------------------------
        // DB connections
        //------------------------------

        try {
            foreach ($schemasInfo as &$item) {
                $method      = StringUtils::toCamelCase(sprintf('get_%s_info', $item['schema_name']), false);
                $item['pdo'] = $this->getSession()->{$method}()->getPDO();
            }
            unset($item);
        } catch (\Exception $e) {
            $this->writeln('');
            $this->writeln('<error>There was an error while installing the database:</error>');
            $this->writeln('<info>'.$e->getMessage().'</info>');
            $this->markAsFailed();

            return;
        }

        //------------------------------
        // Install schemas
        //------------------------------
        foreach ($schemasInfo as $item) {
            $this->installSchema($item['pdo'], $item['schema']);
        }

        $this->getSession()->enableFlag('install_tables_ok');
    }

    /**
     * @param $dbKey
     *
     * @return mixed
     */
    private function getCacheFileName($dbKey)
    {
        if ($dbKey === 'db') {
            $dbKey = 'default_db';
        }
        $dbKey = str_replace('_db', '', $dbKey);

        return sprintf(
            '%s%s%s_deskpro_schema.php',
            $this->getContext()->getDpEnv()->getAppBaseKernelCacheDir(),
            DIRECTORY_SEPARATOR,
            $dbKey
            );
    }

    /**
     * @param $paths
     *
     * @return mixed
     */
    private function checkCachePaths($paths)
    {
        $this->failedCachePaths = [];
        $that                   = $this;

        return array_reduce(
            $paths,
            function ($carry, $path) use ($that) {
                if (!$exists = file_exists($path)) {
                    $that->failedCachePaths = $path;
                }

                return $carry && $exists;
            },
            true
        );
    }

    /**
     * @param $dbPaths
     *
     * @return array
     */
    private function createSchemas($dbPaths)
    {
        $schemasInfo = [];
        foreach ($dbPaths as $schemaName => $path) {
            $schemasInfo[] = [
                'schema'      => SchemaArray::createFromFile($path),
                'schema_name' => $schemaName,
            ];
        }

        return $schemasInfo;
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
                $tables  = $pdo->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);
                $doReset = !empty($tables);
            } catch (\Exception $e) {
                $doReset = true;
            }

            if ($doReset) {
                $this->getSession()->enableFlag('reset_db_details');
            }

            return;
        }
        $progress->finish();

        $this->writeln('');
        $this->writeln('Done');
        $this->writeln('');
    }

    /**
     * @return bool
     */
    public function isComplete()
    {
        return $this->getSession()->hasFlag('install_tables_ok');
    }
}
