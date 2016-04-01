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
namespace DeskPRO\Bundle\ApiBundle\Controller\JobQueue;

use Application\DeskPRO\Entity\Job;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDocSection;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\OutputEntity;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class JobController.
 *
 * @ApiModes("all")
 * @ApiDocSection("Mass actions")
 * @OutputEntity(Job::class)
 * @Rest\Route("/mass_actions")
 */
class JobController extends CrudController
{
    public static $entity = Job::class;

    /**
     * Please refer to /api/v2/man for more information about mass actions.
     *
     * @ApiDoc(
     *     description="create new job",
     *     output="array",
     *     statusCodes={
     *         200="Your request was successful",
     *         400="Malformed request, refer to manual",
     *     }
     * )
     *
     * @Rest\Post("/", name="mass_action_create")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postAction(Request $request)
    {
        /** @var \Application\DeskPRO\JobQueue\JobQueue $queue */
        $queue = $this->get('job.queue');
        $data  = $request->request->all();

        /** @var Job $job */
        $job = $queue->add($data['jobType'], $data['params']);

        return View::create(['job' => $job->getId()], Response::HTTP_CREATED);
    }
}
