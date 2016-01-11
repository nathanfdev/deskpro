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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\ApiBundle\Controller\Chats;

use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\DataService\Chat\ChatCountCriteria;
use DeskPRO\Bundle\AppBundle\DataService\Chat\ChatSelectCriteria;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ChatsController.
 */
class ChatsController extends BaseController
{
    /**
     * @ApiDoc(
     *      description="Get chats count",
     *      statusCodes={
     *          200="Success",
     *          400="Bad Request",
     *          404="Not Found"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\CountBadge\Count"
     * )
     * @Get("/user_chats/counts", name="api_chats_count")
     */
    public function getCountsAction(Request $request)
    {
        /** @var \DeskPRO\Bundle\AppBundle\DataService\Chat\ChatDataService $dataService */
        $dataService = $this->get('data.chat');

        $params = $this->removeAdditionalParameters($request);
        try {
            $criteria = ChatCountCriteria::fromParameters($params, new OptionsResolver(), [$this->getUser()]);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        $count = $dataService->countChats($criteria);

        return View::create(
            $this->createRepresentation($count),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *      description="Get chats",
     *      statusCodes={
     *          200="Success",
     *          400="Bad Request",
     *          404="Not Found"
     *      }
     * )
     * @Get("/user_chats", name="api_chats")
     */
    public function getAction(Request $request)
    {
        /** @var \DeskPRO\Bundle\AppBundle\DataService\Chat\ChatDataService $dataService */
        $dataService = $this->get('data.chat');

        $params = $this->removeAdditionalParameters($request);
        if (array_key_exists('page', $params)) {
            unset($params['page']);
        }
        if (array_key_exists('count', $params)) {
            unset($params['count']);
        }

        try {
            $criteria = ChatSelectCriteria::fromParameters($params, new OptionsResolver(), [$this->getUser()]);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        $page  = $request->query->get('page', 1);
        $count = $request->query->get('count', 10);
        $chats = $dataService->selectChats($criteria, $page, $count);

        return View::create(
            $this->dataSerialize($chats),
            Response::HTTP_OK
        );
    }
}
