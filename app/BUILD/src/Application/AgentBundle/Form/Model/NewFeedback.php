<?php

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
                    $attach = new \Application\DeskPRO\Entity\FeedbackAttachment();
                    $attach->setPerson($feedback->getPerson())->setFeedback($feedback)->setBlob($blob->setIsTemp(false));
                    $feedback->addAttachment($attach);
                    $this->em->persist($attach);
                    $this->em->persist($blob);
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
