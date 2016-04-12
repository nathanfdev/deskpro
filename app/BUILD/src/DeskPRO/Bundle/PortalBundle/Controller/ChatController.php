<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\PortalBundle\Controller;

use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\ChatMessage;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\Security\Voter\Portal\ChatVoter;
use DeskPRO\Bundle\AppBundle\Security\Voter\Portal\TicketsVoter;
use DeskPRO\Bundle\AppBundle\UserChat\UserChatEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ChatController.
 */
class ChatController extends AbstractController
{
    /**
     * @Route("/chat-logs/{type}", name="portal_chats", defaults={"type":"own"}, requirements={"type":"organization"})
     * @Route("/chat-logs", name="user_chatlogs")
     * @Route("/chat-logs/organization", name="user_chats_organization", defaults={"type":"organization"})
     * @Security("is_granted('ROLE_USER') and is_granted('USE_CHAT')")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request, $type)
    {
        $person = $this->getUser();

        // access to organization list?
        if ($type === 'organization' && !($person->organization && $person->organization_manager)) {
            return $this->redirectToRoute('portal_chats');
        }

        $max_per_page = $this->getBrandSetting('portal.per_page_chat', 50);
        $page         = $request->get('page', 1);

        $chats       = $this->getChatDataService()->getUserChatPager($this->getUser(), $page, $max_per_page, $type);
        $breadcrumbs = $this->getBreadcrumbGenerator()->buildChat();

        return $this->renderThemeView('Theme:Chat:list.html.twig', [
            'breadcrumbs' => $breadcrumbs,
            'pager'       => $chats,
            'type'        => $type,
            'person'      => $person,
        ]);
    }

    /**
     * @Route("/chat-logs/{chat}", name="portal_chats_view")
     * @Route("/chat-logs/{chat}", name="user_chatlogs_view")
     * @Security("is_granted('ROLE_USER') and is_granted('USE_TICKETS')")
     *
     * @param ChatConversation $chat
     *
     * @return Response
     */
    public function viewAction(ChatConversation $chat)
    {
        if ($chat->isAgentChat()) {
            throw $this->createNotFoundException();
        }

        // security
        $this->denyAccessUnlessGranted(ChatVoter::CHAT_VIEW, $chat);

        // ensure the use is able to view the linked ticket
        $linked_ticket_authorized = null;

        /** @var \Application\DeskPRO\EntityRepository\Ticket $ticket_repository */
        $ticket_repository = $this->getRepo('DeskPRO:Ticket');
        $linked_ticket     = $ticket_repository->getTicketLinkedToChat($chat);

        if ($linked_ticket) {
            if ($this->isGranted(TicketsVoter::TICKET_VIEW, $linked_ticket) && ($linked_ticket->isOpen() || $linked_ticket->isResolved())) {
                $linked_ticket_authorized = $linked_ticket;
            }
        }

        $breadcrumbs   = $this->getBreadcrumbGenerator()->buildChatConversation($chat);
        $chat_messages = $this->getEm()->createQuery('
            SELECT m
            FROM DeskPRO:ChatMessage m
            WHERE m.conversation = :conversation AND m.is_user_hidden = false
            ORDER BY m.id ASC
        ')->setParameter('conversation', $chat)->getResult();

        /* @var ChatMessage[] $chat_messages */
        $chat_attachments = [];
        foreach ($chat_messages as $chat_message) {
            $metadata = $chat_message->getMetadata();
            if (!empty($metadata['blob'])) {
                // Serialized blob entity
                $chat_attachments[] = $metadata['blob'];
            }
        }

        return $this->renderThemeView('Theme:Chat:view.html.twig', [
            'breadcrumbs'      => $breadcrumbs,
            'chat'             => $chat,
            'chat_messages'    => $chat_messages,
            'chat_attachments' => $chat_attachments,
            'linked_ticket'    => $linked_ticket_authorized,
            'custom_data'      => $this->get('chat.view')->getCustomDataForChat($chat),
        ]);
    }

    /**
     * @Route("/chats/{chat}/validate/email", name="portal_chats_validate_email")
     * @Method({"GET"})
     *
     * @param ChatConversation $chat
     * @param Request          $request
     *
     * @return Response
     */
    public function validateEmailAction(ChatConversation $chat, Request $request)
    {
        $form = $this
            ->get('form.factory')
            ->createNamedBuilder(null, 'api_chat_validate_email', $chat)
            ->getForm()
        ;

        $form->submit($request->query->all());
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($chat);
            $em->flush();

            $this->get('event_dispatcher')->dispatch(UserChatEvent::STARTED, new UserChatEvent($chat));
        }

        return $this->renderThemeView('Theme:Chat:validate-email.html.twig', [
            'is_valid' => $form->isValid(),
        ]);
    }
}
