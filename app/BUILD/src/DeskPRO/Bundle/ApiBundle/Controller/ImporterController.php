<?php

namespace DeskPRO\Bundle\ApiBundle\Controller;

use Application\DeskPRO\Entity\Job;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\ImportBundle\DataService\ImporterJobDataService;
use DeskPRO\Bundle\ImportBundle\Form\Type\ImporterSourceType;
use DeskPRO\Bundle\ImportBundle\Serializer\Model\ImportStatus;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class ImporterController.
 *
 * @ApiModes("all")
 * @Rest\Route("/importer")
 * @ApiUserContext("admin")
 */
class ImporterController extends BaseController
{
    /**
     * @ApiDoc(
     *     section="Importer",
     *     description="Start a new import process from an external data source.",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     input="DeskPRO\Bundle\ImportBundle\Form\Type\ImporterSourceType",
     *     output="DeskPRO\Bundle\ImportBundle\Serializer\Model\ImportStatus"
     * )
     *
     * @Rest\Post("/start_import")
     *
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return View
     */
    public function startImportAction(Request $request)
    {
        $activeJob = $this->get('dp.importer.data_service.job')->getActiveJob();
        if ($activeJob) {
            throw $this->createBadRequestException('Import is already in progress');
        }

        $data = $this->getSourceConfig($request);

        try {
            $scriptResolver = $this->container->get('dp.importer.source_script_resolver');
            $sourceScript   = $scriptResolver->getSourceScript($data['type'], $data['options']);

            $sourceScript->testConfig();
        } catch (\Exception $e) {
            throw $this->createBadRequestException($e->getMessage());
        }

        $job = new Job(ImporterJobDataService::JOB_TYPE, $data);

        $jobQueue = $this->getContainer()->getJobQueue();
        $jobQueue->addJob($job);

        return new View($this->wrap(new ImportStatus($job)));
    }

    /**
     * @ApiDoc(
     *     section="Importer",
     *     description="Stop current importer process.",
     *     statusCodes={
     *         204="Returned if everything is ok"
     *     },
     *     noInput=true,
     *     noOutput=true
     * )
     *
     * @Rest\Post("/stop_import")
     *
     * @throws BadRequestHttpException
     *
     * @return View
     */
    public function stopImportAction()
    {
        $activeJob = $this->get('dp.importer.data_service.job')->getActiveJob();
        if (!$activeJob) {
            throw $this->createBadRequestException('No active imports was found');
        }

        $this->getManager()->remove($activeJob);
        $this->getManager()->flush();

        return new View(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @ApiDoc(
     *     section="Importer",
     *     description="Check if an external data source connect options are valid.",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     input="DeskPRO\Bundle\ImportBundle\Form\Type\ImporterSourceType",
     *     output="array"
     * )
     *
     * @Rest\Post("/test_settings")
     *
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return View
     */
    public function testSettingsAction(Request $request)
    {
        $data = $this->getSourceConfig($request);

        try {
            $scriptResolver = $this->container->get('dp.importer.source_script_resolver');
            $sourceScript   = $scriptResolver->getSourceScript($data['type'], $data['options']);

            $sourceScript->testConfig();
        } catch (\Exception $e) {
            throw $this->createBadRequestException($e->getMessage());
        }

        return new View([
            'success' => true,
        ]);
    }

    /**
     * @ApiDoc(
     *     section="Importer",
     *     description="Get progress of current importer process.",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     output="DeskPRO\Bundle\ImportBundle\Serializer\Model\ImportStatus"
     * )
     *
     * @Rest\Get("/status")
     * @Rest\Get("/status/{job}")
     *
     * @param Job $job
     *
     * @throws \Exception
     *
     * @return View
     */
    public function statusAction(Job $job = null)
    {
        if (!$job) {
            $job = $this->get('dp.importer.data_service.job')->getActiveJob();
        }
        if (!$job) {
            throw $this->createNotFoundException();
        }

        return new View($this->wrap(new ImportStatus($job)));
    }

    /**
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return array
     */
    private function getSourceConfig(Request $request)
    {
        $form = $this->createForm(ImporterSourceType::class);
        $form->submit($request->request->all());

        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        return $form->getData();
    }
}
