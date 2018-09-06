<?php

/**
 * DeskPRO.
 */

namespace Application\InstallBundle\Upgrade\Build;

use Application\DeskPRO\DBAL\SchemaHelper;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Monolog\NullLogger;
use DeskPRO\Component\Util\MapUtils;
use Doctrine\DBAL\Connection;
use DpRun\LowUtil;
use Orb\Data\ContentTypes;
use Orb\Util\Strings;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;

abstract class AbstractBuild
{
    /**
     * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    protected $container;

    /**
     * @var SchemaHelper
     */
    protected $schema_helper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param DeskproContainer $container
     * @param LoggerInterface  $logger
     */
    public function __construct(DeskproContainer $container, LoggerInterface $logger = null)
    {
        if ($logger === null) {
            $logger = new NullLogger();
        }

        $this->logger = $logger;

        $this->container = $container;
    }

    /**
     * @param $buildId
     * @param $name
     * @param $data
     */
    public function setInstallData($buildId, $name, $data)
    {
        if (is_scalar($data)) {
            $saveData = ['@DATA' => $data];
        } else {
            $saveData = $data;
        }

        $db = $this->getDbConnection();
        $db->delete('install_data', ['build' => $buildId, 'name' => $name]);

        if ($data !== null) {
            $db->insert('install_data', [
                'build' => $buildId,
                'name'  => $name,
                'data'  => json_encode($saveData, \JSON_PRETTY_PRINT),
            ]);
        }
    }

