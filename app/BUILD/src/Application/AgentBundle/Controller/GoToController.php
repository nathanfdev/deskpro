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

namespace Application\AgentBundle\Controller;

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupContext;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;

class GoToController extends AbstractController
{
    protected function requireRequestToken($action, $arguments = null)
    {
        return false;
    }

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

        return $this->redirect('/agent/#app.tickets,inbox:agent,t.o:'.$id.',vis:7');
    }

    public function ticketRefAction($ref)
    {
        /** @var Ticket $ticket */
        $ticket = $this->getDoctrine()->getRepository(Ticket::class)->findTicketRef($ref);
        if (!$ticket) {
            $this->createNotFoundException();
        }

        return $this->redirect('/agent/#app.tickets,inbox:agent,t.o:'.$ticket->getId().',vis:7');
    }

    public function personIdAction($id)
    {
        /** @var Person $person */
        $person = $this->getDoctrine()->getRepository(Person::class)->find($id);
        if (!$person) {
            $this->createNotFoundException();
        }

        return $this->redirect('/agent/#app.people,people:*,p.o:'.$id.',vis:7');
    }

    public function personEmailAddressAction($emailAddress)
    {
        /** @var Person $person */
        $person = $this->getDoctrine()->getRepository(Person::class)->findOneByEmail($emailAddress);
        if (!$person) {
            $this->createNotFoundException();
        }

        return $this->redirect('/agent/#app.people,people:*,p.o:'.$person->getId().',vis:7');
    }

    public function organizationIdAction($id)
    {
        /** @var Organization $organization */
        $organization = $this->getDoctrine()->getRepository(Organization::class)->find($id);
        if (!$organization) {
            $this->createNotFoundException();
        }

        return $this->redirect('/agent/#app.people,orgs,o.o:'.$id.',vis:7');
    }

    public function articleIdAction($id)
    {
        /** @var Article $article */
        $article = $this->getDoctrine()->getRepository(Article::class)->find($id);
        if (!$article) {
            $this->createNotFoundException();
        }

        return $this->redirect('/agent/#app.publish,knowledgebase:1,a.o:'.$id.',vis:7');
    }

    public function downloadIdAction($id)
    {
        /** @var Download $download */
        $download = $this->getDoctrine()->getRepository(Download::class)->find($id);
        if (!$download) {
            $this->createNotFoundException();
        }

        return $this->redirect('/agent/#app.publish,downloads:1,d.o:'.$id.',vis:7');
    }

    public function newsIdAction($id)
    {
        /** @var News $news */
        $news = $this->getDoctrine()->getRepository(News::class)->find($id);
        if (!$news) {
            $this->createNotFoundException();
        }

        return $this->redirect('/agent/#app.publish,news:1,n.o:'.$id.',vis:7');
    }

    public function feedbackIdAction($id)
    {
        /** @var Feedback $feedback */
        $feedback = $this->getDoctrine()->getRepository(Feedback::class)->find($id);
        if (!$feedback) {
            $this->createNotFoundException();
        }

        return $this->redirect('/agent/#app.feedback,fb_content,i.o:'.$id.',vis:7');
    }

    public function chatIdAction($id)
    {
        /** @var ChatConversation $conversation */
        $conversation = $this->getDoctrine()->getRepository(ChatConversation::class)->find($id);
        if (!$conversation) {
            $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted(PermissionGroupVoter::VIEW, new PermissionGroupContext($conversation));

        return $this->redirect('/agent/#app.userchat,new:-1,c.o:'.$id.',vis:7');
    }
}
