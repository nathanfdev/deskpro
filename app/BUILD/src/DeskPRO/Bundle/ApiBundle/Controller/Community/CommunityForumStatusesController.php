<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Community;

use Application\DeskPRO\Entity\CommunityForum;
use Application\DeskPRO\Entity\CommunityForumToStatus;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Orb\Util\Arrays;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * API access to community forum's custom fields.
 *
 * @ApiModes("all")
 * @Rest\Route("/community_forums/{forumId}/statuses", requirements={"forumId"="\d+"})
 * @ParamConverter("communityForum", options={"mapping": {"forumId": "id"}})
 * @ApiDoc(target="all", section="Organizations", output="Application\DeskPRO\Entity\CustomDefCommunityTopic")
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\CustomFields\CustomFieldType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\CustomDefCommunityTopic"
 *      }
 *     }
 * )
 */
class CommunityForumStatusesController extends BaseController
{
    /**
     * Get a list of community statuses.
     *
     * @param CommunityForum $communityForum
     * @ApiDoc(
     *     section="Community Per Forum Custom Fields",
     *     resourceDescription="Operations about community forum custom fields",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     output="array<Application\DeskPRO\Entity\CommunityTopicCustomDef>"
     * )
     *
     * @return View
     * @Rest\Get("/")
     */
    public function listAction(CommunityForum $communityForum)
    {
        return View::create($this->wrap($communityForum->getTopicStatuses()));
    }

    /**
     * Post new display orders for statuses.
     *
     * @param CommunityForum $communityForum
     * @param Request        $request
     * @ApiDoc(
     *     section="Community Per Forum Statuses",
     *     resourceDescription="Operations about community forum statuses: display orders",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     input="array",
     *     output="null"
     * )
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return View
     * @Rest\Post("/display_orders")
     */
    public function updateDisplayOrdersAction(CommunityForum $communityForum, Request $request)
    {
        $orders = $request->request->get('display_orders');
        $map    = Arrays::rekey(
            $communityForum->getTopicStatuses(),
            function (CommunityForumToStatus $junctionStatus) {
                return $junctionStatus->getStatus()->getId();
            });

        foreach ($orders as $id => $order) {
            /** @var CommunityForumToStatus[] $map */
            if (isset($map[$id])) {
                $map[$id]->setDisplayOrder($order);
            }
        }

        $this->container->get('doctrine.orm.default_entity_manager')->flush();

        return View::create(null, Response::HTTP_NO_CONTENT);
    }
}
