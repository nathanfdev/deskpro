<?php

namespace DeskPRO\Bundle\MessengerBundle\Controller;

use Application\DeskPRO\Entity\ChatBlock;
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\Session;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\EventListener\ClientMessage\ClientMessageEvent;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\UserChat\ChatCreateType;
use DeskPRO\Bundle\AppBundle\UserChat\UserChatEvent;
use DeskPRO\Bundle\MessengerBundle\Handler\ChatHandler;
use DeskPRO\Bundle\MessengerBundle\Security\EventListener\VisitorIdListener;
use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class ChatController.
 *
 * @ApiModes("all")
 * @ApiUserContext("open")
 * @ApiDoc(
 *     target="createChat",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\UserChat\ChatConversationType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\ChatConversation"
 *      }
 *     }
 * )
 * @Rest\Route("/chat")
 * @Feature("messenger")
 */
class ChatController extends AbstractMessengerController
{
    /**
     * @param Request $request
     * @Rest\Post("")
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return View
     */
    public function createChatAction(Request $request)
    {
        // create legacy session
        $session = new Session();
        $session->setIpAddress($request->getClientIp());
        $visitorId = $request->headers->get(VisitorIdListener::VISITOR_HEADER_NAME);
        $session->setVisitorId($visitorId);
        $person = $this->getUser();
        if ($person && $person->getId()) {
            $session->setPerson($person);
        }

        /** @var \Application\DeskPRO\EntityRepository\ChatBlock $repo */
        $repo  = $this->getDoctrine()->getRepository(ChatBlock::class);
        $block = $repo->getBlockForVisitor($session->getVisitorId(), $request->getClientIp());

        if ($block) {
            throw new AccessDeniedHttpException('banned');
        }

        $this->em()->persist($session);
        $this->em()->flush();

        // create a new chat conversation
        $chat = ChatConversation::newForUserSession($session);

        $form = $this->container->get('form.factory')->create(
            ChatCreateType::class,
            $chat,
            [
                'visitor_id' => $this->getVisitorId($request),
                'person'     => $person && $person->getId() ? $person : null,
            ]
        );

        $form->submit($request->request->all(), false);
        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        $chat->setVisitorId($visitorId);

        $this->em()->persist($chat);
        $this->em()->flush();

        $this->get('event_dispatcher')->dispatch(
            ClientMessageEvent::SEND,
            new ClientMessageEvent('chat.new', $chat)
        );

        // If an email validation code was generated then user needs to validate the entered email first,
        // so skip agent notify until the user validates it
        if ($chat->getEmailValidationCode()) {
            $this->get('event_dispatcher')->dispatch(UserChatEvent::VALIDATE_EMAIL, new UserChatEvent($chat));
        } else {
            // create a task to find an agent
            $task = $this->get('dp.voice.task_builder')->createChatTaskForQueue($chat);
            $chat->setTaskId($task->getId());
        }
        $this->em()->flush();

        return View::create($this->wrap($chat), Response::HTTP_CREATED);
    }

    /**
     * @param string  $idToken
     * @param Request $request
     *
     * @ApiDoc(
     *     section="Messenger",
     *     resourceDescription="send chat message",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     requirements={
     *          {
     *              "name"="idToken",
     *              "requirement"="[a-zA-Z0-9\\-]+",
     *              "description"="id-accessToken to find a chat",
     *              "dataType"="string"
     *          }
     *      }
     * )
     *
     * @Rest\Post("/{idToken}/send", requirements={"idToken"="(\d+)\-([a-zA-Z0-9]{30})"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function sendMessageAction($idToken, Request $request)
    {
        $chat = $this->findChatByIdToken($idToken);

        return View::create($this->get('messenger.handlers.chat')->handle($chat, $request->request->all()), Response::HTTP_OK);
    }

    /**
     * @param string  $idToken
     *
     * @ApiDoc(
     *     section="Messenger",
     *     resourceDescription="ping chat",
     *     statusCodes={
     *         204="Returned if everything is ok"
     *     },
     *     requirements={
     *          {
     *              "name"="idToken",
     *              "requirement"="[a-zA-Z0-9\\-]+",
     *              "description"="id-accessToken to find a chat",
     *              "dataType"="string"
     *          }
     *      }
     * )
     *
     * @Rest\Post("/{idToken}/ping", requirements={"idToken"="(\d+)\-([a-zA-Z0-9]{30})"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function pingChatAction($idToken)
    {
        $chat = $this->findChatByIdToken($idToken);
        $this->get('event_dispatcher')->dispatch(UserChatEvent::POLLING, new UserChatEvent($chat));

        return View::create(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param string  $idToken
     * @param Request $request
     *
     * @ApiDoc(
     *     section="Messenger",
     *     resourceDescription="Get messages",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     requirements={
     *          {
     *              "name"="idToken",
     *              "requirement"="[a-zA-Z0-9\\-]+",
     *              "description"="id-accessToken to find a chat",
     *              "dataType"="string"
     *          }
     *      }
     * )
     *
     * @Rest\Get("/{idToken}/messages", requirements={"idToken"="(\d+)\-([a-zA-Z0-9]{30})"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getChatMessagesAction($idToken)
    {
        $chat = $this->findChatByIdToken($idToken);

        return View::create(
            $this
                ->get('messenger.handlers.chat')
                ->handle($chat, ['type' => ChatHandler::CHAT_HISTORY]),
            Response::HTTP_OK
        );
    }

    /**
     * @param $idToken
     *
     * @return ChatConversation|null|object
     */
    private function findChatByIdToken($idToken)
    {
        list($id, $accessToken) = explode('-', $idToken);
        $chat                   = $this->getRepository(ChatConversation::class)->findOneBy(['id' => $id, 'accessToken' => $accessToken]);
        if (!$chat) {
            throw $this->createNotFoundException(
                $this->createEntityNotFoundExceptionMessage(ChatConversation::class, $idToken)
            );
        }

        return $chat;
    }

    /**
     * @return EntityManager
     */
    protected function em()
    {
        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.default_entity_manager');

        return $em;
    }
}
