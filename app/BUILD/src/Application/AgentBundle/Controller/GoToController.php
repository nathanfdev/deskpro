<?php

namespace Application\AgentBundle\Controller;

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\ReportDashboardShareableLink;
use Application\DeskPRO\Entity\ReportDashboardShareableShortUrl;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\Topic;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class GoToController extends AbstractController
{
    /**
     * {@inheritdoc}
     */
    public function preActionHandler(Request $request, $action, $arguments = null)
    {
        // there could be a redirect to anonymous action (e.g. reports)
        // so don't check if a user is logged in
    }

    /**
     * {@inheritdoc}
     */
    protected function requireRequestToken($action, $arguments = null)
    {
        return false;
    }

    /**
     * @param int $id
     *
     * @throws \Exception
     *
     * @return RedirectResponse
     */
    public function ticketIdAction($id)
    {
        /** @var Ticket $ticket */
        $ticket = $this->getDoctrine()->getRepository(Ticket::class)->find($id);
        if (!$ticket) {
            $ticket = $this->getDoctrine()->getRepository(Ticket::class)->findTicketRef($id);
            if (!$ticket) {
                throw $this->createNotFoundException();
            }
        }

        return $this->redirect($this->getBasePath().'#app.tickets,inbox:agent,t:'.$id);
    }

    /**
     * @param string $ref
     *
     * @return RedirectResponse
     */
    public function ticketRefAction($ref)
    {
        /** @var Ticket $ticket */
        $ticket = $this->getDoctrine()->getRepository(Ticket::class)->findTicketRef($ref);
        if (!$ticket) {
            $this->createNotFoundException();
        }

        return $this->redirect($this->getBasePath().'#app.tickets,inbox:agent,t:'.$ticket->getId());
    }

    /**
     * @param Person $person
     *
     * @return RedirectResponse
     */
    public function personIdAction(Person $person)
    {
        return $this->redirect($this->getBasePath().'#app.people,people:*,p:'.$person->getId());
    }

    /**
     * @param string $emailAddress
     *
     * @return RedirectResponse
     */
    public function personEmailAddressAction($emailAddress)
    {
        /** @var Person $person */
        $person = $this->getDoctrine()->getRepository(Person::class)->findOneByEmail($emailAddress);
        if (!$person) {
            $this->createNotFoundException();
        }

        return $this->redirect($this->getBasePath().'#app.people,people:*,p:'.$person->getId());
    }

    /**
     * @param Organization $organization
     *
     * @return RedirectResponse
     */
    public function organizationIdAction(Organization $organization)
    {
        return $this->redirect($this->getBasePath().'#app.people,orgs,o:'.$organization->getId());
    }

    /**
     * @param Article $article
     *
     * @return RedirectResponse
     */
    public function articleIdAction(Article $article)
    {
        return $this->redirect($this->getBasePath().'#app.publish,knowledgebase:1,a:'.$article->getId());
    }

    /**
     * @param Download $download
     *
     * @return RedirectResponse
     */
    public function downloadIdAction(Download $download)
    {
        return $this->redirect($this->getBasePath().'#app.publish,downloads:1,d:'.$download->getId());
    }

    /**
     * @param News $news
     *
     * @return RedirectResponse
     */
    public function newsIdAction(News $news)
    {
        return $this->redirect($this->getBasePath().'#app.publish,news:1,n:'.$news->getId());
    }

    /**
     * @param Feedback $feedback
     *
     * @return RedirectResponse
     */
    public function feedbackIdAction(Feedback $feedback)
    {
        return $this->redirect($this->getBasePath().'#app.feedback,fb_content,i:'.$feedback->getId());
    }

    /**
     * @param ChatConversation $conversation
     *
     * @return RedirectResponse
     */
    public function chatIdAction(ChatConversation $conversation)
    {
        return $this->redirect($this->getBasePath().'#app.userchat,new:-1,c:'.$conversation->getId());
    }

    /**
     * @param Topic $topic
     *
     * @return RedirectResponse
     */
    public function topicIdAction(Topic $topic)
    {
        return $this->redirect($this->getBasePath().'#app.publish,m:'.$topic->getId());
    }

    /**
     * @param string $authCode
     *
     * @throws \Exception
     *
     * @return RedirectResponse
     */
    public function dashboardLinkAction($authCode)
    {
        $link = $this->getDoctrine()->getRepository(ReportDashboardShareableLink::class)->findOneBy(['authCode' => $authCode]);
        if (!$link) {
            throw $this->createNotFoundException();
        }

        return $this->redirect($this->getDashboardLinkUrl($link));
    }

    /**
     * @param string $authCode
     *
     * @throws \Exception
     *
     * @return RedirectResponse
     */
    public function dashboardShortUrlAction($authCode)
    {
        $shortUrl = $this->getDoctrine()->getRepository(ReportDashboardShareableShortUrl::class)->findOneBy(['authCode' => $authCode]);
        if (!$shortUrl || $shortUrl->isExpired()) {
            throw $this->createNotFoundException();
        }

        return $this->redirect($this->getDashboardLinkUrl($shortUrl->getShareableLink()));
    }

    /**
     * @return string
     */
    private function getBasePath()
    {
        return $this->get('router')->generate('agent');
    }

    /**
     * @param ReportDashboardShareableLink $link
     *
     * @return string
     */
    private function getDashboardLinkUrl(ReportDashboardShareableLink $link)
    {
        $url = $this->getBasePath().'reports-interface/dashboard/'.$link->getAuthCode();
        if ($link->getDefaultReport()) {
            $url .= '#/'.$link->getDefaultReport()->getId();
        }

        return $url;
    }
}