    /**
     * @param $buildId
     * @param $name
     * @param $item
     */
    public function addInstallDataCollection($buildId, $name, $item)
    {
        if (is_scalar($item)) {
            $itemData = ['@DATA' => $item];
        } else {
            $itemData = $item;
        }

        $db         = $this->getDbConnection();
        $recordData = $db->fetchColumn('
          SELECT data
          FROM install_data
          WHERE build = ? AND name = ?
         ', [$buildId, $name]);

        $recordData = @json_decode($recordData, true);
        if (!$recordData || empty($recordData['@ITEMS'])) {
            $recordData = ['@ITEMS' => []];
        }

        $db->delete('install_data', ['build' => $buildId, 'name' => $name]);

        $saveData           = $recordData;
        $saveData['@ITEMS'] = $itemData;

        $db->insert('install_data', [
            'build' => $buildId,
            'name'  => $name,
            'data'  => json_encode($saveData, \JSON_PRETTY_PRINT),
        ]);
    }

    /**
     * @param $buildId
     * @param $name
     *
     * @return mixed
     */
    public function getInstallData($buildId, $name)
    {
        $db         = $this->getDbConnection();
        $recordData = $db->fetchColumn('
          SELECT data
          FROM install_data
          WHERE build = ? AND name = ?
         ', [$buildId, $name]);

        if (!$recordData) {
            return null;
        }

        $recordData = @json_decode($recordData, true);
        if (!$recordData) {
            return null;
        }

        if (isset($recordData['@ITEMS'])) {
            return array_map(function ($v) {
                return isset($v['@DATA']) ? $v['@DATA'] : $v['@DATA'];
            }, $recordData['@ITEMS']);
        }

        return isset($recordData['@DATA']) ? $recordData['@DATA'] : $recordData['@DATA'];
    }

    /**
     * @param $buildId
     * @param $name
     */
    public function deleteInstallData($buildId, $name)
    {
        $this->setInstallData($buildId, $name);
    }

    /**
     * Run through the upgrade.
     */
    public function run()
    {
    }

    /**
     * Write to output.
     *
     * @param string $string
     */
    public function out($string)
    {
        $this->logger->info($string);
    }

    /**
     * Delete all records in a table.
     *
     * @param $connName
     * @param $t
     *
     * @throws \Exception
     */
    public function truncateTable($connName, $t)
    {
        // truncate on cloud seems to destabalise galera
        // probably because truncate is actually a DDL that is the same as DROP/CREATE
        // so we only do that if there are lotttts of rows
        if ($this->getDbConnection($connName)->fetchColumn("SELECT COUNT(*) FROM $t") > 2500) {
            $this->execDbQuery($connName, "TRUNCATE TABLE $t");
        } else {
            $this->execDbQuery($connName, "DELETE FROM $t");
        }
    }

    /**
     * @param string $sql
     * @param bool   $ignore_err
     *
     * @deprecated Use execDbQuery(), or execDbQueryQuiet if you want to suppress errors
     *
     * @throws \Exception
     *
     * @return int Number of affected rows
     */
    public function execMutateSql($sql, $ignore_err = false)
    {
        $sql = preg_replace('#^\s*#m', '', $sql);
        try {
            return $this->container->getDb()->exec($sql);
        } catch (\Exception $e) {
            $this->logger->info('SQL: '.$sql);
            $this->logger->info('Ignored: '.$e->getMessage());
            if (!$ignore_err) {
                throw $e;
            }
        }
    }

    /**
     * Execute a DB query.
     *
     * @param string $connName The connection to use
     * @param string $sql      The query to execute
     *
     * @throws \Exception
     *
     * @return int Number of affected rows
     */
    public function execDbQuery($connName, $sql)
    {
        $db = $this->container->get('doctrine')->getConnection($connName);

        $sql = preg_replace('#^\s*#m', '', $sql);
        try {
            return $db->exec($sql);
        } catch (\Exception $e) {
            $this->logger->info('SQL['.$connName.']: '.$sql);
            $this->logger->info('Error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * @param string $connName
     * @param array  $queries  Array of 'create' and 'alter' queries (such as those found in a generated schema file)
     *
     * @throws \Exception
     */
    public function execDbTableDefArray($connName, array $queries)
    {
        if (!empty($queries['create'])) {
            foreach ($queries['create'] as $sql) {
                $this->execDbQuery($connName, $sql);
            }
        }
        if (!empty($queries['alter'])) {
            foreach ($queries['alter'] as $sql) {
                $this->execDbQuery($connName, $sql);
            }
        }
    }

    /**
     * @param string $connName
     *
     * @return \Application\DeskPRO\DBAL\Connection
     */
    public function getDbConnection($connName = 'default')
    {
        return $this->container->get('doctrine')->getConnection($connName);
    }

    /**
     * Execute a DB query but catch and return any exceptions.
     * Returns exception on error, null on success.
     *
     * @param string $connName The connection to use
     * @param string $sql      The query to execute
     *
     * @return null|\Exception
     */
    public function execDbQueryQuiet($connName, $sql)
    {
        $db = $this->container->get('doctrine')->getConnection($connName);

        $sql = preg_replace('#^\s*#m', '', $sql);
        try {
            $db->exec($sql);

            return;
        } catch (\Exception $e) {
            $this->logger->info('SQL['.$connName.']: '.$sql);
            $this->logger->info('Error: '.$e->getMessage());

            return $e;
        }
    }

    /**
     * Executes an ALTER command that can potentially be slow.
     *
     * If 'online_schema_upgrade' is true on config.upgrader.php,
     * then this will use the 'pt-online-schema-change' command.
     *
     * If the alter is not likely to be slow, it's often better to use the normal
     * execMutateSql method.
     *
     * Note the following VERY IMPORTANT limitations:
     * - The table must have a PK or unique index
     * - You cannot rename a column by dropping + re-adding the same column; the tool
     * wont copy old data if you do this.
     * - If you add a new column as not null, you MUST supply a default value.
     * - To drop a FK, prefix the name with an underscore: fk_foobar becomes _fk_foobar
     * - Note that these should be considered separate from the wrapper script. E.g., you cant set fk checks=0
     *   before running an alter, because the alter might run via the online schema change.
     *
     * @see http://www.percona.com/doc/percona-toolkit/2.2/pt-online-schema-change.html
     *
     * To enable pt-online-schema-change, add this line to /config/config.upgrader.php:
     *  $CONFIG['online_schema_upgrade'] = '/usr/bin/pt-online-schema-change';
     * Change the path accordingly
     *
     * @param string $table The table to alter
     * @param string $alter The alter query, without the 'ALTER TABLE' part
     * @param string $smart Only do slow if the table has more than 20,000 records
     */
    public function execSlowAlterTable($table, $alter, $smart = true)
    {
        $env                = $this->container->get('deskpro.app_env');
        $use_online_upgrade = $env->getConfig('upgrader.online_schema_upgrade');
        $use_online_upgrade = str_replace('%dp.app_dir%', $env->getAppDir(), $use_online_upgrade);

        $do_smart = true;
        if (!$use_online_upgrade) {
            $do_smart = false;
        } else {
            if ($smart) {
                $count = $this->container->getDb()->fetchColumn("SELECT COUNT(*) FROM `$table` LIMIT 20000");
                if ($count < 10000) {
                    $do_smart = false;
                }
            }
        }

        if ($do_smart && $use_online_upgrade) {
            $logger = $this->logger;
            $logger->info('Using online_schema_update');

            if ($use_online_upgrade === true) {
                $tool = 'pt-online-schema-change';
            } elseif (is_string($use_online_upgrade) && is_executable($use_online_upgrade)) {
                $tool = $use_online_upgrade;
            } else {
                throw new \RuntimeException('Unknown path to pt-online-schema-change');
            }

            $logger->info("Tool path: $tool");

            // FKs have leading underscores if the tool has run on the table before.
            // The leading underscores are toggled on/off, each time the tool is run
            // Se also the README in vendor-src/pt-online-schema-change/README.md
            $alter = preg_replace_callback('#DROP\s+FOREIGN\s+KEY\s+(?P<underscore>_?)(?P<fkname>[a-zA-Z0-9_]+)#i', function (array $m) {
                if ($m['underscore']) {
                    return 'DROP FOREIGN KEY '.$m['fkname'];
                } else {
                    return 'DROP FOREIGN KEY _'.$m['fkname'];
                }
            }, $alter);

            $cmd_base = '{tool} --alter {query} --alter-foreign-keys-method drop_swap --no-version-check --recursion-method none --critical-load {critical_load} --max-load {max_load} --host {db_host} --database {db_name} --user {db_user} --password {db_pass} --port {db_port} {mode_param} {dsn}';

            $dbinfo = LowUtil::getMysqlInfoFromConfigArray($env->getConfig('database'));

            $port   = $dbinfo['port'];
            $dbhost = $dbinfo['host'];
            $dbname = $dbinfo['dbname'];

            $params = [
                '{tool}'          => $tool,
                '{query}'         => escapeshellarg($alter),
                '{critical_load}' => escapeshellarg($env->getConfig('upgrader.online_schema_upgrade_critical_load') ?: 'Threads_running=50'),
                '{max_load}'      => escapeshellarg($env->getConfig('upgrader.online_schema_upgrade_max_load') ?: 'Threads_running=25'),
                '{db_host}'       => escapeshellarg($dbhost),
                '{db_port}'       => escapeshellarg($port ?: 3306),
                '{db_name}'       => escapeshellarg($dbname),
                '{db_user}'       => escapeshellarg($env->getConfig('upgrader.online_schema_upgrade_user') ?: $dbinfo['user']),
                '{db_pass}'       => escapeshellarg($env->getConfig('upgrader.online_schema_upgrade_password') ?: $dbinfo['password']),
                '{dsn}'           => "t=$table",
            ];

            $params_test                 = $params;
            $params_test['{mode_param}'] = '--dry-run --print';

            $params_exec                 = $params;
            $params_exec['{mode_param}'] = '--execute';

            $cmd_exec = str_replace(array_keys($params_exec), array_values($params_exec), $cmd_base);

            $logger->info('BEGIN: LIVE');
            $logger->debug('Command: '.str_replace($params['{db_pass}'], '***', $cmd_exec));
            $proc = new Process($cmd_exec, DP_ROOT);
            $proc->setTimeout(43200);
            $proc->run(function ($type, $data) use ($logger) {
                $logger->info(sprintf("\t%s\n", str_replace("\n", "\n\t", trim($data))));
            });
            $logger->info('DONE: LIVE');
            $logger->info('Exit status: '.$proc->getExitCode());

            if (!$proc->isSuccessful()) {
                $logger->critical('!!!!!!!!!!!!!!!');
                throw new \RuntimeException('LIVE run failed with status: '.$proc->getExitCode());
            }
        } else {
            $sql = "ALTER TABLE `$table` $alter";
            $this->execMutateSql('SET FOREIGN_KEY_CHECKS = 0');
            $this->execMutateSql($sql);
            $this->execMutateSql('SET FOREIGN_KEY_CHECKS = 1');
        }
    }

    /**
     * The same as execSlowAlterTable except we ignore most errors.
     *
     * The only time we throw an exception is if the table doesn't exist anymore.
     * This is very edge-casey to do with using pt-online-schema-change with the drop_swap
     * method where the rename failed.
     *
     * @param string $table
     * @param string $alter
     * @param bool   $smart
     */
    public function execSlowAlterTableQuiet($table, $alter, $smart = true)
    {
        try {
            $this->execSlowAlterTable($table, $alter, $smart);
        } catch (\Exception $e) {
            // check the table still exists
            // If this throws, it will propagate up
            $db = $this->container->get('doctrine')->getConnection('default');
            $db->fetchColumn("SELECT 'val' AS test FROM `$table` LIMIT 1");
        }
    }

    /**
     * @static
     *
     * @return string
     */
    public function getBuildId()
    {
        $name = get_class($this);
        $base = \Orb\Util\Util::getBaseClassname($name);

        // Build1293243423 becomes just 1293243423
        $build_id = str_replace('Build', '', $base);

        return $build_id;
    }

    /**
     * @return string
     */
    public function getBackupDir()
    {
        return $this->container->get('deskpro.app_env')->getUserBackupsDir();
    }

    /**
     * @return SchemaHelper
     */
    public function getSchemaHelper()
    {
        if ($this->schema_helper) {
            return $this->schema_helper;
        }

        $this->schema_helper = new SchemaHelper($this->container->getDb());

        return $this->schema_helper;
    }

    /**
     * @param string $name
     * @param mixed  $default
     *
     * @return string
     */
    public function readSetting($name, $default = null)
    {
        $db    = $this->container->getDb();
        $exist = $db->countWithPlaceholders('settings', 'name = ?', [$name]);

        if (!$exist) {
            return $default;
        }

        return $db->fetchColumn('SELECT value FROM settings WHERE name = ?', [$name]);
    }

    /**
     * @param string[] $names
     *
     * @return array
     */
    public function readMultiSetting(array $names)
    {
        $db = $this->container->getDb();

        return array_merge(array_fill_keys($names, null), $db->fetchAllKeyValue('
            SELECT name, value
            FROM settings
            WHERE name IN (?)
        ', [$names], [Connection::PARAM_STR_ARRAY]));
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function saveSetting($name, $value)
    {
        $this->saveMultiSettings([$name => $value]);
    }

    /**
     * @param array $settings
     */
    public function saveMultiSettings(array $settings)
    {
        $names = array_keys($settings);

        $settings_batch = MapUtils::mapToList($settings, function ($name, $val) {
            return ['name' => $name, 'value' => $val];
        });

        $db = $this->container->getDb();
        $db->deleteIn('settings', $names, 'name');
        $db->batchInsert('settings', $settings_batch);
    }

    /**
     * This is an upgrade-safe method of reading blobs.
     *
     * @param string $blobId
     *
     * @return null|string String data on success or null if the blob doesnt exist or couldnt be read
     */
    public function downloadBlob($blobId)
    {
        $db = $this->getDbConnection('default');

        $blob = $db->fetchAll('
            SELECT id, file_url, storage_loc, save_path, filesize
            FROM blob
            WHERE id = ?
        ', [$blobId]);

        if (!$blob) {
            return;
        }

        if (!empty($blob['file_url'])) {
            $data = @file_get_contents($blob['file_url']);
            if ($data === false || (empty($data) && $blob['filesize'])) {
                return;
            } else {
                return $data;
            }
        }

        switch ($blob['storage_loc']) {
            case 'db':
                return implode('', $db->fetchAllCol('SELECT data FROM blobs_storage WHERE blob_id = ? ORDER BY id ASC', [$blobId]));
                break;
            case 'fs':
                /* \DpRun\DpEnv */
                global $DP_ENV;
                $path = $DP_ENV->getUserFilesDir().'/'.$blob['save_path'];

                if (!file_exists($path)) {
                    return;
                }

                return file_get_contents($path);
                break;
            default:
                // we cant handle anything else
                return;
        }
    }

    /**
     * This is an upgrade-safe method of saving blobs.
     *
     * It always uploads blobs to the database, the is currently no way to
     * upload to any other adapter.
     *
     * @param string      $data
     * @param string      $filename
     * @param string|null $contentType
     *
     * @throws \Exception
     *
     * @return int Blob ID
     */
    public function saveBlob($data, $filename, $contentType = null)
    {
        static $maxPacketSize;

        $db = $this->getDbConnection('default');

        if ($maxPacketSize === null) {
            $result        = $db->fetchAssoc("SHOW variables LIKE 'max_allowed_packet'");
            $maxPacketSize = $result['Value'] ?: 5242880;
        }

        $filesize = strlen($data);
        $db->beginTransaction();

        if (!$contentType) {
            $contentType = ContentTypes::getContentTypeFromFilename($filename) ?: 'application/octet-stream';
        }

        $db->insert('blobs', [
            'storage_loc'  => 'db',
            'filename'     => $filename,
            'filesize'     => $filesize,
            'content_type' => $contentType,
            'blob_hash'    => md5($data),
            'date_created' => date('Y-m-d H:i:s'),
        ]);
        $blobId = $db->lastInsertId();

        $batch    = (int) (($blobId - 1) / 1000) + 1;
        $authcode = $blobId.Strings::random(15, Strings::CHARS_KEY_ALPHA).'0';
        $path     = $batch.'/'.$authcode;

        $db->update('blobs', [
            'authcode'  => $authcode,
            'save_path' => $path,
        ], ['id' => $blobId]);

        $data = str_split($data, $maxPacketSize / 2);
        foreach ($data as $part) {
            $db->insert('blobs_storage', [
                'blob_id' => $blobId,
                'data'    => $part,
            ]);
        }

        $db->commit();

        return $blobId;
    }
}
