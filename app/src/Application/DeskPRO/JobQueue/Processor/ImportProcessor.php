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

namespace Application\DeskPRO\JobQueue\Processor;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\DataStore;
use Application\DeskPRO\ORM\EntityManager;
use Application\ImportBundle\Entity\EntityInterface;
use Application\ImportBundle\Generator\Exporter\ExporterInterface;
use Application\ImportBundle\Generator\Exporter\Parser\Json\BatchConfig;
use Application\ImportBundle\Generator\Generator;
use Application\ImportBundle\Generator\GeneratorConfig;
use Application\ImportBundle\Generator\ImporterProgressBar;
use Application\ImportBundle\Generator\Logger\ImporterHandler;
use Application\ImportBundle\Generator\Writer\WriterInterface;
use Application\ImportBundle\Reader\Csv\CsvConfig;
use Application\ImportBundle\Reader\Json\JsonConfig;
use Application\ImportBundle\Reader\OsTicket\OsTicketConfig;
use Application\ImportBundle\Reader\ZenDesk\ZenDeskConfig;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ImportProcessor extends AbstractJobProcessor
{
    const JOB_TYPE = 'deskrpo.import';

    public static $allowed = array(
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
     * {@inheritdoc}
     */
    public function __construct(DeskproContainer $container)
    {
        parent::__construct($container->getEm()->getConnection());
        $this->em        = $container->getEm();
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function setDataOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array('id'));
    }

    public function process(array $data, array $job)
    {
        $em = $this->container->getEm();
        try {
            $importer = self::getImporter($data['id'], $this->container);
        } catch (\Exception $e) {
            return false;
        }

        try {
            $config = self::createGeneratorConfig($importer, $this->container);
            /* @var Generator $generator */
            $this->container->set('deskpro.import.config', $config);
            $generator = $this->container->get('deskpro.import.generator');

            $logger    = new Logger('importer');
            $formatter = new LineFormatter();
            $formatter->ignoreEmptyContextAndExtra(true);
            $handler = new ImporterHandler($importer, $em);
            $handler->setFormatter($formatter);
            $logger->pushHandler($handler);
            $generator->setLogger($logger);

            $total_count = $generator->getTotalRecordsCount();
            //        todo?
            //        $total_count = $generator->getConfig()->hasWriter() ? $total_count * 3 : $total_count * 2;
            $total_count *= 2;
            $progress = new ImporterProgressBar($importer, $em, $total_count);
            $generator->setProgressBarHelper($progress);

            $importer->setData('status', 'exporting');
            $em->flush($importer);
            $progress->start();
            $generator->generate();

            $config
                ->setInputPath($config->getOutputPath())
                ->setOutputPath(null)
                ->setExporterBatchConfig(null)
                ->setExporterType(ExporterInterface::TYPE_JSON)
                ->setWriterType(WriterInterface::TYPE_DESK_PRO);
            $config->setReaderConfig(new JsonConfig($config->getInputPath()));
            $config->setExporterBatchConfig(new BatchConfig());
            $this->container->set('deskpro.import.config', $config);

            $generator = $this->container->get('deskpro.import.generator');
            $generator->setLogger($logger);
            $generator->setProgressBarHelper($progress);

            $importer->setData('status', 'importing');
            $em->flush($importer);
            $progress->start();
            $generator->generate();
            $logger->info("\nDone");
        } catch (\Exception $e) {
            // todo?
            $logger->err("\n".$e->getMessage());
            $logger->info("\nFailed");
            self::cleanup($importer, $this->container);
        }

        $importer->setData('status', 'done');
        $em->flush($importer);

        return true;
    }

    /**
     * @param DataStore        $importer
     * @param DeskproContainer $container
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @return GeneratorConfig
     *
     */
    public static function createGeneratorConfig(DataStore $importer, DeskproContainer $container)
    {
        $em     = $container->getEm();
        $config = new GeneratorConfig();
        $config->setVerbose(true);
        $config->setExporterType(str_replace('importers.', '', $importer['name']));
        $config->setWriterType(WriterInterface::TYPE_JSON);
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

        /*
         * create temp dir
         */
        $tmp = @$readerConfigData['temp'];
        if (!$tmp) {
            $tmp                      = dp_get_tmp_dir().'/importer-'.time();
            $readerConfigData['temp'] = $tmp;
            $em->flush($importer);
        }
        if (!file_exists($tmp)) {
            mkdir($tmp.'/in', 0777, true);
            mkdir($tmp.'/out', 0777, true);
        }

        $config->setInputPath($tmp.'/in');
        $config->setOutputPath($tmp.'/out/');

        /*
         * copy blobs to temp dir
         */
        if (@$readerConfigData['blobs']) {
            $storage = $container->getBlobStorage();

            foreach ($readerConfigData['blobs'] as $blobData) {
                if (!$blob = $em->find('DeskPRO:Blob', $blobData['id'])) {
                    continue;
                }
                $storage->copyBlobRecordToFile($config->getInputPath().'/'.$blob['filename'], $blob);
            }
        }

        $readerConfig = null;
        switch ($config->getExporterType()) {
            case ExporterInterface::TYPE_CSV:
                $readerConfig = CsvConfig::fromArray($readerConfigData);
                break;
            case ExporterInterface::TYPE_ZENDESK:
                $readerConfig = ZenDeskConfig::fromArray($readerConfigData);
                break;
            case ExporterInterface::TYPE_OS_TICKET:
                $readerConfig = OsTicketConfig::fromArray($readerConfigData);
                break;
        }
        $config->setReaderConfig($readerConfig);
        $importer->setData('config', $readerConfigData);
        $em->flush($importer);

        return $config;
    }

    /**
     * @param $id
     * @param DeskproContainer $container
     *
     * @return DataStore
     */
    public static function getImporter($id, DeskproContainer $container)
    {
        /* @var \Application\DeskPRO\EntityRepository\DataStore $rep */
        /* @var Generator $generator */
        $em  = $container->getEm();
        $rep = $em->getRepository('DeskPRO:DataStore');

        if (!in_array($id, self::$allowed)) {
            throw new NotFoundHttpException();
        }

        if (!$importer = $rep->getByName('importers.'.$id)) {
            $importer         = new DataStore();
            $importer['name'] = 'importers.'.$id;
            $importer->setData('id', $id);
            $importer->setData('title', ucfirst($id));
            $importer->setData('status', null);
            $importer->setData('description', null);

            if (ExporterInterface::TYPE_CSV === $id) {
                $config = array('blobs' => array());
                $importer->setData('config', $config);
            }

            $em->persist($importer);
            $em->flush($importer);
        }

        return $importer;
    }

    public static function cleanup(DataStore $importer, DeskproContainer $container)
    {
        $readerConfigData = $importer->getData('config');

        $tmp = @$readerConfigData['temp'];
        if ($tmp && false !== strpos($tmp, 'importer-')) {
            PHP_OS === 'Windows'
                ? exec("rd /s /q {$tmp}")
                : exec("rm -rf {$tmp}");

            unset($readerConfigData['temp']);
            $container->getEm()->flush($importer);
        }
    }
}
