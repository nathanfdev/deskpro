<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Chats;

use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\CustomDefChat;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\CustomDataHelper;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\DateHelper;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\LabelHelper;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\ListHelper;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\RequestQueryContext;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\UserChat\ChatConversationType;
use DeskPRO\Bundle\AppBundle\Serializer\Annotation\SerializerView;
use DeskPRO\Bundle\AppBundle\UserChat\UserChatEvent;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class UserChatsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/user_chats")
 * @ApiDoc(target="all", section="Chats", output="Application\DeskPRO\Entity\ChatConversation")
 * @ApiDoc(
 *     target="listAction,countAction",
 *     filters={
 *          {"name"="status", "description"="status filter", "dataType"="string", "pattern"="open|ended"},
 *          {"name"="date_created", "dataType"="string", "pattern"="Y-m-d:Y-m-d"},
 *          {"name"="date_period", "dataType"="string", "pattern"="today|yesterday|etc"},
 *          {"name"="person", "dataType"="integer", "pattern"="\d+"},
 *          {"name"="agent", "dataType"="integer", "pattern"="\d+"},
 *          {"name"="department", "dataType"="integer", "pattern"="\d+"},
 *          {"name"="label", "description"="labels filter option", "dataType"="array", "pattern"="[\w+,]+"},
 *          {
 *              "name"="chat_field.{id}",
 *              "description"="
 *                  Custom chat field filter. To filter by a custom field with ID=1 you need to add
 *                  ?chat_field.1=value to the query string",
 *              "dataType"="string",
 *              "pattern"="\d+|\w"
 *          }
 *     }
 * )
 * @ApiDoc(
 *     target="countAction",
 *     filters={
 *          {"name"="group_by", "pattern"="date_created|date_period|agent|department", "description"="how to group counts", "dataType"="boolean"}
 *     }
 * )
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\UserChat\ChatConversationType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\ChatConversation"
 *      }
 *     }
 * )
 */
class UserChatsController extends CrudController
{
    public static $entity      = ChatConversation::class;
    public static $type        = ChatConversationType::class;
    public static $sortOptions = [
        'date_created' => 'date_created',
        'agent'        => ['join' => 'agent', 'as' => 'a', 'sort' => 'a.id'],
    ];

    /**
     * Get data for export to CSV.
     *
     * @Rest\Get("/csv")
     * @SerializerView(mapping={
     *     "Application\DeskPRO\Entity\ChatConversation": "DeskPRO\Bundle\AppBundle\Serializer\Model\Chats\ChatCsv"
     * })
     *
     * @param Request $request
     *
     * @return \FOS\RestBundle\View\View
     */
    public function csvAction(Request $request)
    {
        return $this->listAction($request);
    }

    /**
     * Assign a chat conversation to an agent.
     *
     * @ApiDoc(
     *     description="Assign chat to agent",
     *     requirements={
     *         {
     *             "name"="id",
     *             "requirement"="\d+",
     *             "dataType"="integer",
     *         },
     *         {
     *             "name"="agentId",
     *             "requirement"="\d+",
     *             "dataType"="integer",
     *         }
     *     },
     *     statusCodes={
     *        200="Returned if successful request",
     *        400="Returned if you filter set was malformed"
     *     },
     *     noInput=true
     * )
     * @Rest\Put("/{id}/assign/{agentId}", requirements={"id"="\d+", "agentId"="\d+"})
     *
     * @param Request $request
     * @param int     $id
     * @param int     $agentId
     *
     * @return View
     */
    public function assignAction(Request $request, $id, $agentId)
    {
        $conversation = $this->findEntity($id, $request);
        $agent        = $this->getManager()->getRepository(Person::class)->getAgent($agentId);
        if (!$agent) {
            throw $this->createNotFoundException('Agent not found');
        }

        $conversation->setAgent($agent);
        $conversation->addParticipant($agent);

        $em = $this->getManager();
        $em->persist($conversation);
        $em->flush();

        return View::create($this->wrap($conversation), Response::HTTP_OK);
    }

    /**
     * End a chat conversation.
     *
     * @ApiDoc(
     *     description="End a chat conversation",
     *     requirements={
     *         {
     *             "name"="id",
     *             "requirement"="\d+",
     *             "dataType"="integer",
     *         }
     *     },
     *     statusCodes={
     *        200="Returned if successful request",
     *        400="Returned if you filter set was malformed"
     *     },
     *     noInput=true
     * )
     * @Rest\Put("/{id}/end", requirements={"id"="\d+"})
     *
     * @param Request $request
     * @param int     $id
     *
     * @return View
     */
    public function endAction(Request $request, $id)
    {
        $conversation = $this->findEntity($id, $request);
        $conversation->setStatus(ChatConversation::STATUS_ENDED);

        $em = $this->getManager();
        $em->persist($conversation);
        $em->flush();

        $this->get('event_dispatcher')->dispatch(UserChatEvent::ENDED, new UserChatEvent($conversation, [], ['chat_ended']));

        return View::create(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $context = new RequestQueryContext($qb, $alias, $request);

        DateHelper::applyDateRangeFilter($context, 'date_created', 'created_from', 'created_to');
        DateHelper::applyDatePeriodFilter($context, 'date_created', 'date_period');
        ListHelper::applyInListFilter($context, 'department');
        CustomDataHelper::applyCustomDataFilters($context, 'chat', CustomDefChat::class);
        LabelHelper::applyLabelFilters($context, static::$entity);

        $agent = $request->get('agent');
        if ($agent) {
            if ($agent === 'me') {
                $agent = $this->getUser()->getId();
            }

            $qb->andWhere("$alias.agent = :agent");
            $qb->setParameter('agent', $agent);
        }

        $person = $request->get('person');
        if ($person) {
            $qb->andWhere("$alias.person = :person");
            $qb->setParameter('person', $person);
        }

        $status = $request->get('status');
        if (!empty($status)) {
            $status = (array) $status;

            $qb->andWhere("$alias.status IN (:status)");
            $qb->setParameter('status', $status);
        }

        // only user chats
        $qb->andWhere("$alias.is_agent = 0");
    }

    /**
     * {@inheritdoc}
     */
    protected function findEntity($id, Request $request)
    {
        /** @var ChatConversation $entity */
        $entity = parent::findEntity($id, $request);
        if ($entity->isAgentChat()) {
            throw $this->createNotFoundException();
        }

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListGroupBy(QueryBuilder $qb, $alias, $groupBy, Request $request)
    {
        switch ($groupBy) {
            case 'date_created':
                $qb
                    ->addSelect("DATE($alias.date_created) as group_name")
                    ->addSelect("DATE($alias.date_created) as title")
                    ->groupBy('group_name');

                break;
            case 'date_period':
                $context = new RequestQueryContext($qb, $alias, $request);
                DateHelper::applyDatePeriodGroupBy($context, 'date_created');

                break;
            case 'agent':
                $qb
                    ->addSelect('g.id as group_name')
                    ->addSelect('g.name as title')
                    ->leftJoin("$alias.agent", 'g')
                    ->groupBy('group_name');

                break;
            case 'department':
                $qb
                    ->addSelect('g.id as group_name')
                    ->addSelect('g.title as title')
                    ->leftJoin("$alias.department", 'g')
                    ->groupBy('group_name');

                break;
        }
    }

    /**
     * @param ChatConversation $model
     *
     * {@inheritdoc}
     */
    protected function persistModel($model, FormInterface $form = null)
    {
        parent::persistModel($model);
        $this->get('event_dispatcher')->dispatch(UserChatEvent::STARTED, new UserChatEvent($model));

        return $model;
    }
}
