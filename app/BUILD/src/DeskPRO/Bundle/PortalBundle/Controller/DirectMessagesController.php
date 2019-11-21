<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Controller;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\DirectMessage;
use DeskPRO\Bundle\AppBundle\Entity\DirectMessageBlock;
use DeskPRO\Bundle\AppBundle\Entity\DirectMessageParticipant;
use DeskPRO\Bundle\AppBundle\Entity\DirectMessageThread;
use DeskPRO\Bundle\PortalBundle\Form\Form\Type\DirectMessageNewThreadType;
use DeskPRO\Bundle\PortalBundle\Form\Form\Type\DirectMessageReplyType;
use DeskPRO\Bundle\PortalBundle\HttpCache\Configuration\PageHttpCache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DirectMessagesController extends AbstractController
{
    /**
     * Number of direct messages per page.
     */
    const DIRECT_MESSAGES_PER_PAGE = 10;

    /**
     * @Route("/dm", name="portal_dm")
     * @Security("is_granted('ROLE_USER')")
     *
     * @param Request $request
     */
    public function indexAction(Request $request)
    {
        $this->isCommunityEnabledOrNotFoundException();

        $dataService = $this->getDirectMessageThreadDataService();
        $pager       = $dataService->getForUser(
            $this->getUser(),
            $request->query->has('unread'),
            $request->query->get('page', 1),
            self::DIRECT_MESSAGES_PER_PAGE,
            true,
            true
        );

        $participants = $dataService->getParticipantsGroupedByThreads($pager->getCurrentPageResults(), 0);

        $breadcrumbs = $this->getBreadcrumbGenerator()->buildDirectMessagesList();

        $isBlockedByThreadId = [];
        foreach ($pager as list($thread)) {
            $isBlockedByThreadId[$thread->getId()] = $this->getEm()->getRepository(DirectMessageBlock::class)
                ->isBlockedByThread($thread, $this->getUser())
            ;
        }

        return $this->renderThemeView(
            'Theme:DirectMessages:index.html.twig',
            [
                'breadcrumbs'             => $breadcrumbs,
                'pager'                   => $pager,
                'participants'            => $participants,
                'is_blocked_by_thread_id' => $isBlockedByThreadId,
            ]
        );
    }

    /**
     * @Route("/dm/{id}", name="portal_dm_view", requirements={"id"="\d+"})
     * @ParamConverter("thread", class="AppBundle:DirectMessageThread")
     * @Security("is_granted('ROLE_USER')")
     * @PageHttpCache(content="thread")
     *
     * @param Request             $request
     * @param DirectMessageThread $thread
     *
     * @return Response
     */
    public function viewAction(Request $request, DirectMessageThread $thread)
    {
        $this->isCommunityEnabledOrNotFoundException();

        $messages = $this->getEm()->getRepository(DirectMessage::class)
            ->getForThread($thread);

        $participant = $this->getEm()->getRepository(DirectMessageParticipant::class)->findOneBy([
            'thread' => $thread,
            'person' => $this->getUser(),
        ]);
        $replyForm = false;

        if ($participant) {
            $participant->setIsUnread(false);
            $this->getEm()->flush();

            $replyForm = $this->createForm(DirectMessageReplyType::class, new DirectMessage(), [
                'action' => $this->generateUrl('portal_dm_reply', ['id' => $thread->getId()]),
            ]);
        }

        $breadcrumbs = $this->getBreadcrumbGenerator()->buildDirectMessagesList();

        return $this->renderThemeView(
            'Theme:DirectMessages:view.html.twig',
            [
                'breadcrumbs' => $breadcrumbs,
                'messages'    => $messages,
                'reply_form'  => $replyForm ? $replyForm->createView() : false,
            ]
        );
    }

    /**
     * @Route("/dm/{id}/reply", name="portal_dm_reply", requirements={"id"="\d+"})
     * @Method({"POST"})
     * @ParamConverter("thread", class="AppBundle:DirectMessageThread")
     * @Security("is_granted('ROLE_USER')")
     * @PageHttpCache(content="thread")
     *
     * @param Request             $request
     * @param DirectMessageThread $thread
     *
     * @return Response
     */
    public function replyAction(Request $request, DirectMessageThread $thread)
    {
        $this->isCommunityEnabledOrNotFoundException();

        $participant = $this->getEm()->getRepository(DirectMessageParticipant::class)->findOneBy([
            'thread' => $thread,
            'person' => $this->getUser(),
        ]);

        if (!$participant) {
            throw $this->createNotFoundException("Can't find thread participant for person #".$this->getUser()->getId());
        }

        $message = new DirectMessage();
        $message->setAuthor($participant);

        $form = $this->createForm(DirectMessageReplyType::class, $message);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $message = $form->getData();

            try {
                $this->getDirectMessageThreadDataService()->saveMessage($message, $this->getUser());
            } catch (\DomainException $e) {
                $this->addFlash('warning', $e->getMessage());

                return $this->redirectToRoute('portal_dm_view', ['id' => $thread->getId()]);
            }

            $this->sendEmails($message);
            $this->addFlash('success', 'Message added');

            return $this->redirectToRoute('portal_dm_view', ['id' => $thread->getId()]);
        }

        $this->addFlash('error', 'Some error occured');

        return $this->redirectToRoute('portal_dm_view', ['id' => $thread->getId()]);
    }

    /**
     * @Route("/dm/send", name="portal_dm_send")
     * @Route("/dm/send/to/{to}", name="portal_dm_send_to")
     * @ParamConverter("to", class="DeskPRO:Person")
     * @Security("is_granted('ROLE_USER')")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function sendAction(Request $request)
    {
        $this->isCommunityEnabledOrNotFoundException();

        $defaultData = [
            'person'  => null,
            'message' => '',
        ];

        $personTo = $request->attributes->get('to');
        if ($personTo) {
            $defaultData['person'] = $personTo;
            $thread                = $this->getEm()->getRepository(DirectMessageThread::class)->getOneForUsers($this->getUser(), $personTo);
            if ($thread) {
                return $this->redirectToRoute('portal_dm_view', ['id' => $thread->getId()]);
            }
        } else {
            // must always arrive here from a link to a specific person
            // currently we have no way to send arbitrary messages by selecting the person
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(DirectMessageNewThreadType::class, $defaultData);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data        = $form->getData();
                $thread      = $this->getDirectMessageThreadDataService()->createThread($this->getUser(), $personTo);
                $participant = $this->getEm()->getRepository(DirectMessageParticipant::class)->findOneBy([
                    'thread' => $thread,
                    'person' => $this->getUser(),
                ]);
                $message = new DirectMessage();
                $message->setAuthor($participant);
                $message->setMessageHtml($data['message']);

                $this->getDirectMessageThreadDataService()->saveMessage($message, $this->getUser());
            } catch (\DomainException $e) {
                $this->addFlash('warning', $e->getMessage());

                return $this->redirectToRoute('portal_dm');
            }

            $this->sendEmails($message);

            $this->addFlash('success', 'Message added');

            return $this->redirectToRoute('portal_dm_view', ['id' => $thread->getId()]);
        } else {
            $form->setData($defaultData);
        }

        $breadcrumbs = $this->getBreadcrumbGenerator()->buildDirectMessagesList();

        return $this->renderThemeView(
            'Theme:DirectMessages:send.html.twig',
            [
                'breadcrumbs' => $breadcrumbs,
                'form'        => $form->createView(),
                'personTo'    => $personTo,
            ]
        );
    }

    /**
     * @Route("/dm/{id}/block", name="portal_dm_block")
     * @Security("is_granted('ROLE_USER') and thread.hasParticipantId(user.id)")
     *
     * @param DirectMessageThread $thread
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function blockAction(DirectMessageThread $thread)
    {
        $this->isCommunityEnabledOrNotFoundException();

        $em = $this->getEm();
        $em->persist(DirectMessageBlock::createFromThread($em, $thread, $this->getUser()));
        $em->flush();

        $this->addFlash('success', 'User blocked');

        return $this->redirectToRoute('portal_dm');
    }

    /**
     * @Route("/dm/{id}/unblock", name="portal_dm_unblock")
     * @Security("is_granted('ROLE_USER') and thread.hasParticipantId(user.id)")
     *
     * @param DirectMessageThread $thread
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function unblockAction(DirectMessageThread $thread)
    {
        $this->isCommunityEnabledOrNotFoundException();

        try {
            $this->getEm()->getRepository(DirectMessageBlock::class)->unblockIfUserIsBlocker(
                $thread,
                $this->getUser()
            );
        } catch (\DomainException $e) {
            $this->addFlash('warning', $e->getMessage());

            return $this->redirectToRoute('portal_dm');
        }

        $this->addFlash('success', 'User unblocked');

        return $this->redirectToRoute('portal_dm');
    }

    /**
     * @TODO: move in some service
     *
     * @param DirectMessage $message
     *
     * @return mixed
     */
    protected function sendEmails(DirectMessage $message)
    {
        $thread       = $message->getAuthor()->getThread();
        $participants = $this->getDirectMessageThreadDataService()
            ->getParticipantsGroupedByThreads([$thread]);

        if (!isset($participants[$thread->getId()])) {
            return;
        }

        foreach ($participants[$thread->getId()] as $participant) {
            if ($participant->getId() === $message->getAuthor()->getId()) {
                continue;
            }
            $this->getEmailSender()->sendDirectMessageNewEmail($participant->getPerson(), $message);
        }
    }

    /**
     * @throws NotFoundHttpException
     */
    protected function isCommunityEnabledOrNotFoundException()
    {
        $settings = $this->container->get('settings_resolver');
        if (!$settings->getGlobalSettings()->get('portal.members_community')) {
            throw $this->createNotFoundException();
        }
    }
}
