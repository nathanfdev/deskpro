<?php

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
 * @Rest\Route("/api_tags")
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
     * @Rest\Get("/{id}")
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

        return View::create($this->wrap($tagsCollector->getTagsHierarchyForApi($gatheredTags)));
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
     * @Rest\Get("/{id}/flatten")
     *
     * @param int $id
     *
     * @return Response
     */
    public function flattenListAction($id)
    {
        $gatheredTags = $this->get('api_tags.tags_manipulator')->gatherTagsForKey($id);

        return View::create($this->wrap($gatheredTags));
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
     *     },
     *     noInput=true
     * )
     * @Rest\Put("/{id}", name="api_tags_put", requirements={"id": "\d+"})
     *
     * @param Request $request
     * @param int     $id
     *
     * @return View
     *
     * todo refactor to forms
     */
    public function putAction(Request $request, $id)
    {
        $tags        = $request->request->get('tags');
        $manipulator = $this->get('api_tags.tags_manipulator');
        $manipulator->updateTags($id, $tags);

        return View::create(null, Response::HTTP_NO_CONTENT);
    }
}
