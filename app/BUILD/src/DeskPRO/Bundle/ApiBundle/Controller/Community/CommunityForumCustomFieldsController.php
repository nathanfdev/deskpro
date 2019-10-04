<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Community;

use Application\DeskPRO\Entity\CommunityForum;
use Application\DeskPRO\Entity\CommunityForumToCustomDefCommunityTopic;
use Application\DeskPRO\Entity\CustomDefCommunityTopic;
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
 * @Rest\Route("/community_forums/{forumId}/custom_fields", requirements={"forumId"="\d+"})
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
class CommunityForumCustomFieldsController extends BaseController
{
    /**
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
        return View::create($this->wrap($communityForum->getTopicFields()));
    }

    /**
     * @param CommunityForum $communityForum
     * @param Request        $request
     * @ApiDoc(
     *     section="Community Per Forum Custom Fields",
     *     resourceDescription="Operations about community forum custom fields",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     output="array<Application\DeskPRO\Entity\CommunityTopicCustomDef>"
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
            $communityForum->getTopicJunctionFields(),
            function (CommunityForumToCustomDefCommunityTopic $junctionField) {
                return $junctionField->getField()->getId();
            });

        foreach ($orders as $id => $order) {
            /** @var CommunityForumToCustomDefCommunityTopic[] $map */
            if (isset($map[$id])) {
                $map[$id]->setDisplayOrder($order);
            }
        }

        $this->container->get('doctrine.orm.default_entity_manager')->flush();

        return View::create(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param CommunityForum          $communityForum
     * @param CustomDefCommunityTopic $customDefCommunityTopic
     * @ApiDoc(
     *     section="Community Per Forum Custom Fields",
     *     resourceDescription="Operations about community forum custom fields",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     output="Application\DeskPRO\Entity\CommunityTopicCustomDef"
     * )
     *
     * @return View
     * @Rest\Get("/{id}")
     * */
    public function getAction(CommunityForum $communityForum, CustomDefCommunityTopic $customDefCommunityTopic)
    {
        return View::create($this->wrap($customDefCommunityTopic));
    }
}
