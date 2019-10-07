<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Controller;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\DirectMessage;
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
     * Number of direct messages per page
     */
    const DIRECT_MESSAGES_PER_PAGE = 20;

    /**
     * @Route("/dm", name="portal_dm")
     * @Security("is_granted('ROLE_USER')")
     *
     * @param Request $request
     */
    public function indexAction(Request $request)
    {
        $this->isCommunityEnabledOrNotFoundException();

        $dataService  = $this->getDirectMessageThreadDataService();
        $pager = $dataService->getForUser(
            $this->getUser(),
            $request->query->has('unread'),
            $request->query->get('page', 1),
            self::DIRECT_MESSAGES_PER_PAGE,
            true
        );

        $participants = $dataService->getParticipantsGroupedByThreads($pager->getCurrentPageResults(), 0);

        $breadcrumbs = $this->getBreadcrumbGenerator()->buildDirectMessagesList();

        return $this->renderThemeView(
            'Theme:DirectMessages:index.html.twig',
            [
                'breadcrumbs'  => $breadcrumbs,
                'pager'        => $pager,
                'participants' => $participants,
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
            $this->getDirectMessageThreadDataService()->saveMessage($message);
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
            'email'   => $request->attributes->get('to', ''),
            'message' => '',
        ];

        $form = $this->createForm(DirectMessageNewThreadType::class, $defaultData);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data        = $form->getData();
            $personTo    = $this->getEm()->getRepository(Person::class)->findOneByEmail($data['email']);
            $thread      = $this->getDirectMessageThreadDataService()->createThread($this->getUser(), $personTo);
            $participant = $this->getEm()->getRepository(DirectMessageParticipant::class)->findOneBy([
                'thread' => $thread,
                'person' => $this->getUser(),
            ]);
            $message = new DirectMessage();
            $message->setAuthor($participant);
            $message->setMessageHtml($data['message']);

            $this->getDirectMessageThreadDataService()->saveMessage($message);
            $this->sendEmails($message);

            $this->addFlash('success', 'Message added');

            return $this->redirectToRoute('portal_dm_view', ['id' => $thread->getId()]);
        }

        $breadcrumbs = $this->getBreadcrumbGenerator()->buildDirectMessagesList();

        return $this->renderThemeView(
            'Theme:DirectMessages:send.html.twig',
            [
                'breadcrumbs' => $breadcrumbs,
                'form'        => $form->createView(),
            ]
        );
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
