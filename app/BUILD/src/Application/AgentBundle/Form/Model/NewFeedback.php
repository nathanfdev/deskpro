<?php

/*
 * Deskpro (r) has been developed by Deskpro Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, Deskpro Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that Deskpro is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing Deskpro since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team Deskpro
 */

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Form\Model;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContext;
use Application\DeskPRO\Tickets\TicketManager;
use DeskPRO\Bundle\AppBundle\Content\FeedbackSubscriptionHelper;
use DeskPRO\Bundle\AppBundle\Entity\TicketFeedbackLink;
use Doctrine\ORM\EntityManager;

class NewFeedback
{
    /**
     * @var EntityManager
     */
    protected $em;

    /** @var string */
    public $title;
    /** @var int */
    public $category_id;
    /** @var string */
    public $status_code;
    /** @var string */
    public $content;

    /** @var string */
    public $slug;
    /** @var array */
    public $labels = [];
    /** @var array */
    public $attach_ids;
    /** @var Ticket */
    public $person;
    /** @var Ticket */
    public $linked_ticket;
    /** @var bool */
    public $is_subscribe_ticket_owner = false;
    /** @var bool */
    public $is_subscribe_ticket_participants = false;

    /** @var Feedback */
    protected $_feedback;

    /**
     * @var TicketManager
     */
    protected $ticket_manager;

    /**
     * @var FeedbackSubscriptionHelper
     */
    protected $subscriptionHelper;

    public function __construct(
        EntityManager $em,
        Person $person_context,
        TicketManager $ticket_manager,
        FeedbackSubscriptionHelper $subscriptionHelper)
    {
        $this->em                 = $em;
        $this->ticket_manager     = $ticket_manager;
        $this->_person_context    = $person_context;
        $this->subscriptionHelper = $subscriptionHelper;
    }

    public function save()
    {
        $this->em->beginTransaction();

        $feedback         = new Feedback();
        $feedback->person = $this->getPersonForFeedback();
        $feedback->setStatusCode($this->status_code);
        $feedback->title = $this->title;

        $feedback->content = $this->_person_context->hasPerm('agent_publish.can_insert_html')
            ? App::$container->getInputCleaner()->clean($this->content ?: '', 'string', ['noclean' => true])
            : App::$container->getInputCleaner()->clean($this->content ?: '', 'html');

        $cat                = $this->em->find('DeskPRO:FeedbackCategory', $this->category_id);
        $feedback->category = $cat;
        $this->em->persist($feedback);
        $this->em->flush();

        if ($this->labels) {
            $feedback->getLabelManager()->setLabelsArray($this->labels, $this->em);
            $this->em->flush();
        }

        if ($this->attach_ids) {
            foreach ($this->attach_ids as $aid) {
                $blob = $this->em->getRepository('DeskPRO:Blob')->find($aid);
                if ($blob) {
                    $attach           = new \Application\DeskPRO\Entity\FeedbackAttachment();
                    $attach->person   = $feedback->person;
                    $attach->feedback = $feedback;
                    $attach->blob     = $blob;

                    $feedback->addAttachment($attach);
                    $this->em->persist($attach);
                }
            }
            $this->em->flush();
        }

        $this->_feedback = $feedback;

        try {
            $this->processLinkedTicket();
        } catch (\Exception $ex) {
            $this->em->rollback();
            throw new $ex();
        }

        $this->em->commit();
    }

    protected function processLinkedTicket()
    {
        if (!$this->linked_ticket) {
            return;
        }

        $link = new TicketFeedbackLink();
        $link->setPerson($this->_person_context);
        $link->setFeedback($this->_feedback);
        $this->linked_ticket->addFeedbackLink($link);
        $this->linked_ticket->disableAutoTicketProcess();

        $context = $this->ticket_manager->createAgentExecutorContext(
            $this->_person_context,
            ExecutorContext::EVENT_UPDATE,
            ExecutorContext::METHOD_WEB
        );

        $this->ticket_manager->saveTicket($this->linked_ticket, $context);

        $this->subscriptionHelper->subscribeTicketPersons(
            $this->_feedback,
            $this->linked_ticket,
            $this->is_subscribe_ticket_owner,
            $this->is_subscribe_ticket_participants
        );
    }

    /**
     * @return Person
     */
    protected function getPersonForFeedback()
    {
        return $this->person ?: $this->_person_context;
    }

    public function getFeedback()
    {
        return $this->_feedback;
    }
}
