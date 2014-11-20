<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage ApiBundle
 */

namespace Application\ApiBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Searcher\ChatConversationSearch;
use Orb\Util\Numbers;

/**
* @SWG\Resource(
* 	resourcePath="/chats",
* 	description="Operations about Chats",
* 	basePath="/api"
* )
*/
class ChatController extends AbstractController
{
    // todo: better search - ordering, more criteria

    /**
     * @SWG\Api(
     * 	path="/chats",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Search for chats matching criteria",
     * 		notes="Returns list of chats that matched.",
     *		type="array",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="agent_id[]",
     *				description="Requires chat to be assigned to the specified agent ID.",
     *				paramType="query",
     *				required=false,
     *				type="string"
     *			),
     *			@SWG\Parameter(
     *				name="department_id[]",
     *				description="Requires chat to be in the specified department ID.",
     *				paramType="query",
     *				required=false,
     *				type="string"
     *			),
     *			@SWG\Parameter(
     *				name="label[]",
     *				description="Requires chat to be have the specified label.",
     *				paramType="query",
     *				required=false,
     *				type="string"
     *			),
     *			@SWG\Parameter(
     *				name="person_id[]",
     *				description="Requires chat to be created by the specified person ID.",
     *				paramType="query",
     *				required=false,
     *				type="string"
     *			),
     *			@SWG\Parameter(
     *				name="status[]",
     *				description="Requires chat to be in the specified status. Possible values are ended and open.",
     *				paramType="query",
     *				required=false,
     *				type="string"
     *			),
     *			@SWG\Parameter(
     *				name="order",
     *				description="Order of the results. Defaults to newest chats. This parameter is not used used yet.",
     *				paramType="query",
     *				required=false,
     *				type="string"
     *			),
     *			@SWG\Parameter(
     *				name="cache_id",
     *				description="If provided, cached results from this result set are used. If it cannot be found or used, the other constraints provided will be used to create a new result set.",
     *				paramType="query",
     *				required=false,
     *				type="string"
     *			),
     *			@SWG\Parameter(
     *				name="page",
     *				description="The page number of the results to fetch.",
     *				paramType="query",
     *				required=false,
     *				type="string"
     *			)
     *		)
     * 	)
     * )
     */
    public function searchAction()
    {
        $search_map = array(
            'agent_id'      => ChatConversationSearch::TERM_AGENT_ID,
            'department_id' => ChatConversationSearch::TERM_DEPARTMENT_ID,
            'label'         => ChatConversationSearch::TERM_LABEL,
            'person_id'     => ChatConversationSearch::TERM_PERSON_ID,
            'status'        => ChatConversationSearch::TERM_DATE_CREATED,
        );

        $terms = array();

        foreach ($search_map as $input => $search_key) {
            $value = $this->in->getCleanValueArray($input, 'raw', 'discard');
            if ($value) {
                $terms[] = array('type' => $search_key, 'op' => 'contains', 'options' => $value);
            }
        }

        $date_created_start = $this->in->getUint('date_created_start');
        $date_created_end   = $this->in->getUint('date_created_end');
        if ($date_created_end) {
            $terms[] = array('type' => ChatConversationSearch::TERM_DATE_CREATED, 'op' => 'between', 'options' => array(
                'date1' => $date_created_start,
                'date2' => $date_created_end,
            ));
        } elseif ($date_created_start) {
            $terms[] = array('type' => ChatConversationSearch::TERM_DATE_CREATED, 'op' => 'between', 'options' => array(
                'date1' => $date_created_start,
            ));
        }

        /*if ($this->in->checkIsset('order')) {
            $order_by = $this->in->getString('order');
        } else {
            $order_by = 'chat_conversation.id:desc';
        }*/

        $order_by = 'chat_conversations.id:desc';

        $extra = array();
        if ($order_by !== null) {
            $extra['order_by'] = $order_by;
        }

        $result_cache = $this->getApiSearchResult('chat', $terms, $extra, $this->in->getUint('cache_id'), new ChatConversationSearch());

        $page = $this->in->getUint('page');
        if (!$page) {
            $page = 1;
        }

        $per_page = Numbers::bound($this->in->getUint('per_page') ?: 25, 1, 250);

        $person_ids = $result_cache->results;

        $page_ids = \Orb\Util\Arrays::getPageChunk($person_ids, $page, $per_page);
        $chats    = App::getEntityRepository('DeskPRO:ChatConversation')->getByIds($page_ids, true);

        return $this->createApiResponse(array(
            'page'     => $page,
            'per_page' => $per_page,
            'total'    => count($person_ids),
            'cache_id' => $result_cache->id,
            'chats'    => $this->getApiData($chats),
        ));
    }

