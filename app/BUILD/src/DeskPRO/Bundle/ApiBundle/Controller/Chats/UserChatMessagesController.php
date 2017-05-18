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
