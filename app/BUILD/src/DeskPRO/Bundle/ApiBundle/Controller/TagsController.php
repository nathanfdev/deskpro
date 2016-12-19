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

namespace DeskPRO\Bundle\ApiBundle\Controller;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TagsController.
 *
 * @ApiModes("all")
 */
class TagsController extends BaseController
{
    /**
     * Fetch api tags collection with permissions.
     *
     * @ApiDoc(
     *     section="Tags",
     *     resourceDescription="Operations about tags",
     *     description="get api tags collection",
     *     requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="The id of key",
     *              "dataType"="integer"
     *          }
     *      },
     *     statusCodes={
     *         200="Returned if everything is OK",
     *     },
     *     output="array<DeskPRO\Bundle\AppBundle\ApiTag\Model\Tag>"
     * )
     * @Rest\Get("/api_tags/{id}")
     *
     * @param int $id
     *
     * @return Response
     */
    public function listAction($id)
    {
        $tagsCollector = $this->get('api_tags.tags_collector');
        $tagsCollector->collectTags(true);
        $gatheredTags = $this->get('api_tags.tags_manipulator')->gatherTagsForKey($id);

        return View::create(
            $this->wrap($tagsCollector->getTagsHierarchyForApi($gatheredTags)),
            Response::HTTP_OK
        );
    }

    /**
     * Fetch api tags collection with permissions.
     *
     * @ApiDoc(
     *     section="Tags",
     *     resourceDescription="Operations about tags",
     *     description="get api tags faltten collection",
     *     requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="The id of key",
     *              "dataType"="integer"
     *          }
     *      },
     *     statusCodes={
     *         200="Returned if everything is OK",
     *     },
     *     output="array<string>"
     * )
     * @Rest\Get("/api_tags/{id}/flatten")
     *
     * @param int $id
     *
     * @return Response
     */
    public function flattenListAction($id)
    {
        $gatheredTags = $this->get('api_tags.tags_manipulator')->gatherTagsForKey($id);

        return View::create(
            $this->wrap($gatheredTags),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *     section="Tags",
     *     resourceDescription="Operations about tags",
     *     description="update tags for key",
     *     requirements={
     *         {
     *             "name"="id",
     *             "requirement"="\d+",
     *             "description"="The id of key",
     *             "dataType"="integer"
     *         },
     *         {
     *             "name"="action",
     *             "requirement"="string",
     *             "description"="Tag name",
     *             "dataType"="string"
     *         },
     *         {
     *             "name"="value",
     *             "requirement"="1|0",
     *             "description"="Allow|deny",
     *             "dataType"="boolean"
     *         },
     *     },
     *     statusCodes={
     *         204="Returned if everything is OK",
     *     }
     * )
     * @Rest\Put("/api_tags/{id}", name="api_tags_put", requirements={"id": "\d+"})
     *
     * @param Request $request
     * @param int     $id
     *
     * @return View
     */
    public function putAction(Request $request, $id)
    {
        $tags        = $request->request->get('tags');
        $manipulator = $this->get('api_tags.tags_manipulator');
        $manipulator->updateTags($id, $tags);

        return View::create(null, Response::HTTP_NO_CONTENT);
    }
}
