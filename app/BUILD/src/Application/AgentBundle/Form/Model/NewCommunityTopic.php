<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Form\Model;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\CommunityTopic;
use Application\DeskPRO\Entity\CommunityTopicAttachment;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContext;
use Application\DeskPRO\Tickets\TicketManager;
use DeskPRO\Bundle\AppBundle\Content\CommunitySubscriptionHelper;
use DeskPRO\Bundle\AppBundle\Entity\TicketCommunityTopicLink;
use Doctrine\ORM\EntityManager;

class NewCommunityTopic
{
    /**
     * @var EntityManager
     */
    protected $em;

    /** @var string */
    public $title;

    /**
     * @var Brand
     */
    public $brand;

    /** @var int */
    public $forum_id;

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

    /** @var array */
    public $blob_inline_ids;

    /** @var Ticket */
    public $person;

    /** @var Ticket */
    public $linked_ticket;

    /** @var bool */
    public $is_subscribe_ticket_owner = false;

    /** @var bool */
    public $is_subscribe_ticket_participants = false;

    /** @var CommunityTopic */
    protected $_communityTopic;

    /**
     * @var TicketManager
     */
    protected $ticket_manager;

    /**
     * @var CommunitySubscriptionHelper
     */
    protected $subscriptionHelper;

    public function __construct(
        EntityManager $em,
        Person $person_context,
        TicketManager $ticket_manager,
        CommunitySubscriptionHelper $subscriptionHelper)
    {
        $this->em                 = $em;
        $this->ticket_manager     = $ticket_manager;
        $this->_person_context    = $person_context;
        $this->subscriptionHelper = $subscriptionHelper;
    }

    public function save()
    {
        $this->em->beginTransaction();

        $communityTopic = new CommunityTopic();
        $communityTopic->setBrand($this->brand);
        $communityTopic->person = $this->getPersonForCommunityTopic();
        $communityTopic->setStatusCode($this->status_code);
        $communityTopic->title = $this->title;

        $communityTopic->content = $this->_person_context->hasPerm('agent_publish.can_insert_html')
            ? App::$container->getInputCleaner()->clean($this->content ?: '', 'string', ['noclean' => true])
            : App::$container->getInputCleaner()->clean($this->content ?: '', 'html');

        $cat = $this->em->find('DeskPRO:CommunityForum', $this->forum_id);
        $communityTopic->setForum($cat);
        $this->em->persist($communityTopic);
        $this->em->flush();

        if ($this->labels) {
            $communityTopic->getLabelManager()->setLabelsArray($this->labels, $this->em);
            $this->em->flush();
        }

        if ($this->attach_ids) {
            $attachBlobs = $this->em->getRepository(Blob::class)->findBy(['id' => $this->attach_ids]);
            foreach ($attachBlobs as $blob) {
                $attach = new CommunityTopicAttachment();
                $attach->setPerson($communityTopic->getPerson())->setTopic($communityTopic)->setBlob($blob->setIsTemp(false)->setSourceRef('community_topic.'.$communityTopic->getId()));
                $communityTopic->addAttachment($attach);
                $this->em->persist($attach);
                $this->em->persist($blob);
            }
        }

        if ($this->blob_inline_ids) {
            $inlineBlobs = $this->em->getRepository(Blob::class)->findBy(['id' => $this->blob_inline_ids]);
            foreach ($inlineBlobs as $blob) {
                $this->em->persist($blob->setIsTemp(false)->setSourceRef('community_topic.'.$communityTopic->getId()));
            }
        }

        $this->em->flush();

        $this->_communityTopic = $communityTopic;

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

        $link = new TicketCommunityTopicLink();
        $link->setPerson($this->_person_context);
        $link->setTopic($this->_communityTopic);
        $this->linked_ticket->addTopicLink($link);
        $this->linked_ticket->disableAutoTicketProcess();

        $context = $this->ticket_manager->createAgentExecutorContext(
            $this->_person_context,
            ExecutorContext::EVENT_UPDATE,
            ExecutorContext::METHOD_WEB
        );

        $this->ticket_manager->saveTicket($this->linked_ticket, $context);

        $this->subscriptionHelper->subscribeTicketPersons(
            $this->_communityTopic,
            $this->linked_ticket,
            $this->is_subscribe_ticket_owner,
            $this->is_subscribe_ticket_participants
        );
    }

    /**
     * @return Person
     */
    protected function getPersonForCommunityTopic()
    {
        return $this->person ?: $this->_person_context;
    }

    public function getTopic()
    {
        return $this->_communityTopic;
    }
}
