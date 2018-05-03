<?php

namespace Application\AgentBundle\Controller;

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\Topic;

class GoToController extends AbstractController
{
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
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function ticketIdAction($id)
    {
        /** @var Ticket $ticket */
        $ticket = $this->getDoctrine()->getRepository(Ticket::class)->find($id);
        if (!$ticket) {
            $ticket = $this->getDoctrine()->getRepository(Ticket::class)->findTicketRef($id);
            if (!$ticket) {
                $this->createNotFoundException();
            }
        }

        return $this->redirect($this->getBasePath().'#app.tickets,inbox:agent,t:'.$id);
    }

    /**
     * @param string $ref
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
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
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function personIdAction(Person $person)
    {
        return $this->redirect($this->getBasePath().'#app.people,people:*,p:'.$person->getId());
    }

    /**
     * @param string $emailAddress
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
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
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function organizationIdAction(Organization $organization)
    {
        return $this->redirect($this->getBasePath().'#app.people,orgs,o:'.$organization->getId());
    }

    /**
     * @param Article $article
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function articleIdAction(Article $article)
    {
        return $this->redirect($this->getBasePath().'#app.publish,knowledgebase:1,a:'.$article->getId());
    }

    /**
     * @param Download $download
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function downloadIdAction(Download $download)
    {
        return $this->redirect($this->getBasePath().'#app.publish,downloads:1,d:'.$download->getId());
    }

    /**
     * @param News $news
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function newsIdAction(News $news)
    {
        return $this->redirect($this->getBasePath().'#app.publish,news:1,n:'.$news->getId());
    }

    /**
     * @param Feedback $feedback
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function feedbackIdAction(Feedback $feedback)
    {
        return $this->redirect($this->getBasePath().'#app.feedback,fb_content,i:'.$feedback->getId());
    }

    /**
     * @param ChatConversation $conversation
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function chatIdAction(ChatConversation $conversation)
    {
        return $this->redirect($this->getBasePath().'#app.userchat,new:-1,c:'.$conversation->getId());
    }

    /**
     * @param Topic $topic
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function topicIdAction(Topic $topic)
    {
        return $this->redirect($this->getBasePath().'#app.publish,m:'.$topic->getId());
    }

    /**
     * @return string
     */
    private function getBasePath()
    {
        return $this->get('router')->generate('agent');
    }
}
