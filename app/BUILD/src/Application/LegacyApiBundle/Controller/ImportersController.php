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

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\Entity;
use Application\DeskPRO\EntityRepository;
use Application\LegacyApiBundle\PermissionStrategy\UserTypePermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class ImportersController.
 *
 * @ApiModes("all")
 */
class ImportersController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritdoc}
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
        /** @var EntityRepository\DataStore $repository */
        $repository = $this->em->getRepository('DeskPRO:DataStore');
        $importers  = $repository->getByPrefix('importers.');

        $is = $this->get('deskpro.import');

        if (count($importers) !== count($is::$allowed)) {
            $importers = array();
            foreach ($is::$allowed as $type) {
                $importers[] = $is->getImporter($type);
            }
        }

        $ret = array();

        foreach ($importers as $importer) {
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
     * @param string $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAction($id)
    {
        $importer     = $this->get('deskpro.import')->getImporter($id);
        $data         = $importer->getData();
        $data['icon'] = $this->getIcon($importer);

        return $this->createJsonResponse($data);
    }

    /**
     * @param string $id
     *
     * @return BinaryFileResponse|Response
     */
    public function downloadLogAction($id)
    {
        $importer = $this->get('deskpro.import')->getImporter($id);
        $logfile  = $importer->getData('logfile');

        if ($logfile && is_file($logfile) && is_readable($logfile)) {
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
     * @param string  $id
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function saveAction($id, Request $request)
    {
        if (!$data = json_decode($request->getContent(), 1)) {
            throw new BadRequestHttpException();
        }

        $is       = $this->get('deskpro.import');
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
     * test if import ready to start.
     *
     * @param string $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function testAction($id)
    {
        $is       = $this->get('deskpro.import');
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
     * @param string $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function startAction($id)
    {
        $this->get('deskpro.import')->startImport($id);

        return $this->getAction($id);
    }

    /**
     * @param Entity\DataStore $importer
     *
     * @return string
     */
    protected function getIcon(Entity\DataStore $importer)
    {
        if (defined('DPC_SITE_DOMAIN')) {
            return '//'.DPC_SITE_DOMAIN.'/web/images/admin/icons/icon-'.$importer->getData('id').'.png';
        }

        return '/web/images/admin/icons/icon-'.$importer->getData('id').'.png';
    }
}
