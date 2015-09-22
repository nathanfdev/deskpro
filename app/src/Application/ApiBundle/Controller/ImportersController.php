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
use Application\ImportBundle\Generator;
use Application\ImportBundle\Service\Import as ImportService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class ImportersController
 * @package Application\ApiBundle\Controller
 */
class ImportersController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * @var ImportService
     */
    protected $is;

    /**
     * {@inheritDoc}
     */
    public function getPermissionStrategy()
    {
        return new UserTypePermission(UserTypePermission::ADMIN);
    }

    /**
     * @return ImportService
     */
    protected function is()
    {
        if (!$this->is) {
            $this->is = $this->get('deskpro.import');
        }

        return $this->is;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        $importers = $this->em->getRepository('DeskPRO:DataStore')->getByPrefix('importers.');
        $is = $this->is();

        if (count($importers) !== count($is::$allowed)) {
            $importers = array();
            foreach ($is::$allowed as $type) {
                $importers[] = $is->getImporter($type);
            }
        }

        $ret = array();

        foreach ($importers as $importer) {
            /** @var $importer DataStoreEntity */

            // TODO: remove when these are ready
            if (
                $importer['name'] === 'importers.osticket'
            ) {
                continue;
            }
            $ret[] = array(
                'id'          => str_replace('importers.', '', $importer['name']),
                'title'       => $importer->getData('title'),
                'description' => $importer->getData('description'),
                'status'      => $importer->getData('status'),
                'icon'        => $this->getIcon($importer),
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
        $importer = $this->is()->getImporter($id);
        $importer['icon'] = $this->getIcon($importer);
        return $this->createJsonResponse($importer->getData());
    }

    /**
     * @param $id
     * @return BinaryFileResponse|Response
     */
    public function downloadLogAction($id)
    {
        $importer = $this->is()->getImporter($id, $this->container);

        if (($logfile = $importer->getData('logfile')) && is_file($logfile) && is_readable($logfile)) {
            $response = new BinaryFileResponse($logfile, 200);
            $response->headers->set('Content-Type', 'text/plain');
            $response->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                'importlog.txt'
            );
            return $response;
        } else {
            $response = new Response($importer->getData('log'), 200);
            $response->headers->set('Content-Type', 'text/plain');
            return $response;
        }
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

        $is = $this->is();
        $importer = $is->getImporter($id);
        $importer->setData('config', @$data['config']);
        $this->em->flush($importer);

        $importer->getData('status');
        if ($request->get('reset')) {
            $is->cleanup($importer);
        }


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
        $is       = $this->is();
        $importer = $is->getImporter($id);

        try {
            $config = $is->createGeneratorConfig($importer);
            $reader = Generator\GeneratorFactory::createReader($this->getContainer(), $config);

            $res = $this->createJsonResponse(array('result' => $reader->checkConfig()));
        } catch (\Exception $e) {
            $res = $this->createJsonResponse(array('error_message' => $e->getMessage()));
        }

        $is->cleanup($importer);

        return $res;
    }

    /**
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function startAction($id, Request $request)
    {
        $this->is()->startImport($id);
        return $this->getAction($id);
    }

    protected function getIcon($importer)
    {
        $path = defined('DPC_SITE_DOMAIN')
            ? '//' . DPC_SITE_DOMAIN . '/web/images/admin/icons/icon-' . $importer->getData('id') . '.png'
            : (dp_get_config('static_path') ?: '/web') . '/images/admin/icons/icon-' . $importer->getData('id') . '.png';

        return $path;
    }
}
