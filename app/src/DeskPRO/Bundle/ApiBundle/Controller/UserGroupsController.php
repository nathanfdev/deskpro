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
namespace DeskPRO\Bundle\ApiBundle\Controller;

use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class UserGroupsController.
 */
class UserGroupsController extends BaseController
{
    /**
     * @ApiDoc(
     *      description="Get collection of User Groups",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     * @Get("/user_groups", name="api_user_groups")
     */
    public function cgetAction()
    {
        /** @var \DeskPRO\Bundle\AppBundle\DataService\UserGroups\UserGroupsDataService $service */
        $service = $this->get('data.user_groups');

        return View::create(
            $this->dataSerialize($service->loadUserGroupsEnabled()),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *      description="Get a User Group",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     * @Get("/user_groups/{id}", name="api_single_user_group", requirements={"id" = "\d+"})
     */
    public function getUserGroup($id)
    {
        $service = $this->get('data.user_groups');

        return View::create(
            $this->dataSerialize($service->loadSingleUserGroupEnabled($id)),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *      description="Count Users in User Groups",
     *      statusCodes={
     *          200="Success",
     *          400="Bad Request"
     *      }
     * )
     * @Get("/user_groups/counts", name="api_user_group_count_users")
     */
    public function getUsersCountsAction()
    {
        /** @var \DeskPRO\Bundle\AppBundle\DataService\UserGroups\UserGroupsDataService $service */
        $service = $this->get('data.user_groups');

        return View::create(
            $this->createRepresentation($service->countPeopleInUserGroups()),
            Response::HTTP_OK
        );
    }
}
