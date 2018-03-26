<?php

/**
 * DeskPRO.
 *
 * @category TaskQueueJob
 */

namespace Application\DeskPRO\TaskQueueJob;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\DataStore;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;
use Application\DeskPRO\Entity\TaskQueue;
use DeskPRO\Bundle\ImportBundle\CsvImport\CsvImporter;
use DeskPRO\Component\Util\MapUtils;
use Monolog\Logger;
use Orb\Logger\Handler\ArrayHandler;
use Orb\Util\Strings;

/**
 * Class CsvImport.
 */
class CsvImport extends AbstractJob
{
    /**
     * @var array
     */
    protected static $options = [
        'delimeter' => [
            'comma'     => ',',
            'semicolon' => ';',
        ],
        'enclosure' => [
            'none'   => null,
            'quotes' => '"',
        ],
    ];

    /**
     * @var array
     */
    protected static $defaults = [
        'delimeter' => 'comma',
        'enclosure' => 'quotes',
    ];

    /**
     * @param array $options
     *
     * @return array
     */
    public static function getOptions(array $options = [])
    {
        foreach ($options as $k => $v) {
            if (!isset(self::$options[$k]) || !isset(self::$options[$k][$v])) {
                unset($options[$k]);
            }
        }

        $options = array_merge(self::$defaults, $options);
        foreach ($options as $k => &$v) {
            $v = self::$options[$k][$v];
        }

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        if ($this->data['user_filename']) {
            return 'CSV Import: '.$this->data['user_filename'];
        } else {
            return 'CSV Import';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function run($max_time)
    {
        $max_time = 300;
        $em       = App::getOrm();
        $tmpDir   = App::$container->get('deskpro.app_env')->getUserTmpDir();
        $logger   = App::$container->get('dp.importer_logger');
        $handler  = new ArrayHandler(0, Logger::ERROR);
        $logger->pushHandler($handler);
        /** @var CsvImporter $importer */
        $importer = App::$container->get('dp.importer.csv');

        $this->trimLogs();

        /** @var Blob $blob */
        $blob = $em->find(Blob::class, $this->data['blob_id']);
        if (!$blob) {
            return self::TASK_COMPLETED;
        }

        $csvFile = $tmpDir.'/blob-'.$blob->getId().'.csv';

        if (!file_exists($csvFile) || !is_readable($csvFile)) {
            file_put_contents($csvFile, App::getContainer()->getBlobStorage()->copyBlobRecordToString($blob));
        }
        if (!file_exists($csvFile) || !is_readable($csvFile)) {
            throw new \Exception("CSV file $csvFile does not exist or is not readable");
        }

        $startTime = microtime(true);

        $options = self::getOptions($this->data['options']);
        $fp      = fopen($csvFile, 'r');
        fseek($fp, $this->data['fseek']);

        if ($this->data['fseek'] == 0 && $this->data['skip_first']) {
            // skip the first row - it's labels
            fgetcsv($fp, null, $options['delimeter'], $options['enclosure']);
        }

        $complete = false;
        $imported = 0;
        $skipped  = 0;

        while (($imported + $skipped) < 500 && (microtime(true) - $startTime < $max_time)) {
            if (feof($fp)) {
                $complete = true;
                break;
            }

            $row = fgetcsv($fp, null, $options['delimeter'], $options['enclosure']);
            if (!$row) {
                $complete = true;
                break;
            }

            ++$this->data['lines_done'];

            if ($this->importRow($importer, $row, $handler)) {
                ++$this->data['imported'];
                ++$imported;
            } else {
                ++$skipped;
            }

            if (($imported + $skipped) % 100 === 0) {
                $em->clear(Person::class);
                $em->clear(PersonEmail::class);
            }
        }

        $this->data['fseek'] = ftell($fp);

        fclose($fp);

        if ($this->getLogger()) {
            $this->getLogger()->logDebug("Imported $imported people");
        }

        $this->trimLogs();

        $task = $this->getTask();
        $blob = $em->find(Blob::class, $this->data['blob_id']);

        $task->setRunStatus('Processed '.$this->data['lines_done'].' entries, imported '.$this->data['imported'].' people');
        $task->setTaskData(array_merge($task->getTaskData(), $this->data));

        $logStore = $this->getLogStore($task, $blob);
        $logStore->setData('skipped', $logStore->getData('skipped') + $skipped);
        $logStore->setData('imported', $logStore->getData('imported') + $imported);

        if ($complete) {
            $logStore->setData('finished', time());
            $tmpFile = $tmpDir.'/blob-import-log-'.$task->getId().'.csv';

            $taskData = $task->getTaskData();
            $taskLog  = $taskData['log'];

            if ($taskLog && ($fp = fopen($tmpFile, 'w'))) {
                foreach ($taskLog as $logEntry) {
                    fputcsv($fp, [$logEntry], $options['delimeter'], $options['enclosure']);
                }

                fclose($fp);

                $logBlob = App::getContainer()->getBlobStorage()->createBlobRecordFromFile(
                    $tmpFile,
                    'import-log-'.$task->getId().'.csv',
                    'text/csv'
                );

                $task->setTaskData(array_merge($task->getTaskData(), [
                    'log'         => null,
                    'log_blob_id' => $logBlob->getId(),
                ]));

                @unlink($tmpFile);
            }

            @unlink($csvFile);

            try {
                App::getContainer()->getBlobStorage()->deleteBlobRecord($blob);
            } catch (\Exception $e) {
            }

            $ret = self::TASK_COMPLETED;
        } else {
            $ret = self::TASK_CONTINUING;
        }

        $em->persist($logStore);
        $em->persist($task);
        $em->flush();

        return $ret;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultData()
    {
        return [
            'blob_id'            => false,
            'field_maps'         => false,
            'new_custom_map'     => false,
            'skip_first'         => true,
            'update_if_exists'   => true,
            'welcome_email'      => false,
            'welcome_from_name'  => '',
            'welcome_from_email' => '',
            'welcome_subject'    => '',
            'welcome_message'    => '',
            'imported'           => 0,
            'failed'             => 0,
            'lines_done'         => 0,
            'fseek'              => 0,
            'user_filename'      => '',
            'log_blob_id'        => 0,
            'log'                => [],
            'ref'                => null,
        ];
    }

    /**
     * @param array $errors
     */
    protected function log(array $errors)
    {
        ++$this->data['failed'];
        $this->data['log'] = array_merge($this->data['log'], $errors);

        $this->trimLogs();
    }

    /**
     * Trims logs to last 1000. This is important or else logs get too big and
     * exceed memory limit.
     */
    private function trimLogs()
    {
        if (!empty($this->data['log']) && count($this->data['log']) > 1000) {
            $this->data['log'] = array_slice($this->data['log'], 0, -1000);
        }
    }

    /**
     * @param array $row
     *
     * @return int
     */
    protected function importRow(CsvImporter $importer, array $row, ArrayHandler $handler)
    {
        if (isset($row[0]) && $row[0] === null) {
            $this->log(['Empty row']);

            return false;
        }

        $result = false;
        try {
            $row = MapUtils::mapValues($row, function ($k, $v) {
                return Strings::utf8_bad_strip($v);
            });
            $result = $importer->importPerson($this->data['field_maps'], $row, $this->data['ref'], $this->data['welcome_email']);
        } catch (\Exception $e) {
            $this->getLogger()->logDebug('Skipped row due to error: '.$e->getMessage());
            $this->log(['Skipped row due to error: '.$e->getMessage()]);
        }

        if (!$result) {
            // TODO separate error log from this Job, reduce verbosity
            $this->log(['Skipped row due to validation error']);
//            $this->log($handler->getMessages());
        }
        $handler->reset();

        return $result;
    }

    /**
     * @param TaskQueue $task
     * @param Blob      $blob
     *
     * @return DataStore|null
     */
    protected function getLogStore(TaskQueue $task, Blob $blob)
    {
        $em = App::getOrm();

        /** @var \Application\DeskPRO\EntityRepository\DataStore $rep */
        $rep   = $em->getRepository(DataStore::class);
        $store = null;

        $taskData = $task->getTaskData();
        $ref      = isset($taskData['ref']) ? $taskData['ref'] : null;

        if ($ref) {
            $store = $rep->findOneBy(['name' => 'csv_import.'.$ref]);
        }

        if (!$store) {
            if (!$ref) {
                $stores = $rep->getByPrefix('csv_import.'.date('Ymd'));
                if ($stores) {
                    $last = end($stores);
                    $ref  = substr($last['name'], 11, -3).sprintf('%03d', (int) substr($last['name'], -3) + 1);
                } else {
                    $ref = date('Ymd').'-001';
                }

                $this->data['ref'] = $ref;
            }

            $store = new DataStore();
            $store->setName('csv_import.'.$ref);
            $store->setData('file', $blob['filename']);
            $store->setData('started', time());
            $store->setData('finished', 0);
            $store->setData('skipped', 0);
            $store->setData('imported', 0);
        }

        return $store;
    }
}
