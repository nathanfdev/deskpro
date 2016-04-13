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

namespace DeskPRO\Bundle\ApiBundle\Controller\MassActions;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDocSection;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\ActionEngine\Services\ApplicatorServiceInterface;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MassActionsController.
 *
 * @ApiModes("all")
 * @ApiDocSection("Mass actions")
 * @Rest\Route("/mass_actions")
 */
class MassActionsController extends BaseController
{
    /**
     * With this endpoint you can apply mass actions for the collection of objects in real-time.
     *
     * @ApiDoc(
     *     description="create new mass action for real-time apply",
     *     output="array",
     *     requirements={
     *         {
     *             "name"="content",
     *             "requirement"="string",
     *             "description"="Type of content, e.g. 'feedback', 'tickets' and so on",
     *             "dataType"="string"
     *         },
     *         {
     *             "name"="ids",
     *             "requirement"="int[]",
     *             "description"="Array of object's IDs for mass actions apply",
     *             "dataType"="array"
     *         },
     *         {
     *             "name"="actions",
     *             "requirement"="array",
     *             "description"="Array with action_name as key and array of action's options as value",
     *             "dataType"="array"
     *         },
     *     },
     *     statusCodes={
     *         200="Your request was successful",
     *         400="Malformed request, refer to manual",
     *     }
     * )
     *
     * @Rest\Post("/{content}", name="mass_action_create", requirements={"content"="\w+"})
     *
     * @param Request $request
     * @param string  $content
     *
     * @return Response
     */
    public function postRealTimeAction(Request $request, $content)
    {
        list($ids, $actions) = $this->checkRequest($request, $content);
        /* @var ApplicatorServiceInterface $applicator */
        $service = $this->getActionApplicatorService($content);
        $service->apply($ids, $actions);
        $service->apply($ids, $actions);

        return Response::HTTP_OK;
    }

    /**
     * Temporary unavailable. With this endpoint you can create queued job for apply mass actions for the collection of objects.
     *
     * @ApiDoc(
     *     description="create new mass action for delayed apply",
     *     output="array",
     *     requirements={
     *         {
     *             "name"="content",
     *             "requirement"="string",
     *             "description"="Type of content, e.g. 'feedback', 'tickets' and so on",
     *             "dataType"="string"
     *         },
     *     },
     *     statusCodes={
     *         200="Your request was successful",
     *         400="Malformed request, refer to manual",
     *     }
     * )
     *
     * @Rest\Post("/{content}/queued", name="queued_mass_action_create")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postQueuedAction(Request $request)
    {
        /* Temporary just put a 404 as a placeholder. https://trello.com/c/aFwqyV5V/661-mass-actions-in-real-time */
        throw $this->createNotFoundException();

        return View::create([], Response::HTTP_CREATED);
    }

    /**
     * @param Request $request
     * @param string  $content
     *
     * @return array
     */
    private function checkRequest(Request $request, $content)
    {
        $ids     = $request->request->get('ids');
        $actions = $request->request->get('actions');
        if (null === $ids || empty($ids)) {
            throw $this->createBadRequestException("You must select $content for mass action apply");
        }
        if (null === $actions || empty($actions)) {
            throw $this->createBadRequestException('You must define one or more actions');
        }

        return [$ids, $actions];
    }

    private function getActionApplicatorService($content)
    {
        $service = $this->container->get('action_engine.'.$content, ContainerInterface::NULL_ON_INVALID_REFERENCE);
        if (null === $service) {
            throw $this->createBadRequestException(
                "You try to apply mass actions for non-existent type of content (`$content`)"
            );
        }

        return $service;
    }
}
