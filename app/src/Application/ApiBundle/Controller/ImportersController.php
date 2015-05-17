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

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage ApiBundle
 */

namespace Application\ApiBundle\Controller;

use Application\ApiBundle\PermissionStrategy\UserTypePermission;
use Application\DeskPRO\EntityRepository\DataStore;
use Application\DeskPRO\Entity\DataStore as DataStoreEntity;
use Application\DeskPRO\HttpFoundation\Request;
use Application\ImportBundle\Entity\EntityInterface;
use Application\ImportBundle\Generator\Exporter\AbstractExporter;
use Application\ImportBundle\Generator\Exporter\ExporterInterface;
use Application\ImportBundle\Generator\Generator;
use Application\ImportBundle\Generator\GeneratorConfig;
use Application\ImportBundle\Generator\Writer\WriterInterface;
use Application\ImportBundle\Reader\Csv\CsvConfig;
use Application\ImportBundle\Reader\Json\JsonConfig;
use Application\ImportBundle\Reader\ZenDesk\OsTicketConfig;
use Application\ImportBundle\Reader\ZenDesk\ZenDeskConfig;
use Orb\Util\OptionsArray;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ImportersController extends AbstractController implements ProtectedControllerInterface
{
    static public $allowed = array(
        ExporterInterface::TYPE_CSV,
        ExporterInterface::TYPE_OS_TICKET,
        ExporterInterface::TYPE_ZENDESK,
    );

    /**
     * {@inheritDoc}
     */
    public function getPermissionStrategy()
    {
        return new UserTypePermission(UserTypePermission::ADMIN);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        $importers = $this->em->getRepository('DeskPRO:DataStore')->getByPrefix('importers.');

        if (count($importers) !== count(self::$allowed)) {
            $importers = array();
            foreach (self::$allowed as $type) {
                $importers[] = $this->getImporter($type);
            }
        }

        foreach ($importers as $importer) {
            $ret[] = array(
                'id' => str_replace('importers.', '', $importer['name']),
                'title' => $importer->getData('title'),
                'icon' => $importer->getData('icon'),
                'status' => $importer->getData('status'),
                'description' => $importer->getData('description'),
            );
        }

        return $this->createJsonResponse($ret);
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAction($id)
    {
        $importer = $this->getImporter($id);

        return $this->createJsonResponse($importer->getData());
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function saveAction($id, Request $request)
    {
        if (!$data = json_decode($request->getContent(), 1)) {
            throw new BadRequestHttpException;
        }

        $importer = $this->getImporter($id);

        $importer->setData('config', $data['config']);
        $this->em->flush($importer);

        return $this->getAction($id);
    }

    /**
     * test if import ready to start
     *
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function testAction($id, Request $request)
    {
        $importer = $this->getImporter($id);
        $config = $this->createGeneratorConfig($importer);
        /** @var Generator $generator */
        $this->container->set('deskpro.import.config', $config);
        $generator = $this->container->get('deskpro.import.generator');

        try {
            return $this->createJsonResponse(array('result' => $generator->isReady()));
        } catch (\Exception $e) {
            return $this->createJsonResponse(array('error_message' => $e->getMessage()));
        }

    }

    /**
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function startAction($id, Request $request)
    {
        $importer = $this->getImporter($id);
        $importer->setData('status', 'pending');
        $this->em->flush($importer);

        return $this->getAction($id);
    }

    protected function getImporter($id)
    {
        /** @var DataStore $rep */
        /** @var Generator $generator */
        $rep = $this->em->getRepository('DeskPRO:DataStore');

        if (!in_array($id, self::$allowed)) {
            throw new NotFoundHttpException;
        }

        if (!$importer = $rep->getByName('importers.'.$id)) {
            $importer = new DataStoreEntity();
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

            $this->em->persist($importer);
            $this->em->flush($importer);
        }

        return $importer;
    }

    /**
     * @param DataStoreEntity $importer
     * @return GeneratorConfig
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    protected function createGeneratorConfig(DataStoreEntity $importer)
    {
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
            $this->em->flush($importer);

            $config->setInputPath($readerConfigData['temp']);
            $storage = $this->container->getBlobStorage();

            foreach ($readerConfigData['blobs'] as $blobData) {
                if (!$blob = $this->em->find('DeskPRO:Blob', $blobData['id'])) {
                    continue;
                }
                $storage->copyBlobRecordToFile($readerConfigData['temp'].'/'.$blob['filename'], $blob);
            }
        }

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
}
