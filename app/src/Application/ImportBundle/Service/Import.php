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
use Application\ImportBundle\Generator\Exporter\ExporterInterface;
use Application\ImportBundle\Generator\Generator;
use Application\ImportBundle\Reader\Csv\CsvConfig;
use Application\ImportBundle\Reader\OsTicket\OsTicketConfig;
use Application\ImportBundle\Reader\ZenDesk\ZenDeskConfig;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Import
{
    static public $allowed = array(
        ExporterInterface::TYPE_CSV,
        ExporterInterface::TYPE_OS_TICKET,
        ExporterInterface::TYPE_ZENDESK,
    );

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

    public function __construct(DeskproContainer $container)
    {
        $this->c = $container;
        $this->em = $container->getEm();
        $this->rep = $container->getEm()->getRepository('DeskPRO:DataStore');
    }

    public function getData()
    {
        if (!$data = $this->rep->getByName('importers.main')) {
            $data = new DataStore();
            $data['name'] = 'importers.main';
            $data->setData('script', null);
            $data->setData('status', null);
            $this->em->persist($data);
            $this->em->flush($data);
        }

        return $data;
    }

    public function getImporter($id)
    {
        if (!in_array($id, self::$allowed)) {
            throw new NotFoundHttpException;
        }

        if ($importer = $this->rep->getByName('importers.'.$id)) {
            return $importer;
        }

        $importer = new DataStore();
        $importer['name'] = 'importers.'.$id;
        $importer->setData('id', $id);
        $importer->setData('title', ucfirst($id));
        $importer->setData('status', null);

        if (ExporterInterface::TYPE_CSV === $id) {
            $data = array('blobs' => array());
            $importer->setData('config', $data);
        }

        $this->em->persist($importer);
        $this->em->flush($importer);
        return $importer;
    }

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

    public function initReader($id)
    {
        $importer = $this->getImporter($id);
        $config = $importer->getData('config');

        /**
         * create temp dir
         */
        if ($tmp = @$config['temp']) {
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

        $importer->setData('config', $config);
        $this->em->flush($importer);

        return $importer;
    }
}