<?php

namespace DeskPRO\Bundle\InstallBundle\Installer\InstallStep;

use DeskPRO\Bundle\InstallBundle\Installer\InstallerContext;
use DeskPRO\Bundle\InstallBundle\Installer\InstallProfile;
use DeskPRO\Bundle\InstallBundle\InstallSession\InstallSession;
use DeskPRO\Bundle\InstallBundle\Schema\Exception\SchemaInstallException;
use DeskPRO\Bundle\InstallBundle\Schema\SchemaArray;
use DeskPRO\Bundle\InstallBundle\Schema\SchemaInstaller;
use DeskPRO\Component\Lock\PdoStore;
use DeskPRO\Component\Util\StringUtils;
use Symfony\Component\Process\ProcessBuilder;

class InstallTablesStep extends AbstractStep
{
    const TRUNCATE_DB = 'truncate';
    const RECREATE_DB = 'recreate';

    /**
     * @var array
     */
    private $failedCachePaths = [];

    /**
     * @var string|null
     */
    private $dbExistAction;

    public function __construct(InstallerContext $context, $dbExistAction = null)
    {
        parent::__construct($context);
        $this->dbExistAction = $dbExistAction;
    }

    public function run()
    {
        $this->writeBigTitle('Installing Database Schema');

        if ($this->dbExistAction) {
            try {
                $pdo    = $this->getSession()->getDbInfo()->getPdo();
                $dbname = $this->getSession()->getDbInfo()->dbname;
                $tables = $pdo->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);
                if (!empty($tables)) {
                    $this->write('');

                    if ($this->dbExistAction === self::TRUNCATE_DB) {
                        $this->writeln('<info>Database '.$dbname.' already has tables. Doing a truncate (because you used the --truncate-db flag)</info>');
                        $this->writeln('NOTE: We did not check the schema. If the schema has changed, you need to use a new db.');
                        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
                        foreach ($tables as $t) {
                            $pdo->exec("TRUNCATE TABLE {$t}");
                        }
                        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
                        $this->getSession()->enableFlag('install_tables_ok');

                        return;
                    } elseif ($this->dbExistAction === self::RECREATE_DB) {
                        $this->writeln('<info>Database '.$dbname.' already has tables. Recreating database.</info>');
                        $pdo->exec("DROP DATABASE {$dbname}");
                        $pdo->exec("CREATE DATABASE {$dbname}");
                    }
                }
            } catch (\Exception $e) {
                $this->writeln('<info>'.$e->getMessage().'</info>');
            }
        }

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

        //------------------------------
        // Install PDO lock table
        //------------------------------

        $store = new PdoStore($this->getSession()->getDbInfo()->getPdo());
        $store->createTable();

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
                    $that->failedCachePaths[] = $path;
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
