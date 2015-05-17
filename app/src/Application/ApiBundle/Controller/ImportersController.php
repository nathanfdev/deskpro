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
use Application\DeskPRO\Entity\DataStore as DataStoreEntity;
use Application\DeskPRO\HttpFoundation\Request;
use Application\DeskPRO\JobQueue\Processor\ImportProcessor;
use Application\ImportBundle\Generator\Generator;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ImportersController extends AbstractController implements ProtectedControllerInterface
{
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

        if (count($importers) !== count(ImportProcessor::$allowed)) {
            $importers = array();
            foreach (ImportProcessor::$allowed as $type) {
                $importers[] = ImportProcessor::getImporter($type, $this->container);
            }
        }

        foreach ($importers as $importer) {
            /** @var $importer DataStoreEntity */
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
        $importer = ImportProcessor::getImporter($id, $this->container);

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

        $importer = ImportProcessor::getImporter($id, $this->container);

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
        $importer = ImportProcessor::getImporter($id, $this->container);
        $config = ImportProcessor::createGeneratorConfig($importer, $this->container);
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
        $importer = ImportProcessor::getImporter($id, $this->container);
        $importer->setData('status', 'pending');

        $queue = $this->container->getJobQueue();
        $queue->add(ImportProcessor::JOB_TYPE, array('id' => $id));

        $this->em->flush();

        return $this->getAction($id);
    }


}
