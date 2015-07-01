<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/



namespace Application\ImportBundle\Service;


use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\DataStore;
use Application\ImportBundle\Entity\EntityInterface;
use Application\ImportBundle\Generator\Exporter\ExporterInterface;
use Application\ImportBundle\Generator\Generator;
use Application\ImportBundle\Generator\GeneratorConfig;
use Application\ImportBundle\Generator\Writer\WriterInterface;
use Application\ImportBundle\Reader\Csv\CsvConfig;
use Application\ImportBundle\Reader\OsTicket\OsTicketConfig;
use Application\ImportBundle\Reader\ZenDesk\ZenDeskConfig;
use Orb\Util\Strings;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Import
{
    static public $allowed = array(
        ExporterInterface::TYPE_CSV,
        ExporterInterface::TYPE_OS_TICKET,
        ExporterInterface::TYPE_ZENDESK,
        ExporterInterface::TYPE_DESKPRO,
    );

    const STATUS_PENDING     = 'pending';
    const STATUS_EXPORT      = 'export';
    const STATUS_VALIDATION  = 'validation';
    const STATUS_IMPORT      = 'import';
    const STATUS_DONE        = 'done';

    /**
     * @var DeskproContainer
     */
    protected $c;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Application\DeskPRO\EntityRepository\DataStore
     */
    protected $rep;

    /**
     * @var DataStore
     */
    protected $current;

    public function __construct(DeskproContainer $container)
    {
        $this->c = $container;
        $this->em = $container->getEm();
        $this->rep = $container->getEm()->getRepository('DeskPRO:DataStore');
    }

    /**
     * get current importer name
     * @return DataStore
     */
    public function getCurrentName()
    {
        if (!$data = $this->rep->getByName('importers.main')) {
            return null;
        }

        return $data->getData('current');
    }

    /**
     * set current importer name
     * @param $name
     */
    public function setCurrentName($name)
    {
        if (!$data = $this->rep->getByName('importers.main')) {
            $data = new DataStore();
            $data['name'] = 'importers.main';
            $this->em->persist($data);
        }

        $data->setData('current', $name);
        $this->em->flush($data);
    }

    /**
     * get importer by id
     * @param $id
     * @return DataStore
     */
    public function getImporter($id)
    {
        if (!in_array($id, self::$allowed)) {
            throw new NotFoundHttpException;
        }

        if ($importer = $this->rep->getByName('importers.'.$id)) {
           return $this->current = $importer;
        }

        $importer = new DataStore();
        $importer['name'] = 'importers.'.$id;
        $importer->setData('id', $id);
        $importer->setData('title', ucfirst($id));

        if (ExporterInterface::TYPE_CSV === $id) {
            $data = array('blobs' => array());
            $importer->setData('config', $data);
        }

        $this->em->persist($importer);
        $this->em->flush($importer);
        return $this->current = $importer;
    }

    /**
     * create reader config
     * @param $id
     * @return CsvConfig|OsTicketConfig|ZenDeskConfig|null
     */
    public function getReaderConfig($id)
    {
        $importer = $this->getImporter($id);
        $config = $importer->getData('config');

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
        }

        return $readerConfig;
    }

    /**
     * create/copy all necessary dirs/files for import
     * @param $id
     * @return DataStore
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function initReader($id)
    {
        $importer = $this->getImporter($id);
        $config = $importer->getData('config');

        /**
         * create temp dir
         */
        if (!$tmp = @$config['temp']) {
            $tmp = dp_get_tmp_dir().'/importer-'.time();
            $config['temp'] = $tmp;
            $this->em->flush($importer);
        }
        if (!file_exists($tmp)) {
            mkdir($tmp.'/in', 0777, true);
            mkdir($tmp.'/out', 0777, true);
        }

        /**
         * copy blobs to temp dir
         */
        if (@$config['blobs']) {

            $storage = $this->c->getBlobStorage();

            foreach ($config['blobs'] as $blobData) {
                if (!$blob = $this->em->find('DeskPRO:Blob', $blobData['id'])) {
                    continue;
                }
                $storage->copyBlobRecordToFile($tmp . '/in/' . $blob['filename'], $blob);
            }
        }

        /**
         * log file
         */
        if (!$log_file = $importer->getData('logfile')) {
            $log_file = dp_get_log_dir().'/importlog-'.date('Ymd-His').'-'.Strings::random(6, Strings::CHARS_ALPHA_IU);
            $importer->setData('logfile', $log_file);
        }

        $importer->setData('config', $config);
        $this->em->flush($importer);

        return $importer;
    }

    /**
     * set state of import
     * @param $state
     * @param null $id
     * @throws \Exception
     */
    public function setStatus($id, $state)
    {
        $importer = $this->getImporter($id);
        $importer->setData('status', $state);
        $this->em->flush($importer);
    }

    public function startImport($id)
    {
        $importer = $this->initReader($id);

        // set pointer to current import
        $importer->setData('status', self::STATUS_PENDING);
        $this->em->flush($importer);
        $this->setCurrentName($id);

        // trigger cron to start console command
        file_put_contents(dp_get_data_dir() . '/importer_cron.pid', 0);
        return $importer;
    }

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

    public function cleanup(DataStore $importer)
    {
        $readerConfigData = $importer->getData('config');

        $tmp = @$readerConfigData['temp'];
        if ($tmp && false !== strpos($tmp, 'importer-')) {
            PHP_OS === 'Windows'
                ? exec("rd /s /q {$tmp}")
                : exec("rm -rf {$tmp}");

            unset($readerConfigData['temp']);
            $this->em->flush($importer);
        }
    }
}