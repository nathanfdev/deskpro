<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
     * @return View
     */
    public function startImportAction(Request $request)
    {
        $activeJob = $this->get('dp.importer.data_service.job')->getActiveJob();
        if ($activeJob) {
            throw $this->createBadRequestException('Import is already in progress');
        }

        $job = new Job(ImporterJobDataService::JOB_TYPE, $this->getSourceConfig($request));

        $jobQueue = $this->getContainer()->getJobQueue();
        $jobQueue->addJob($job);

        return new View($this->wrap(new ImportStatus($job)));
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

        return $this->wrap(new ImportStatus($job));
    }

    /**
     * @param Request $request
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
