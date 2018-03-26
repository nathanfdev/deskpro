<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Chats;

use Application\DeskPRO\Entity\ChatMessage;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudSubController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\UserChat\ChatMessageType;
use DeskPRO\Bundle\AppBundle\UserChat\UserChatEvent;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class UserChatMessagesController.
 *
 * @ApiModes("all")
 * @Rest\Route("/user_chats/{conversationId}/messages")
 * @ApiDoc(target="all", section="Chats", output="Application\DeskPRO\Entity\ChatMessage")
 * @ApiDoc(
 *     target="listAction,countAction",
 *     filters={
 *          {"name"="last_message_id", "dataType"="integer", "pattern"="\d+"},
 *          {"name"="agent_only", "dataType"="integer", "pattern"="\d+"}
 *     }
 * )
 * @ApiDoc(
 *     target="postAction",
 *     parameters = {
 *         { "name" = "content", "dataType" = "string", "format" = "string", "description" = "Message content", "required" = false },
 *         { "name" = "author", "dataType" = "string|integer", "format" = "string|integer", "description" = "Author id or email address", "required" = false },
 *         { "name" = "is_user", "dataType" = "boolean", "format" = "boolean", "description" = "Guest email address", "required" = false },
 *         { "name" = "person_name", "dataType" = "string", "format" = "string", "description" = "Author name", "required" = false },
 *     }
 * )
 * @ApiDoc(
 *     target="postAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\UserChat\ChatMessageType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\ChatMessage",
 *          "person"="Application\DeskPRO\Entity\Person",
 *          "conversation"="Application\DeskPRO\Entity\ChatConversation"
 *      }
 *     }
 * )
 */
class UserChatMessagesController extends CrudSubController
{
    public static $exposeOnly      = ['post', 'list', 'count'];
    public static $entity          = ChatMessage::class;
    public static $type            = ChatMessageType::class;
    public static $parentProperty  = 'conversation';
    public static $parentParameter = 'conversationId';

    /**
     * {@inheritdoc}
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $options = array_merge($options, [
            'conversation' => $this->findParentOr404(),
            'person'       => $this->getUser(),
        ]);

        return parent::handleForm($model, $request, $options);
    }

    /**
     * @param ChatMessage $model
     *
     * {@inheritdoc}
     */
    protected function persistModel($model, FormInterface $form = null)
    {
        if (!$model->getAuthor()->isAgent()) {
            $model->setIsUser(true);
        }

        parent::persistModel($model);

        $conversation = $model->getConversation();
        $this->get('event_dispatcher')->dispatch(UserChatEvent::SEND_MESSAGE, new UserChatEvent($conversation, $model));

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        parent::applyListFilters($qb, $alias, $request);

        $qb->andWhere($alias.'.is_user_hidden = 0');

        $lastMessageId = $request->get('last_message_id');
        if ($lastMessageId) {
            $qb->andWhere($alias.'.id > :last_message_id');
            $qb->setParameter('last_message_id', $lastMessageId);
        }
        if ($request->get('agent_only')) {
            $qb->andWhere($alias.'.is_user = 0');
        }
    }
}
