<?php

namespace DeskPRO\Bundle\UpdateBundle\Command;

use Application\DeskPRO\DBAL\SchemaHelper;
use Application\DeskPRO\ORM\Util\Util as ORMUtil;
use DeskPRO\Component\Doctrine\ORM\Tools\SchemaTool;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class FixSchemaCommand.
 */
class FixSchemaCommand extends ContainerAwareCommand
{
    /**
     * @var Connection[]
     */
    private $connections = [];

    /**
     * @var Schema[]
     */
    private $originalSchemas = [];

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('dp:update:fix-schema')
            ->setDescription(<<<'TXT'
Attempts to correct all schema problems.

<info>!!! WARNING !!!</info>
It is very highly recommended to make a database backup first! Running this tool alters your database!
As with any tool that works directly against the database, there is a risk of data loss or data corruption.
Therefore it is very important to backup first.

<info>!!! WARNING !!!</info>
This tool may take a very long time to run depending on the options you choose, how corrupt your schema is,
and the size of your database. While the tool runs, the database may lock as queries are performed.
Ths means that your helpdesk may be offline for an extended period of time if long-running locking
queries are required to fix the schema. The only way to be certain of how long the process will take is
to run the tool on a copy of your database in a test environment first.
TXT
            )
            ->addOption('run', null, InputOption::VALUE_NONE, 'Actually run all the fixes. Without this flag, this tool will only show you a preview of what will happen.')
            ->addOption('fix-tables', null, InputOption::VALUE_NONE, 'Fix tables, columns, column types')
            ->addOption('fix-indexes', null, InputOption::VALUE_NONE, 'Fix indexes')
            ->addOption('fix-fks', null, InputOption::VALUE_NONE, 'Fix foreign key constraints')
            ->addOption('fix-ref-integrity', null, InputOption::VALUE_NONE, 'Fix referential integrity. This will check all FKs to ensure referential integrity between tables. If there is a problem, columns will be set to NULL or rows may be deleted. Since integrity errors may sometimes involve deleting data, it\'s very highly recommend to make a backup first. Deleted records SHOULD be unrefernced or old (i.e. unused or invalid), but you should make a backup anyway just in case.')
            ->addOption('fix-all', null, InputOption::VALUE_NONE, 'Shortcut to enable all --fix-XXX options.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = new Logger('fix-schema');
        $logger->pushHandler(new StreamHandler($this->getContainer()->get('deskpro.app_env')->getUserLogsDir().'/fix-schema.log'));

        if (!$input->getOption('fix-tables')
            && !$input->getOption('fix-indexes')
            && !$input->getOption('fix-fks')
            && !$input->getOption('fix-ref-integrity')
            && !$input->getOption('fix-all')
        ) {
            $output->writeln('Specify at least one fix option. Check --help for usage info.');

            return 1;
        }

        if ($input->getOption('fix-all')) {
            $input->setOption('fix-tables', true);
            $input->setOption('fix-indexes', true);
            $input->setOption('fix-fks', true);
            $input->setOption('fix-ref-integrity', true);
        }

        if (!$input->getOption('run')) {
            $output->writeln('<info>!!! NOTE !!!</info>');
            $output->writeln('You have not specified the --run flag, so no changes are being applied. This is running in PREVIEW mode.');
            $output->writeln('');

            if ($input->getOption('fix-ref-integrity')) {
                $output->writeln('<info>!!! NOTE !!!</info>');
                $output->writeln('You have enabled --fix-ref-integrity, but this is preview mode. If your schema is missing
FKs, then the preview here may not be accurate. For an accurate preview, fix FKs first 
with --fix-fks and THEN run a preview of --fix-ref-integrity. (This is necessary because
FKs must first be correct and accurate before the integrity can be validated.)');
                $output->writeln('');
            }
        }

        /** @var EntityManager[] $entityManagers */
        $entityManagers = [
            'default' => $this->getContainer()->get('doctrine.orm.default_entity_manager'),
            'system'  => $this->getContainer()->get('doctrine.orm.system_entity_manager'),
            'audit'   => $this->getContainer()->get('doctrine.orm.audit_entity_manager'),
        ];

        // update database schema
        if ($input->getOption('fix-tables') || $input->getOption('fix-indexes') || $input->getOption('fix-fks')) {
            $fixDiff = $this->getFixSchemaQueries($entityManagers);

            if ($input->getOption('fix-tables')) {
                $output->writeln('<info>Fixing database tables ...</info>');
                $logger->debug('Fixing database tables.');

                foreach ($fixDiff['tables'] as $dbId => $queries) {
                    foreach ($queries as $query) {
                        $output->writeln('');
                        $output->writeln($query);
                        $logger->error($query);

                        if ($input->getOption('run')) {
                            $this->executeQuery($dbId, $query, $output, $logger);
                        }
                    }
                }

                $output->writeln('');
                $output->writeln('<info>Done fixing tables.</info>');
            }
            if ($input->getOption('fix-indexes')) {
                $output->writeln('<info>Fixing indexes ...</info>');
                $logger->debug('Fixing indexes.');

                foreach ($fixDiff['drop_indexes'] as $dbId => $queries) {
                    foreach ($queries as $query) {
                        $query = $this->correctDropIndexQuery($dbId, $query, $output, $logger);

                        $output->writeln('');
                        $output->writeln($query);
                        $logger->error($query);

                        if ($input->getOption('run')) {
                            $this->executeQuery($dbId, $query, $output, $logger);
                        }
                    }
                }
                foreach ($fixDiff['add_indexes'] as $dbId => $queries) {
                    foreach ($queries as $query) {
                        $output->writeln('');
                        $output->writeln($query);
                        $logger->error($query);

                        if ($input->getOption('run')) {
                            $this->executeQuery($dbId, $query, $output, $logger);
                        }
                    }
                }

                $output->writeln('');
                $output->writeln('<info>Done fixing indexes.</info>');
            }
            if ($input->getOption('fix-fks')) {
                $output->writeln('<info>Fixing foreign keys ...</info>');
                $logger->debug('Fixing foreign keys.');

                foreach ($fixDiff['drop_fks'] as $dbId => $queries) {
                    foreach ($queries as $query) {
                        $query = $this->correctDropForeignKeyQuery($dbId, $query, $output, $logger);

                        $output->writeln('');
                        $output->writeln($query);
                        $logger->error($query);

                        if ($input->getOption('run')) {
                            $this->executeQuery($dbId, $query, $output, $logger);
                        }
                    }
                }
                foreach ($fixDiff['add_fks'] as $dbId => $queries) {
                    foreach ($queries as $query) {
                        $output->writeln('');
                        $output->writeln($query);
                        $logger->error($query);

                        if ($input->getOption('run')) {
                            $this->executeQuery($dbId, $query, $output, $logger);
                        }
                    }
                }

                $output->writeln('');
                $output->writeln('<info>Done fixing foreign keys.</info>');
            }
        }

        // update ref integrity
        if ($input->getOption('fix-ref-integrity')) {
            $output->writeln('<info>Checking integrity. This may take a while.</info>');
            $logger->debug('Checking integrity.');

            try {
                foreach ($entityManagers as $dbId => $em) {
                    $checks = $this->getIntegrityChecks($em);

                    foreach ($checks as $tableName => $tableChecks) {
                        $output->writeln('');
                        $output->writeln("<comment>Table: $tableName</comment>");

                        $logger->debug("Table: $tableName");

                        foreach ($tableChecks as $check) {
                            $fkName = sprintf("\tFK: {$check['name']} (%s)", implode(', ', $check['columns']));

                            $output->writeln('');
                            $output->writeln($fkName);
                            $output->write("\t");

                            $logger->debug($fkName);

                            foreach ($this->getIntegrityDataIterator($dbId, $check, $output, $logger) as $rows) {
                                $output->write('.');

                                // verify foreign rows
                                $queries = $this->getIntegrityFixQueries($dbId, $check, $rows, $output, $logger);
                                foreach ($queries as $query) {
                                    $output->writeln('');
                                    $output->writeln("\t\t-> $query");
                                    $logger->error("\t\t-> $query");

                                    if ($input->getOption('run')) {
                                        $this->executeQuery($dbId, $query, $output, $logger);
                                    }
                                }
                            }
                        }
                    }
                }

                $output->writeln('');
                $output->writeln('<info>Done fixing integrity references.</info>');
            } catch (DBALException $e) {
                $output->writeln('<comment>Unable to perform integrity fixes due to the database error.</comment>');
                $output->writeln('<comment>Try to run the command with --fix-tables --fix-indexes --fix-fks first.</comment>');
            }
        }

        if ($input->getOption('run')) {
            // causes FK check on cron to run to refresh notice in admin (CleanupAlways)
            $this->getContainer()->get('database_connection')->delete('settings', ['name' => 'core.last_fk_check']);
        }

        $output->writeln('<info>All done.</info>');
    }

    /**
     * @param string $dbId
     *
     * @return Connection
     */
    private function getConnection($dbId)
    {
        if (!isset($this->connections[$dbId]) || !$this->connections[$dbId]->isConnected()) {
            $params     = $this->getContainer()->get('deskpro.db_config_reader')->getParams($dbId);
            $connection = $this->getContainer()->get('doctrine.dbal.connection_factory')->createConnection($params);
            $connection->executeQuery('SET FOREIGN_KEY_CHECKS = 0;');

            $this->connections[$dbId] = $connection;
        }

        return $this->connections[$dbId];
    }

    /**
     * @param EntityManager[] $entityManagers
     *
     * @return array
     */
    private function getFixSchemaQueries(array $entityManagers)
    {
        $fixDiff = [
            'tables'  => [],
            'indexes' => [],
            'fks'     => [],
        ];

        foreach ($entityManagers as $dbId => $em) {
            $fixDiff['tables'][$dbId]       = [];
            $fixDiff['add_indexes'][$dbId]  = [];
            $fixDiff['drop_indexes'][$dbId] = [];
            $fixDiff['add_fks'][$dbId]      = [];
            $fixDiff['drop_fks'][$dbId]     = [];

            $schemaDiff = ORMUtil::getUpdateSchemaSql($em);
            if ($schemaDiff) {
                foreach ($schemaDiff as $query) {
                    if (preg_match('/^CREATE (UNIQUE |)INDEX/', $query)) {
                        $fixDiff['add_indexes'][$dbId][] = $query;
                    } elseif (preg_match('/^DROP (UNIQUE |)INDEX/', $query)) {
                        $fixDiff['drop_indexes'][$dbId][] = $query;
                    } elseif (preg_match('/(?<!DROP )FOREIGN KEY/', $query)) {
                        $fixDiff['add_fks'][$dbId][] = $query;
                    } elseif (preg_match('/DROP FOREIGN KEY/', $query)) {
                        $fixDiff['drop_fks'][$dbId][] = $query;
                    } else {
                        $fixDiff['tables'][$dbId][] = $query;
                    }
                }
            }
        }

        return $fixDiff;
    }

    /**
     * @param EntityManager $em
     *
     * @return array
     */
    private function getIntegrityChecks(EntityManager $em)
    {
        $metadata   = $em->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($em);
        $schema     = $schemaTool->getSchemaFromMetadata($metadata);
        $checks     = [];

        foreach ($schema->getTables() as $table) {
            foreach ($table->getForeignKeys() as $foreignKey) {
                $setNull = true;

                // check if we need to delete row by FK 'onDelete' option
                if ($foreignKey->onDelete() === 'CASCADE') {
                    $setNull = false;
                }

                // check if can unset FK referred columns
                foreach ($foreignKey->getColumns() as $columnName) {
                    $column = $table->getColumn($columnName);
                    if ($column->getNotnull()) {
                        $setNull = false;
                    }
                }

                $checks[$table->getName()][] = [
                    'name'           => $foreignKey->getName(),
                    'table'          => $table->getName(),
                    'primaryColumns' => $table->getPrimaryKeyColumns(),
                    'columns'        => $foreignKey->getColumns(),
                    'foreignTable'   => $foreignKey->getForeignTableName(),
                    'foreignColumns' => $foreignKey->getForeignColumns(),
                    'setNull'        => $setNull,
                ];
            }
        }

        return $checks;
    }

    /**
     * @param string          $dbId
     * @param array           $check
     * @param OutputInterface $output
     *
     * @return \Generator
     */
    private function getIntegrityDataIterator($dbId, array $check, OutputInterface $output, LoggerInterface $logger)
    {
        $batchId   = 0;
        $batchSize = 500;

        do {
            $qb = $this->getConnection($dbId)->createQueryBuilder();
            $qb
                ->select(array_merge($check['primaryColumns'], $check['columns']))
                ->from($check['table'])
                ->setMaxResults($batchSize)
                ->setFirstResult($batchId * $batchSize)
            ;

            $rows = $this->executeQuery($dbId, $qb->getSQL(), $output, $logger)->fetchAll();
            ++$batchId;

            yield $rows;
        } while (count($rows) > 0);
    }

    /**
     * @param string          $dbId
     * @param array           $check
     * @param array           $rows
     * @param OutputInterface $output
     * @param LoggerInterface $logger
     *
     * @return string[]
     */
    private function getIntegrityFixQueries($dbId, array $check, array $rows, OutputInterface $output, LoggerInterface $logger)
    {
        // collect foreign ids to check
        $foreignIds = [];
        foreach ($rows as $row) {
            foreach ($check['columns'] as $columnName) {
                if ($row[$columnName]) {
                    $foreignIds[$columnName][$row[$columnName]] = $row[$columnName];
                }
            }
        }

        // no references, skipping
        if (!$foreignIds) {
            return [];
        }

        // prepare foreign query to get referred rows from the foreign table
        $qb = $this->getConnection($dbId)->createQueryBuilder();
        $qb->from($check['foreignTable']);

        foreach ($check['foreignColumns'] as $n => $foreignColumn) {
            $columnName = $check['columns'][$n];
            if (!isset($foreignIds[$columnName])) {
                continue;
            }

            $values = array_values($foreignIds[$columnName]);
            $values = array_map(function ($value) use ($dbId) {
                return $this->getConnection($dbId)->quote($value);
            }, $values);

            $qb->addSelect("$foreignColumn as $columnName");
            $qb->andWhere(sprintf("$foreignColumn IN (%s)", implode(', ', $values)));
        }

        $foreignResults = $this->executeQuery($dbId, $qb->getSQL(), $output, $logger)->fetchAll();
        $actualIds      = [];
        foreach ($foreignResults as $row) {
            foreach ($row as $columnName => $id) {
                $actualIds[$columnName][$id] = $id;
            }
        }

        // compare referred ids to the actual rows
        $queries = [];
        foreach ($rows as $row) {
            $valid = true;
            foreach ($check['columns'] as $n => $columnName) {
                if (!$row[$columnName]) {
                    continue;
                }
                if (!isset($actualIds[$columnName][$row[$columnName]])) {
                    $valid = false;
                    $rowId = implode(',', array_map(function ($column) use ($row) {
                        return $row[$column];
                    }, $check['primaryColumns']));

                    $message = sprintf(
                        "\t\tInvalid reference on row %s: %s.%s=%s references invalid %s.%s=%s",
                        $rowId, $check['table'], $columnName, $row[$columnName], $check['foreignTable'], $check['foreignColumns'][$n], $row[$columnName]
                    );

                    $output->writeln($message);
                    $logger->debug($message);
                }
            }

            if (!$valid) {
                $qb = $this->getConnection($dbId)->createQueryBuilder();

                if ($check['setNull']) {
                    // set referred props to null
                    $qb->update($check['table']);
                    foreach ($check['columns'] as $columnName) {
                        $qb->set($columnName, 'NULL');
                    }
                } else {
                    // unable to unset referred props
                    // removing the row
                    $qb->delete($check['table']);
                }

                foreach ($check['primaryColumns'] as $primaryColumn) {
                    $qb->andWhere("$primaryColumn = {$this->getConnection($dbId)->quote($row[$primaryColumn])}");
                }

                $queries[] = $qb->getSQL();
            }
        }

        return $queries;
    }

    /**
     * @param string          $dbId
     * @param string          $query
     * @param OutputInterface $output
     * @param LoggerInterface $logger
     *
     * @throws \Exception
     *
     * @return \Doctrine\DBAL\Driver\Statement|null
     */
    private function executeQuery($dbId, $query, OutputInterface $output, LoggerInterface $logger)
    {
        $attempt     = 0;
        $maxAttempts = 2;

        while ($attempt < $maxAttempts) {
            try {
                return $this->getConnection($dbId)->executeQuery($query);
            } catch (\Exception $e) {
                $logger->error($e->getMessage());
                $output->writeln("<error>{$e->getMessage()}</error>");

                ++$attempt;
            }
        }

        // break the process if unable to perform a query
        // after a number of attempts
        if (isset($e)) {
            throw $e;
        }

        return;
    }

    /**
     * @param string $dbId
     *
     * @return Schema
     */
    private function getOriginalSchema($dbId)
    {
        if (!isset($this->originalSchemas[$dbId])) {
            $this->originalSchemas[$dbId] = $this->getConnection($dbId)->getSchemaManager()->createSchema();
        }

        return $this->originalSchemas[$dbId];
    }

    /**
     * @param string          $dbId
     * @param string          $query
     * @param OutputInterface $output
     * @param LoggerInterface $logger
     *
     * @return string
     */
    private function correctDropIndexQuery($dbId, $query, OutputInterface $output, LoggerInterface $logger)
    {
        if (preg_match('/DROP INDEX (.+) ON (.+)/', $query, $matches)) {
            list(, $indexName, $tableName) = $matches;

            $schema = $this->getOriginalSchema($dbId);
            $table  = $schema->getTable($tableName);

            try {
                $index        = $table->getIndex($indexName);
                $schemaHelper = new SchemaHelper($this->getConnection($dbId));
                $realIndex    = $schemaHelper->findIndex($tableName, $index->getColumns());
                if ($realIndex) {
                    $query = str_replace('DROP INDEX '.$indexName, "DROP INDEX `{$realIndex->getName()}`", $query);
                }
            } catch (\Exception $e) {
                $output->writeln("<error>Unable to find index for $tableName.$indexName</error>");
                $logger->error("Unable to find index for $tableName.$indexName");
            }
        }

        return $query;
    }

    /**
     * @param string          $dbId
     * @param string          $query
     * @param OutputInterface $output
     * @param LoggerInterface $logger
     *
     * @return string
     */
    private function correctDropForeignKeyQuery($dbId, $query, OutputInterface $output, LoggerInterface $logger)
    {
        if (preg_match('/ALTER TABLE (.+) DROP FOREIGN KEY (.+)/', $query, $matches)) {
            list(, $tableName, $fkName) = $matches;

            $schema = $this->getOriginalSchema($dbId);
            $table  = $schema->getTable($tableName);

            try {
                $foreignKey   = $table->getForeignKey($fkName);
                $schemaHelper = new SchemaHelper($this->getConnection($dbId));
                $realKey      = $schemaHelper->findForeignKey($tableName, $foreignKey->getColumns(), $foreignKey->getForeignTableName(), $foreignKey->getForeignColumns());
                if ($realKey) {
                    $query = str_replace('DROP FOREIGN KEY '.$fkName, "DROP FOREIGN KEY `{$realKey->getName()}`", $query);
                }
            } catch (\Exception $e) {
                $output->writeln("<error>Unable to find FK for $tableName.$fkName</error>");
                $logger->error("Unable to find FK for $tableName.$fkName");
            }
        }

        return $query;
    }
}
