<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Content\Categories;

use Application\DeskPRO\Entity\Guide;
use Application\DeskPRO\Entity\Topic;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Form\Type\Content\GuideType;
use FOS\RestBundle\Controller\Annotations as Rest;
use Orb\Util\Arrays;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class GuideController.
 *
 * @Feature("guides")
 * @ApiModes("all")
 * @Rest\Route("/guides")
 * @ApiDoc(target="all", section="Content", output="Application\DeskPRO\Entity\Guide")
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Content\GuideType",
 *      "options"={"data"="Application\DeskPRO\Entity\Guide"}
 *     }
 * )
 */
class GuideController extends AbstractCategoriesController
{
    public static $entity = Guide::class;
    public static $type   = GuideType::class;

    /**
     * @ApiDoc(
     *     description="give a topics tree list of a guide",
     *     statusCodes={
     *         201="Created",
     *         400="Bad Request"
     *     },
     *     output="array"
     * )
     * @Rest\Get("/{guideId}/tree")
     * @ParamConverter("guide", class="DeskPRO:Guide", options={"id" = "guideId"})
     *
     * @param Guide $guide
     *
     * @return JsonResponse
     */
    public function getTreeAction(Guide $guide)
    {
        $results = $this->getManager()->getConnection()->fetchAllKeyed('
                SELECT t.id, t.title, IFNULL(t.parent_id, 0) as parent_id, status, hidden_status
                FROM topics t
                WHERE t.guide_id = ?
                ORDER BY display_order ASC
            ', [$guide->getId()], 'id');

        return new JsonResponse(self::objectToArray(Arrays::intoHierarchy($results)));
    }

    /**
     * @ApiDoc(
     *     description="save the tree structure",
     *     statusCodes={
     *         201="Created",
     *         400="Bad Request"
     *     },
     *     parameters={
     *         {"name"="tree", "description"="", "dataType"="array", "required"=true}
     *     }
     * )
     * @Rest\Put("/{guideId}/tree")
     * @ParamConverter("guide", class="DeskPRO:Guide", options={"id" = "guideId"})
     *
     * @param Request $request
     * @param Guide   $guide
     *
     * @return Response
     */
    public function putTreeAction(Request $request, Guide $guide)
    {
        $tree = $request->request->get('tree');

        $tree = self::fixParentId($tree);

        $tree = Arrays::flattenHierarchy($tree);

        $em = $this->getManager();

        foreach ($tree as $topic) {
            $q = $em->createQuery('
              UPDATE '.Topic::class.' t 
              SET 
                t.display_order = :display_order, t.parent = :parent_id WHERE t.id = :id');
            $q->execute([
                'display_order' => $topic['display_order'],
                'parent_id'     => $topic['parent_id'] ? $topic['parent_id'] : null,
                'id'            => $topic['id'],
            ]);
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    private static function objectToArray($topics)
    {
        foreach ($topics as &$topic) {
            if (!empty($topic['children'])) {
                $topic['children'] = self::objectToArray($topic['children']);
            }
        }

        return array_values($topics);
    }

    private static function fixParentId($topics, $parentId = 0)
    {
        $displayOrder = 1;
        foreach ($topics as &$topic) {
            if (!empty($topic['children'])) {
                $topic['children'] = self::fixParentId($topic['children'], $topic['id']);
            }
            $topic['parent_id']     = $parentId;
            $topic['display_order'] = $displayOrder++;
        }

        return $topics;
    }

    /**
     * @ApiDoc(
     *      description="export a full json of the guide",
     *      statusCodes={
     *          201="Created",
     *          400="Bad Request"
     *      },
     *     output="Application\DeskPRO\Entity\Guide"
     * )
     * @Rest\Get("/{guideId}/export")
     * @Rest\View(serializerGroups={"list", "details"})
     * @ParamConverter("guide", class="DeskPRO:Guide", options={"id" = "guideId"})
     *
     * @param Guide $guide
     *
     * @return \DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper
     */
    public function exportAction(Guide $guide)
    {
        return $this->wrap($guide);
    }
}
