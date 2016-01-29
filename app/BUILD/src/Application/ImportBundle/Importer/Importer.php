<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace Application\ImportBundle\Importer;

use Application\DeskPRO\App;
use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\Entity\DataStore;
use Application\DeskPRO\EntityRepository;
use Application\ImportBundle\Generator\Exporter\ExporterInterface;
use Application\ImportBundle\Generator\GeneratorConfig;
use Application\ImportBundle\Generator\GeneratorInterface;
use Application\ImportBundle\Generator\ImporterProgressBar;
use Application\ImportBundle\Generator\Writer\WriterInterface;
use Application\ImportBundle\Reader\Csv\CsvConfig;
use Application\ImportBundle\Reader\DeskPRO\DeskPROConfig;
use Application\ImportBundle\Reader\OsTicket\OsTicketConfig;
use Application\ImportBundle\Reader\ZenDesk\ZenDeskConfig;
use Doctrine\ORM\EntityManager;
use Orb\Util\Strings;
use Orb\Zip\Zip;

/**
 * Class Importer.
 */
class Importer
{
    public static $allowed = array(
        ExporterInterface::TYPE_CSV,
        ExporterInterface::TYPE_JSON,
        ExporterInterface::TYPE_OS_TICKET,
        ExporterInterface::TYPE_ZENDESK,
        ExporterInterface::TYPE_DESKPRO,
    );

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entity_manager;

    /**
     * @var EntityRepository\DataStore
     */
    protected $data_store_repository;

    /**
     * @var DeskproBlobStorage
     */
    protected $blob_storage;

    /**
     * @var Zip
     */
    protected $zipper;

    /**
     * Constructor.
     *
     * @param EntityManager      $entity_manager
     * @param DeskproBlobStorage $blob_storage
     * @param Zip                $zipper
     */
    public function __construct(EntityManager $entity_manager, DeskproBlobStorage $blob_storage, Zip $zipper)
    {
        $this->entity_manager        = $entity_manager;
        $this->blob_storage          = $blob_storage;
        $this->data_store_repository = $this->entity_manager->getRepository('DeskPRO:DataStore');
    }

    /**
     * Get current importer name.
     *
     * @return DataStore
     */
    public function getCurrentName()
    {
        $data = $this->data_store_repository->getByName('importers.main');
        if (!$data) {
            return;
        }

        return $data->getData('current');
    }

    /**
     * Set current importer name.
     *
     * @param string $name
     */
    public function setCurrentName($name)
    {
        $data = $this->data_store_repository->getByName('importers.main');
        if (!$data) {
            $data         = new DataStore();
            $data['name'] = 'importers.main';
            $this->entity_manager->persist($data);
        }

        $data->setData('current', $name);
        $this->entity_manager->flush($data);
    }

    /**
     * Get importer by id.
     *
     * @param string $id
     *
     * @throws \RuntimeException
     *
     * @return DataStore
     */
    public function getImporter($id)
    {
        if (!in_array($id, self::$allowed)) {
            throw new \RuntimeException(sprintf('Importer `%s` is not supported', $id));
        }

        $name     = 'importers.'.$id;
        $importer = $this->data_store_repository->getByName($name);

        if ($importer) {
            return $importer;
        }

        $importer         = new DataStore();
        $importer['name'] = $name;
        $importer->setData('id', $id);

        switch ($id) {
            case ExporterInterface::TYPE_CSV:
                $title = 'CSV';
                $desc  = 'Import from CSV (comma-separated values) files.';
                break;
            case ExporterInterface::TYPE_OS_TICKET:
                $title = 'osTicket';
                $desc  = 'Import from an osTicket database.';
                break;
            case ExporterInterface::TYPE_ZENDESK:
                $title = 'ZenDesk';
                $desc  = 'Import from a ZenDesk helpdesk.';
                break;
            case ExporterInterface::TYPE_DESKPRO:
                $title = 'DeskPRO';
                $desc  = 'Import from a DeskPRO helpdesk.';
                break;
            default:
                $title = ucfirst($id);
                $desc  = "Import from $title";
        }

        $importer->setData('title', $title);
        $importer->setData('description', $desc);

        if (ExporterInterface::TYPE_CSV === $id) {
            $data = array('blobs' => array());
            $importer->setData('config', $data);
        }

        $this->entity_manager->persist($importer);
        $this->entity_manager->flush($importer);

        return $importer;
    }

