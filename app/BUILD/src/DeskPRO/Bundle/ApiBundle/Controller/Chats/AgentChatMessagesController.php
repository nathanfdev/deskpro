<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Chats;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiUnstable;
use DeskPRO\Bundle\ApiBundle\Controller\CrudSubController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Entity\AgentChat;
use DeskPRO\Bundle\AppBundle\Entity\AgentChatMessage;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\AgentChat\AgentChatMessageType;
use DeskPRO\Bundle\AppBundle\Form\Type\AgentChat\AgentMarkMessageType;
use DeskPRO\Bundle\AppBundle\Notification\Event\AgentChat\MarkAllMessagesEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\AgentChat\MarkMessageEvent;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AgentChatMessagesController.
 *
 * @ApiModes("all")
 * @Rest\Route("/agent_chats/{parentId}/messages")
 * @ApiDoc(target="all", section="Chats", output="DeskPRO\Bundle\AppBundle\Entity\AgentChatMessage")
 * @ApiDoc(
 *     target="listAction",
 *     filters={
 *          {"name"="search", "dataType"="string"},
 *          {"name"="order", "dataType"="string", "pattern"="date_created"}
 *      }
 * )
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\AgentChat\AgentChatMessageType",
 *      "options"={
 *          "data"="DeskPRO\Bundle\AppBundle\Entity\AgentChatMessage",
 *          "person"="Application\DeskPRO\Entity\Person",
 *          "chat"="DeskPRO\Bundle\AppBundle\Entity\AgentChat"
 *      }
 *     }
 * )
 * @ApiDoc(
 *     target="markMessagesAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\AgentChat\AgentMarkMessageType",
 *      "options"={
 *          "chat"="DeskPRO\Bundle\AppBundle\Entity\AgentChat"
 *      }
 *     }
 * )
 * @ApiUnstable()
 * @Feature("agent_chat")
 */
class AgentChatMessagesController extends CrudSubController
{
    public static $entity         = AgentChatMessage::class;
    public static $type           = AgentChatMessageType::class;
    public static $parentProperty = 'chat';
    public static $sortOptions    = [
        'date_created' => 'date_created',
    ];

    /**
     * Mark message with given id as sent/read.
     *
     * @ApiDoc(
     *      description="mark message as sent/read",
     *      requirements={
     *          {
     *              "name"="ids",
     *              "requirement"="[\d+]",
     *              "dataType"="integer[]",
     *              "description"="Array of ids to update with given status"
     *          },
     *          {
     *              "name"="status",
     *              "requirement"="1|2",
     *              "dataType"="integer",
     *              "description"="Read status. 1 => sent, 2 => read"
     *          },
     *      },
     *      statusCodes={
     *          204="Returned if success",
     *          400={
     *              "Returned if given status was wrong",
     *              "Returned if ids list was wrong formed"
     *          }
     *      }
     * )
     * @Rest\Put("/mark")
     *
     * @param Request $request
     *
     * @return View
     */
    public function markMessagesAction(Request $request)
    {
        $this->denyAccessUnlessGranted(PermissionGroupVoter::MODIFY, $this->getPermissionGroupContext($request));

        $form = $this->createForm(AgentMarkMessageType::class, null, ['chat' => $this->findParentOr404()]);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        /** @var AgentChatMessage[] $messages */
        $messages = $form->get('ids')->getData();
        $status   = $form->get('status')->getData();

        $qb = $this->getManager()->createQueryBuilder();
        $qb
            ->update(static::$entity, 'e')
            ->set('e.status', ':status')
            ->where('e.id IN(:ids)')
            ->setParameter('status', $status)
            ->setParameter('ids', $messages)
        ;

        $qb->getQuery()->execute();

        $dispatcher = $this->get('event_dispatcher');
        foreach ($messages as $message) {
            $dispatcher->dispatch(MarkMessageEvent::EVENT_NAME, new MarkMessageEvent($message->getId(), $status));
        }

        return new View(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Mark message with given id as sent/read.
     *
     * @ApiDoc(
     *      description="mark message as sent/read",
     *      statusCodes={
     *          204="Returned if success",
     *          400={
     *              "Returned if given status was wrong",
     *              "Returned if ids list was wrong formed"
     *          }
     *      },
     *     noInput=true
     * )
     * @Rest\Put("/mark_all")
     *
     * @param Request $request
     *
     * @return View
     */
    public function markAllMessagesAction(Request $request)
    {
        $this->denyAccessUnlessGranted(PermissionGroupVoter::MODIFY, $this->getPermissionGroupContext($request));

        /** @var AgentChat $chat */
        $chat = $this->findParentOr404();
        $qb   = $this->getManager()->createQueryBuilder();
        $qb
            ->update(static::$entity, 'e')
            ->set('e.status', ':status')
            ->where('e.status != :status2')
            ->andWhere('e.person != :user')
            ->orWhere('e.person IS NULL')
            ->setParameter('status', 2)
            ->setParameter('status2', 2)
            ->setParameter('user', $this->getUser())
        ;

        $qb->getQuery()->execute();

        $dispatcher = $this->get('event_dispatcher');
        $dispatcher->dispatch(MarkAllMessagesEvent::EVENT_NAME, new MarkAllMessagesEvent($chat->getId(), 2));

        return new View(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @ApiDoc(
     *     description="get page where this message is",
     *     statusCodes={
     *         204="Returned if success",
     *         400={
     *             "Returned if given status was wrong",
     *             "Returned if ids list was wrong formed"
     *         }
     *     },
     *     output="integer"
     * )
     * @Rest\Get("/{id}/page")
     *
     * @param $id
     * @param $request
     *
     * @return View
     */
    public function findMessagePageAction($id, Request $request)
    {
        $qb = $this->getManager()->createQueryBuilder();
        $qb->from(static::$entity, 'e');
        $this->applyListFilters($qb, 'e', $request);
        $this->applySorting($qb, 'e', $request);
        $qb->andWhere('e.id > :id')->setParameter('id', $id);
        $qb->select('count(e.id) as value');
        $count = $qb->getQuery()->getSingleScalarResult();

        return $this->wrap(ceil($count / static::$listPerPage) ?: 1);
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        parent::applyListFilters($qb, $alias, $request);

        $search = $request->get('search');
        if ($search) {
            $qb->andWhere("$alias.message LIKE :search");
            $qb->setParameter('search', "%$search%");
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $all     = $request->request->all();
        $options = array_merge($options, [
            'chat'   => $this->findParentOr404(),
            'person' => $this->getUser(),
            'blobs'  => isset($all['blobs']) ? $all['blobs'] : [],
        ]);

        return parent::handleForm($model, $request, $options);
    }

    protected function persistModel($model, FormInterface $form = null)
    {
        /** @var AgentChatMessage $model */
        $blobs = $form->getConfig()->getOption('blobs', []);
        $this->get('attachment_helper')->processInlineBlobs($model->getMessage(), $blobs);

        return parent::persistModel($model);
    }
}
