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
namespace DeskPRO\Bundle\ApiBundle\Controller\Chats;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\DataService\Chat\ChatCountCriteria;
use DeskPRO\Bundle\AppBundle\DataService\Chat\ChatSelectCriteria;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\Annotations as FOS;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ChatsController.
 *
 * @ApiModes("all")
 */
class ChatsController extends BaseController
{
    /**
     * Get count of user chats.
     *
     * @ApiDoc(
     *     section="Chats",
     *     resourceDescription="Operations about user`s chats",
     *     description="get chats count",
     *     statusCodes={
     *         200="Will returned with count list",
     *         400="You have malformed filters in request",
     *     },
     *     filters={
     *          {"name"="date_created", "dataType"="string", "pattern"="Y-m-d:Y-m-d"},
     *          {"name"="date_period", "dataType"="string", "pattern"="today|yesterday|etc"},
     *          {"name"="agent", "dataType"="integer", "pattern"="\d+"},
     *          {"name"="department", "dataType"="integer", "pattern"="\d+"}
     *     },
     *     output="DeskPRO\Bundle\AppBundle\CountBadge\Count"
     * )
     * @Annotations\Get("/user_chats/counts", name="api_chats_count")
     * @FOS\View(serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     *
     * @return View
     */
    public function getCountsAction(Request $request)
    {
        /** @var \DeskPRO\Bundle\AppBundle\DataService\Chat\ChatDataService $dataService */
        $dataService = $this->get('data.chat');

        $params = $this->removeAdditionalParameters($request);
        try {
            /** @var ChatCountCriteria $criteria */
            $criteria = ChatCountCriteria::fromParameters($params, new OptionsResolver(), [$this->getUser()]);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        $count = $dataService->countChats($criteria);

        return View::create(
            $this->wrap($count),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *     section="Chats",
     *     resourceDescription="Operations about user`s chats",
     *     description="Get chats",
     *     statusCodes={
     *         200="Returned if request was successful",
     *         400="Returned if filter set was malformed",
     *     },
     *     filters={
     *          {"name"="page", "dataType"="integer", "pattern"="\d+", "description"="which page to display"},
     *          {"name"="count", "dataType"="integer", "pattern"="\d+", "description"="per page chats quantity"},
     *          {"name"="date_created", "dataType"="string", "pattern"="Y-m-d:Y-m-d"},
     *          {"name"="date_period", "dataType"="string", "pattern"="today|yesterday|etc"},
     *          {"name"="agent", "dataType"="integer", "pattern"="\d+"},
     *          {"name"="department", "dataType"="integer", "pattern"="\d+"}
     *     },
     *     output="array<Application\DeskPRO\Entity\ChatConversation>"
     * )
     * @Annotations\Get("/user_chats", name="api_chats")
     *
     * @param Request $request
     *
     * @return View
     */
    public function listAction(Request $request)
    {
        /** @var \DeskPRO\Bundle\AppBundle\DataService\Chat\ChatDataService $dataService */
        $dataService = $this->get('data.chat');

        $params = $this->removeAdditionalParameters($request);

        try {
            /** @var ChatSelectCriteria $criteria */
            $criteria = ChatSelectCriteria::fromParameters($params, new OptionsResolver(), [$this->getUser()]);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        $page  = $request->query->get('page', 1);
        $count = $request->query->get('count', 10);
        $chats = $dataService->selectChats($criteria, $page, $count);

        return View::create(
            $this->wrap($chats),
            Response::HTTP_OK
        );
    }
}
