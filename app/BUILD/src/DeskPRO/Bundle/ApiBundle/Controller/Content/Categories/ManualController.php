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

namespace DeskPRO\Bundle\ApiBundle\Controller\Content\Categories;

use Application\DeskPRO\Entity\Manual;
use Application\DeskPRO\Entity\ManualTopic;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Form\Type\Content\ManualType;
use FOS\RestBundle\Controller\Annotations as Rest;
use Orb\Util\Arrays;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ManualController.
 *
 * @Feature("manuals")
 * @ApiModes("all")
 * @Rest\Route("/content/manuals")
 * @ApiDoc(target="all", section="Content", output="Application\DeskPRO\Entity\Manual")
 * @ApiDoc(
 *     target="postAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Content\ManualType",
 *      "options"={"method"="POST"},
 *      "name"=""
 *     }
 * )
 */
class ManualController extends AbstractCategoriesController
{
    public static $entity = Manual::class;
    public static $type   = ManualType::class;

    /**
     * @ApiDoc(
     *      description="give a topics tree list of a manual",
     *      statusCodes={
     *          201="Created",
     *          400="Bad Request"
     *      }
     * )
     * @Rest\Get("/tree/{manualId}")
     *
     * @param Request $request
     * @param $manualId
     *
     * @return JsonResponse
     */
    public function getTreeAction(Request $request, $manualId)
    {
        /** @var Manual $manual */
        $manual = $this->getRepository(Manual::class)->find($manualId);

        if (!$manual) {
            throw $this->createNotFoundException();
        }

        $results = $this->getManager()->getConnection()->fetchAllKeyed('
                SELECT t.id, t.title, IFNULL(t.parent_id, 0) as parent_id
                FROM manual_topics t
                WHERE t.manual_id = ?
                ORDER BY display_order ASC
            ', [$manual->getId()], 'id');

        return new JsonResponse(self::objectToArray(Arrays::intoHierarchy($results)));
    }

    /**
     * @ApiDoc(
     *      description="save the tree structure",
     *      statusCodes={
     *          201="Created",
     *          400="Bad Request"
     *      }
     * )
     * @Rest\Put("/tree/{manualId}")
     *
     * @param Request $request
     * @param $manualId
     *
     * @return Response
     */
    public function putTreeAction(Request $request, $manualId)
    {
        /** @var Manual $manual */
        $manual = $this->getRepository(Manual::class)->find($manualId);

        if (!$manual) {
            throw $this->createNotFoundException();
        }

        $tree = $request->request->get('tree');

        $tree = self::fixParentId($tree);

        $tree = Arrays::flattenHierarchy($tree);

        $em   = $this->getManager();
        $repo = $em->getRepository(ManualTopic::class);

        // Might need performance optimisation
        foreach ($manual->getTopics() as $topic) {
            $treeElement = $tree[$topic->getId()];
            if (($topic->getParent() && $topic->getParent()->getId() !== $treeElement['parent_id'])
                || ($treeElement['parent_id'] && !$topic->getParent())
                || ($topic->getParent() && !$treeElement['parent_id'])
            ) {
                $parent = null;
                if ($treeElement['parent_id']) {
                    $parent = $repo->find($treeElement['parent_id']);
                }
                $topic->setParent($parent);
                $em->persist($topic);
            }
            if ($topic->getDisplayOrder() !== $treeElement['display_order']) {
                $topic->setDisplayOrder($treeElement['display_order']);
                $em->persist($topic);
            }
        }

        $em->flush();

        return new Response('', 204);
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
}