    /**
     * @SWG\Api(
     * 	path="/chats/{chat_id}",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Gets a chat by chat ID.",
     * 		notes="Information about the chat by chat ID.",
     *		type="Chat",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="chat_id",
     *				description="ID of the chat that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Chat not found")
     * 	)
     * )
     */
    public function getChatAction($chat_id)
    {
        $chat = $this->_getChatOr404($chat_id);

        return $this->createApiResponse(array('chat' => $chat->toApiData()));
    }

    /**
     * @SWG\Api(
     * 	path="/chats/{chat_id}",
     * 	@SWG\Operation(
     * 		method="POST",
     * 		summary="Updates a chat by chat ID.",
     *		type="Chat",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="chat_id",
     *				description="ID of the chat that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="department_id",
     *				description="Department the chat is in.",
     *				paramType="query",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Chat not found")
     * 	)
     * )
     */
    public function postChatAction($chat_id)
    {
        $chat = $this->_getChatOr404($chat_id);

        $chat_manager = $this->container->getSystemObject('user_chat_manager');

        if ($this->in->checkIsset('department_id')) {
            $dep           = null;
            $department_id = $this->in->getUint('department_id');
            if ($department_id) {
                $dep = $this->em->find('DeskPRO:Department', $department_id);
            }
            $chat_manager->setDepartment($chat, $dep, $this->person);
        }

        $this->db->beginTransaction();

        try {
            $this->em->persist($chat);
            $this->em->flush();

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }

        return $this->createSuccessResponse();
    }

    /**
     * @SWG\Api(
     * 	path="/chats/{chat_id}/leave",
     * 	@SWG\Operation(
     * 		method="POST",
     * 		summary="Leaves a chat by chat ID.",
     *		type="Chat",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="chat_id",
     *				description="ID of the chat that needs to be left.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="action",
     *				description="If the chat is open, an additional action to take. Options are unassign or end.",
     *				paramType="query",
     *				required=true,
     *				type="string"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Chat not found")
     * 	)
     * )
     */
    public function leaveChatAction($chat_id)
    {
        $chat = $this->_getChatOr404($chat_id);

        $chat_manager = $this->container->getSystemObject('user_chat_manager');
        $chat_manager->personLeft($chat, $this->person);

        /** @var $chat_manager \Application\DeskPRO\Chat\UserChat\UserChatManager */
        if ($chat->status == 'open') {
            switch ($this->in->getString('action')) {
                case 'unassign':
                    if ($chat->agent && $chat->agent->getId() == $this->person->getId()) {
                        $chat_manager->unassignAgent($chat);
                    }
                    break;

                case 'end':
                    $chat_manager->endChat($chat, $this->person);
                    break;
            }
        }

        return $this->createSuccessResponse();
    }

    /**
     * @SWG\Api(
     * 	path="/chats/{chat_id}/end",
     * 	@SWG\Operation(
     * 		method="POST",
     * 		summary="Ends a chat by chat ID.",
     *		type="Chat",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="chat_id",
     *				description="ID of the chat that needs to be ended.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Chat not found")
     * 	)
     * )
     */
    public function endChatAction($chat_id)
    {
        $chat = $this->_getChatOr404($chat_id);

        $chat_manager = $this->container->getSystemObject('user_chat_manager');
        $chat_manager->endChat($chat, $this->person);

        return $this->createSuccessResponse();
    }

