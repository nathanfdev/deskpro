<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Community;

use Application\DeskPRO\Entity\CommunityForum;
use Application\DeskPRO\Entity\CommunityForumToCustomDefCommunityTopic;
use Application\DeskPRO\Entity\CustomDefCommunityTopic;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\CustomFields\CustomFieldType;
use DeskPRO\Bundle\AppBundle\Serializer\Annotation\SerializerView;
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

    /**
     * @param CommunityForum $communityForum
     * @ApiDoc(
     *      description="Create a new custom def",
     *      statusCodes={
     *          201="Returned in case of successful resource creation",
     *          400="We will return this in case your request was malformed",
     *      }
     * )
     * @Rest\Post("")
     *
     * @param Request $request
     *
     * @return View
     */
    public function postAction(CommunityForum $communityForum, Request $request)
    {
        return $this->handleForm($communityForum, new CustomDefCommunityTopic(), $request);
    }

    /**
     * @param CommunityForum          $communityForum
     * @param CustomDefCommunityTopic $customDefCommunityTopic
     *
     * @ApiDoc(
     *      description="Update an existing resource",
     *      tags={"CRUD"="#ffa500"},
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="The id of the resource",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          204="Returned in case of successful resource modify",
     *          400="We will return this in case your request was malformed",
     *      }
     * )
     * @Rest\Put("/{id}", requirements={"id"="\d+"})

     * @param Request $request
     * @SerializerView(serializeNull=true)
     *
     * @return View
     */
    public function putAction(CommunityForum $communityForum, CustomDefCommunityTopic $customDefCommunityTopic, Request $request)
    {
        return $this->handleForm($communityForum, $customDefCommunityTopic, $request);
    }

    /**
     * @param object  $model
     * @param Request $request
     * @param array   $options
     *
     * @throws InvalidFormException
     *
     * @return View
     */
    protected function handleForm(CommunityForum $communityForum, $model, Request $request, array $options = [])
    {
        $isModify = $model && $model->getId();
        $status   = $isModify ? Response::HTTP_NO_CONTENT : Response::HTTP_CREATED;

        $partialUpdate = $isModify;

        $form = $this->createForm(CustomFieldType::class, $model, $options);
        $form->submit($request->request->all(), !$partialUpdate);
        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        // in some cases entity will be created in form
        $model = $form->getData();
        $em    = $this->getManager();
        $em->persist($model);
        $em->flush();

        $view = View::create(!$isModify ? $this->wrap($model) : null, $status);
        $view->setLocation($this->getLocationUrl($communityForum, $model, $request));

        return $view;
    }

    /**
     * @param object  $entity
     * @param Request $request
     * @param array   $params
     *
     * @return string
     */
    protected function getLocationUrl(CommunityForum $communityForum, $entity, Request $request, array $params = [])
    {
        $route = preg_replace('/_post$/', '_get', $request->get('_route'));

        return $this->generateUrl($route,
            array_merge(
                [
                    'forumId' => $communityForum->getId(),
                    'id'      => $entity->getId(),
                ], $params
            )
        );
    }

    /**
     * @ApiDoc(
     *      description="Delete a community field",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="The id of the resource",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Returned if everything is ok and there is no such resource anymore",
     *          404="Well, looks like either resource already deleted either it doesn't exists at all"
     *      }
     * )
     * @Rest\Delete("/{id}", requirements={"id"="\d+"})
     *
     * @param CommunityForum          $communityForum
     * @param CustomDefCommunityTopic $customDefCommunityTopic
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return View
     */
    public function deleteAction(CommunityForum $communityForum, CustomDefCommunityTopic $customDefCommunityTopic)
    {
        $em = $this->getManager();
        $em->remove($customDefCommunityTopic);
        $em->flush();

        return View::create([], Response::HTTP_OK);
    }
}
