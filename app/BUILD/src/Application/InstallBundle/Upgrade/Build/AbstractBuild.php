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
     * @var bool
     */
    protected $rerun = false;

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
        $this->init();
    }

    /**
     * Saves data to the filesystem (into the tmp dir). Will be overwritten if it already exists.
     *
     * @param string       $tag
     * @param string       $name
     * @param string|array $data Array data will be json_encoded, string data will be written as-is
     *
     * @return string|false Filename written when successful, or false if failed to write
     */
    public function saveUpgradeData($tag, $name, $data, $throw_exception = true)
    {
        if (is_array($data)) {
            $fname = 'updata-'.$tag.'.'.$name.'.json';
            $path  = dp_get_tmp_dir().DIRECTORY_SEPARATOR.$fname;
            $data  = json_encode($data);
            if (file_put_contents($path, $data) === false) {
                if ($throw_exception) {
                    throw new \RuntimeException("Failed to write upgrade data file to: $path");
                }

                return false;
            }
        } else {
            $fname = 'updata-'.$tag.'.'.$name.'.dat';
            $path  = dp_get_tmp_dir().DIRECTORY_SEPARATOR.$fname;
            $data  = (string) $data;
            if (file_put_contents($path, $data) === false) {
                if ($throw_exception) {
                    throw new \RuntimeException("Failed to write upgrade data file to: $path");
                }

                return false;
            }
        }

        @chmod($path, 0777);

        return $path;
    }

    /**
     * Read previously saved upgrade data.
     *
     * @param string $tag
     * @param string $name
     *
     * @return array|null|string Array for JSON-encoded array data, string for string data or null if file could not be found
     */
    public function getUpgradeData($tag, $name)
    {
        $name_part = 'updata-'.$tag.'.'.$name.'.';
        $path_part = dp_get_tmp_dir().DIRECTORY_SEPARATOR.$name_part;

        if (file_exists($path_part.'json')) {
            $data = file_get_contents($path_part.'json');
            $data = json_decode($data, true);

            return $data;
        } elseif (file_exists($path_part.'dat')) {
            $data = file_get_contents($path_part.'dat');

            return $data;
        } else {
            return;
        }
    }

    /**
     * Empty hook into the constructor.
     */
    protected function init()
    {
    }

    /**
     * Run through the upgrade.
     */
    abstract public function run();

    /**
     * Set this build handler to run again.
     * This allows "pages" to run. The "runcount" (fetch with getStatus('runcount')) will be
     * incremented automatically.
     *
     * @param bool $rerun
     *
     * @return bool
     */
    public function setRerun($rerun = true)
    {
        return $this->rerun = (bool) $rerun;
    }

    /**
     * @return bool
     */
    public function shouldRerun()
    {
        return $this->rerun;
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
     * @param string $sql
     * @param bool   $ignore_err
     *
     * @throws \Exception
     */
    public function execMutateSql($sql, $ignore_err = false)
    {
        $sql = preg_replace('#^\s*#m', '', $sql);
        try {
            $this->container->getDb()->exec($sql);
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
     */
    public function execDbQuery($connName, $sql)
    {
        $db = $this->container->get('doctrine')->getConnection($connName);

        $sql = preg_replace('#^\s*#m', '', $sql);
        try {
            $db->exec($sql);
        } catch (\Exception $e) {
            $this->logger->info('SQL['.$connName.']: '.$sql);
            $this->logger->info('Error: '.$e->getMessage());
            throw $e;
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

            $cmd_base = '{tool} --alter {query} --alter-foreign-keys-method drop_swap --no-version-check --recursion-method none --host {db_host} --database {db_name} --user {db_user} --password {db_pass} --port {db_port} {mode_param} {dsn}';

            $dbinfo = LowUtil::getMysqlInfoFromConfigArray($env->getConfig('database'));

            $port   = $dbinfo['port'];
            $dbhost = $dbinfo['host'];
            $dbname = $dbinfo['dbname'];

            $params = [
                '{tool}'    => $tool,
                '{query}'   => escapeshellarg($alter),
                '{db_host}' => escapeshellarg($dbhost),
                '{db_port}' => escapeshellarg($port ?: 3306),
                '{db_name}' => escapeshellarg($dbname),
                '{db_user}' => escapeshellarg($env->getConfig('upgrader.online_schema_upgrade_user') ?: $dbinfo['user']),
                '{db_pass}' => escapeshellarg($env->getConfig('upgrader.online_schema_upgrade_password') ?: $dbinfo['password']),
                '{dsn}'     => "t=$table",
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
     * Save status data (ex. steps completed etc).
     *
     * @param $key
     * @param $val
     */
    public function saveStatus($key, $val)
    {
        $this->container->getDb()->replace('import_datastore', [
            'typename' => 'up.'.$this->getBuildId().'.'.$key,
            'data'     => $val,
        ]);
    }

    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getStatus($key, $default = null)
    {
        $val = $this->container->getDb()->fetchArray('
            SELECT data
            FROM import_datastore
            WHERE typename = ?
        ', ['up.'.$this->getBuildId().'.'.$key]);

        if (!$val) {
            return $default;
        }

        return $val[0];
    }

    public function getDefaultCollation()
    {
        try {
            $collation = \Application\DeskPRO\App::getSetting('core.db_collation');
        } catch (\Exception $e) {
            $collation = null;
        }

        return $collation ?: 'utf8_general_ci';
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
     *
     * @return string
     */
    public function readSetting($name)
    {
        $db = $this->container->getDb();

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

        return $db->fetchAllKeyValue('
            SELECT name, value
            FROM settings
            WHERE name IN (?)
        ', [$names], [Connection::PARAM_STR_ARRAY]);
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