    /**
     * @SWG\Api(
     * 	path="/chats/{chat_id}/messages",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Gets all messages in a chat by chat ID.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="chat_id",
     *				description="ID of the chat that needs to be ended.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Chat not found")
     * 	)
     * )
     */
    public function getMessagesAction($chat_id)
    {
        $chat = $this->_getChatOr404($chat_id);

        return $this->createApiResponse(array('messages' => $this->getApiData($chat->messages)));
    }

    /**
     * @SWG\Api(
     * 	path="/chats/{chat_id}/messages",
     * 	@SWG\Operation(
     * 		method="POST",
     * 		summary="Replies to a chat as the API user",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="chat_id",
     *				description="ID of the chat that needs to be ended.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="message",
     *				description="Message reply text.",
     *				paramType="query",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Chat not found")
     * 	)
     * )
     */
    public function newMessageAction($chat_id)
    {
        $chat = $this->_getChatOr404($chat_id);

        $text = $this->in->getString('message');
        if ($text === '') {
            return $this->createApiErrorResponse('required_field', "message cannot be empty");
        }

        /** @var $chat_manager \Application\DeskPRO\Chat\UserChat\UserChatManager */
        $chat_manager = $this->container->getSystemObject('user_chat_manager');
        $message      = $chat_manager->addMessage($chat, $this->person, $text);

        return $this->createApiCreateResponse(
            array('message_id' => $message->id),
            $this->generateUrl('api_chats_chat_message', array('chat_id' => $chat->id, 'message_id' => $message->id), true)
        );
    }

    /**
     * @SWG\Api(
     * 	path="/chats/{chat_id}/participants",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Gets all participants in a chat by chat ID.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="chat_id",
     *				description="ID of the chat that needs to be ended.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Chat not found")
     * 	)
     * )
     */
    public function getParticipantsAction($chat_id)
    {
        $chat = $this->_getChatOr404($chat_id);

        return $this->createApiResponse(array('participants' => $this->getApiData($chat->participants)));
    }

    /**
     * @SWG\Api(
     * 	path="/chats/{chat_id}/participants",
     * 	@SWG\Operation(
     * 		method="POST",
     * 		summary="Adds a participants in a chat by chat ID and person ID.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="chat_id",
     *				description="ID of the chat.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="person_id",
     *				description="ID of the person that needs to be added to the Chat.",
     *				paramType="query",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Chat not found")
     * 	)
     * )
     */
    public function postParticipantsAction($chat_id)
    {
        $chat = $this->_getChatOr404($chat_id);

        $person = $this->em->find('DeskPRO:Person', $this->in->getUint('person_id'));
        if (!$person) {
            return $this->createApiErrorResponse('not_found', 'Person not found');
        }

        if (!$chat->hasParticipant($person)) {
            $chat->addParticipant($person);
            $this->em->persist($chat);
            $this->em->flush();
        }

        return $this->createApiCreateResponse(
            array('id' => $person->id),
            $this->generateUrl('api_chats_chat_participant', array('chat' => $chat->id, 'person_id' => $person->id), true)
        );
    }

    /**
     * @SWG\Api(
     * 	path="/chats/{chat_id}/participants/{person_id}",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Determines if a person is participating in a chat.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="chat_id",
     *				description="ID of the chat that needs to be checked.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="person_id",
     *				description="ID of the person that needs to be added to be checked.",
     *				paramType="query",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Chat not found")
     * 	)
     * )
     */
    public function getParticipantAction($chat_id, $person_id)
    {
        $chat   = $this->_getChatOr404($chat_id);
        $person = $this->em->find('DeskPRO:Person', $person_id);

        if (!$person || !$chat->hasParticipant($person)) {
            return $this->createApiResponse(array('exists' => false));
        }

        return $this->createApiResponse(array('exists' => true));
    }

