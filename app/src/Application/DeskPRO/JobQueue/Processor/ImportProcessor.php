<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at https://www.deskpro.com/eula/                            |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

namespace Application\DeskPRO\JobQueue\Processor;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\DataStore;
use Application\DeskPRO\ORM\EntityManager;
use Application\ImportBundle\Entity\EntityInterface;
use Application\ImportBundle\Generator\Exporter\ExporterInterface;
use Application\ImportBundle\Generator\Generator;
use Application\ImportBundle\Generator\GeneratorConfig;
use Application\ImportBundle\Generator\Writer\WriterInterface;
use Application\ImportBundle\Reader\Csv\CsvConfig;
use Application\ImportBundle\Reader\ZenDesk\OsTicketConfig;
use Application\ImportBundle\Reader\ZenDesk\ZenDeskConfig;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ImportProcessor extends AbstractJobProcessor
{
    const JOB_TYPE = 'deskrpo.import';

    static public $allowed = array(
        ExporterInterface::TYPE_CSV,
        ExporterInterface::TYPE_OS_TICKET,
        ExporterInterface::TYPE_ZENDESK,
    );

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var DeskproContainer
     */
    protected $container;

    /**
     * @inheritdoc
     */
    public function __construct(DeskproContainer $container)
    {
        parent::__construct($container->getEm()->getConnection());
        $this->em = $container->getEm();
        $this->container = $container;
    }

    /**
     * @inheritdoc
     */
    public function setDataOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array('id'));
    }

    public function process(array $data, array $job)
    {
        $importer = ImportProcessor::getImporter($data['id'], $this->container);
        $config = ImportProcessor::createGeneratorConfig($importer, $this->container);
        /** @var Generator $generator */
        $this->container->set('deskpro.import.config', $config);
        $generator = $this->container->get('deskpro.import.generator');


    }

    /**
     * @param DataStore $importer
     * @param DeskproContainer $container
     * @return GeneratorConfig
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    static public function createGeneratorConfig(DataStore $importer, DeskproContainer $container)
    {
        $em = $container->getEm();
        $config = new GeneratorConfig();
        $config->setExporterType(str_replace('importers.', '', $importer['name']));
        $config->setWriterType(WriterInterface::TYPE_DESK_PRO);
        $readerConfigData = $importer->getData('config');

        $supported_types = array(
            EntityInterface::TYPE_TICKET,
            EntityInterface::TYPE_PERSON,
            EntityInterface::TYPE_ARTICLE,
            EntityInterface::TYPE_DOWNLOAD,
            EntityInterface::TYPE_FEEDBACK,
            EntityInterface::TYPE_NEWS,
        );

        foreach ($supported_types as $type) {
            $config->addEntityType($type);
        }

        /**
         * copy blobs to temp dir
         */
        if (@$readerConfigData['blobs']) {

            if (@$readerConfigData['temp'] && false !== strpos($readerConfigData['temp'], 'importer-')) {
                PHP_OS === 'Windows'
                    ? exec("rd /s /q {$readerConfigData['temp']}")
                    : exec("rm -rf {$readerConfigData['temp']}");
            }

            $readerConfigData['temp'] = dp_get_tmp_dir().'/importer-'.time();
            mkdir($readerConfigData['temp']);

            $importer->setData('config', $readerConfigData);
            $em->flush($importer);

            $config->setInputPath($readerConfigData['temp']);
            $storage = $container->getBlobStorage();

            foreach ($readerConfigData['blobs'] as $blobData) {
                if (!$blob = $em->find('DeskPRO:Blob', $blobData['id'])) {
                    continue;
                }
                $storage->copyBlobRecordToFile($readerConfigData['temp'].'/'.$blob['filename'], $blob);
            }
        }

        $readerConfig = null;
        switch ($config->getExporterType()) {
            case ExporterInterface::TYPE_CSV:
                $readerConfig = CsvConfig::fromArray($importer->getData('config'));
                break;
            case ExporterInterface::TYPE_ZENDESK:
                $readerConfig = ZenDeskConfig::fromArray($importer->getData('config'));
                break;
            case ExporterInterface::TYPE_OS_TICKET:
                $readerConfig = OsTicketConfig::fromArray($importer->getData('config'));
                break;
        }
        $config->setReaderConfig($readerConfig);

        return $config;
    }

    /**
     * @param $id
     * @param DeskproContainer $container
     * @return DataStore
     */
    static public function getImporter($id, DeskproContainer $container)
    {
        /** @var \Application\DeskPRO\EntityRepository\DataStore $rep */
        /** @var Generator $generator */
        $em = $container->getEm();
        $rep = $em->getRepository('DeskPRO:DataStore');

        if (!in_array($id, self::$allowed)) {
            throw new NotFoundHttpException;
        }

        if (!$importer = $rep->getByName('importers.'.$id)) {
            $importer = new DataStore();
            $importer['name'] = 'importers.'.$id;
            $importer->setData('title', ucfirst($id));
            $importer->setData('icon', null);
            $importer->setData('status', null);
            $importer->setData('description', null);

            $config = array();
            if (ExporterInterface::TYPE_CSV === $id) {
                $config['blobs'] = array();
            }
            $importer->setData('config', $config);

            $em->persist($importer);
            $em->flush($importer);
        }

        return $importer;
    }
}