    /**
     * Returns reader config.
     *
     * @param string $id
     *
     * @throws \Exception
     *
     * @return CsvConfig|DeskPROConfig|OsTicketConfig|ZenDeskConfig|null
     */
    public function getReaderConfig($id)
    {
        $importer = $this->getImporter($id);
        $config   = $importer->getData('config');

        $readerConfig = null;
        switch ($id) {
            case ExporterInterface::TYPE_CSV:
                $readerConfig = CsvConfig::fromArray($config);
                break;
            case ExporterInterface::TYPE_ZENDESK:
                $readerConfig = ZenDeskConfig::fromArray($config);
                break;
            case ExporterInterface::TYPE_OS_TICKET:
                $readerConfig = OsTicketConfig::fromArray($config);
                break;
            case ExporterInterface::TYPE_DESKPRO:
                $readerConfig = DeskPROConfig::fromArray($config);
                break;
            default:
                throw new \Exception(sprintf('Unknown importer "%s"', $id));
        }

        return $readerConfig;
    }

    /**
     * Create/copy all necessary dirs/files for import.
     *
     * @param string $id
     *
     * @return DataStore
     */
    public function initReader($id)
    {
        $importer = $this->getImporter($id);
        $config   = $importer->getData('config');

        // Create temp dir
        $tmp = @$config['temp'];
        if (!$tmp) {
            $tmp            = dp_get_tmp_dir().'/importer-'.time();
            $config['temp'] = $tmp;
            $this->entity_manager->flush($importer);
        }

        if (!file_exists($tmp)) {
            mkdir($tmp.'/in', 0777, true);
            mkdir($tmp.'/out', 0777, true);

            // Copy blobs to temp dir
            if (@$config['blobs']) {
                foreach ($config['blobs'] as $blobData) {
                    if (!$blob = $this->entity_manager->find('DeskPRO:Blob', $blobData['id'])) {
                        continue;
                    }

                    $this->blob_storage->copyBlobRecordToFile($tmp.'/in/'.$blob['filename'], $blob);

                    if ('application/zip' === $blob['content_type']) {
                        $this->zipper->decompressZip($tmp.'/in/'.$blob['filename'], $tmp.'/in');
                    }
                }
            }
        }

        // Log file
        $log_file = $importer->getData('logfile');
        if (!$log_file) {
            $log_file = dp_get_log_dir().'/importlog-'.date('Ymd-His').'-'.Strings::random(6, Strings::CHARS_ALPHA_IU);
            $importer->setData('logfile', $log_file);
        }

        $importer->setData('config', $config);
        $this->entity_manager->flush($importer);

        return $importer;
    }

    /**
     * Set state of import.
     *
     * @param $state
     * @param null $id
     *
     * @throws \Exception
     */
    public function setStatus($id, $state)
    {
        $importer = $this->getImporter($id);
        $importer->setData('status', $state);
        $importer->setData('updated', time());
        $this->entity_manager->flush($importer);
    }

    /**
     * @param string $id
     *
     * @return DataStore
     */
    public function startImport($id)
    {
        $importer = $this->initReader($id);

        // set pointer to current import
        $this->setStatus($id, GeneratorInterface::STATUS_PENDING);
        $this->setCurrentName($id);

        // trigger cron to start console command
        file_put_contents(App::$container->getParameter('kernel.dp_config_dir').'/importer_cron.pid', 0);

        return $importer;
    }

    /**
     * @param DataStore $importer
     *
     * @return GeneratorConfig
     */
    public function createGeneratorConfig(DataStore $importer)
    {
        $config = new GeneratorConfig();
        $config->setVerbose(true);
        $config->setExporterType(str_replace('importers.', '', $importer['name']));
        $config->setWriterType(WriterInterface::TYPE_JSON);

        $id = $importer->getData('id');
        $this->initReader($id);
        $config->setReaderConfig($this->getReaderConfig($id));

        return $config;
    }

    /**
     * @param DataStore $importer
     */
    public function cleanup(DataStore $importer)
    {
        $readerConfigData = $importer->getData('config');

        $tmp = @$readerConfigData['temp'];
        if ($tmp && false !== strpos($tmp, 'importer-')) {
            PHP_OS === 'Windows'
                ? exec("rd /s /q {$tmp}")
                : exec("rm -rf {$tmp}");

            unset($readerConfigData['temp']);
            $this->entity_manager->flush($importer);
        }

        $importer->setData('status', null);
        $importer->setData('log', null);
        $importer->setData('updated', null);
        $importer->setData('progress_start', null);
        $importer->setData('progress_step', null);
        $importer->setData('progress_max', null);

        $this->entity_manager->flush($importer);
    }

    /**
     * @param $total_count
     *
     * @return ImporterProgressBar
     */
    public function createProgressBar($total_count)
    {
        $importer = $this->getImporter($this->getCurrentName());

        return new ImporterProgressBar($importer, $this->entity_manager, $total_count);
    }
}