    /**
     * @SWG\Api(
     * 	path="/chats/{chat_id}/participants/{person_id}",
     * 	@SWG\Operation(
     * 		method="DELETE",
     * 		summary="Removes a participant from a chat",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="chat_id",
     *				description="ID of the chat that needs to be checked.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="person_id",
     *				description="ID of the person that needs to be added to be removed.",
     *				paramType="query",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Chat not found")
     * 	)
     * )
     */
    public function deleteParticipantAction($chat_id, $person_id)
    {
        $chat   = $this->_getChatOr404($chat_id);
        $person = $this->em->find('DeskPRO:Person', $person_id);

        if (!$person) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        $chat->removeParticipant($person);
        $this->em->persist($chat);
        $this->em->flush();

        return $this->createSuccessResponse();
    }

    /**
     * @SWG\Api(
     * 	path="/chats/{chat_id}/labels",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Gets the labels for a chat",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="chat_id",
     *				description="ID of the chat that needs to be checked.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Chat not found")
     * 	)
     * )
     */
    public function getChatLabelsAction($chat_id)
    {
        $chat = $this->_getChatOr404($chat_id);

        return $this->createApiResponse(array('labels' => $this->getApiData($chat->labels)));
    }

    /**
     * @SWG\Api(
     * 	path="/chats/{chat_id}/labels",
     * 	@SWG\Operation(
     * 		method="POST",
     * 		summary="Adds a label to a chat.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="chat_id",
     *				description="ID of the chat that needs to be checked.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="label",
     *				description="Label to add.",
     *				paramType="query",
     *				required=true,
     *				type="string"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Chat not found")
     * 	)
     * )
     */
    public function postChatLabelsAction($chat_id)
    {
        $chat  = $this->_getChatOr404($chat_id);
        $label = $this->in->getString('label');

        if ($label === '') {
            return $this->createApiErrorResponse('required_field', "Field 'label' missing or empty");
        }

        $chat->getLabelManager()->addLabel($label);
        $this->em->persist($chat);
        $this->em->flush();

        return $this->createApiCreateResponse(
            array('label' => $label),
            $this->generateUrl('api_chats_chat_label', array('chat_id' => $chat->id, 'label' => $label), true)
        );
    }

    /**
     * @SWG\Api(
     * 	path="/chats/{chat_id}/labels/{label}",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Determines if the chat has the label.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="chat_id",
     *				description="ID of the chat that needs to be checked.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="label",
     *				description="Label to check.",
     *				paramType="path",
     *				required=true,
     *				type="string"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Chat not found")
     * 	)
     * )
     */
    public function getChatLabelAction($chat_id, $label)
    {
        $chat = $this->_getChatOr404($chat_id);

        if ($chat->getLabelManager()->hasLabel($label)) {
            return $this->createApiResponse(array('exists' => true));
        } else {
            return $this->createApiResponse(array('exists' => false));
        }
    }

    /**
     * @SWG\Api(
     * 	path="/chats/{chat_id}/labels/{label}",
     * 	@SWG\Operation(
     * 		method="DELETE",
     * 		summary="Removes a label from a chat.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="chat_id",
     *				description="ID of the chat that needs to be checked.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="label",
     *				description="Label that needs to be removed.",
     *				paramType="path",
     *				required=true,
     *				type="string"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Chat not found")
     * 	)
     * )
     */
    public function deleteChatLabelAction($chat_id, $label)
    {
        $chat = $this->_getChatOr404($chat_id);

        $chat->getLabelManager()->removeLabel($label);
        $this->em->persist($chat);
        $this->em->flush();

        return $this->createSuccessResponse();
    }

    /**
     * @param  integer                                                       $id
     * @return \Application\DeskPRO\Entity\ChatConversation
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function _getChatOr404($id)
    {
        $chat = $this->em->getRepository('DeskPRO:ChatConversation')->findOneById($id);

        if (!$chat || !$this->person->PermissionsManager->ChatChecker->canView($chat)) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no chat with ID $id");
        }

        return $chat;
    }
}
