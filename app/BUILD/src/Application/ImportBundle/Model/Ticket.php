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

namespace Application\ImportBundle\Model;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Exporting ticket entity.
 *
 * Class Ticket
 */
class Ticket implements PersonAwareInterface, LabelAwareModelInterface, LanguageAwareInterface, CustomDataAwareModelInterface, PrimaryImportModelInterface
{
    use PrimaryImportModelTrait, LabelAwareTrait, CustomDataAwareTrait;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $ref;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $department;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     */
    private $person;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $agent;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     * @Assert\Choice(choices={
     *   "awaiting_agent",
     *   "awaiting_user",
     *   "resolved",
     *   "archived",
     *   "hidden",
     *   "hidden.spam",
     *   "hidden.deleted"
     * })
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @JMS\Type("DateTime")
     */
    private $date_created;

    /**
     * @var \DateTime
     *
     * @JMS\Type("DateTime")
     */
    private $date_resolved;

    /**
     * @var \DateTime
     *
     * @JMS\Type("DateTime")
     */
    private $date_archived;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     */
    private $subject;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $language;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $category;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $priority;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $workflow;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $product;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $organization;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $is_hold = false;

    /**
     * @var int
     *
     * @JMS\Type("integer")
     */
    private $urgency = 1;

    /**
     * @var string[]
     *
     * @JMS\Type("array<string>")
     *
     * @Assert\All(constraints={
     *   @Assert\NotBlank()
     * })
     */
    private $participants = [];

    /**
     * @var TicketMessage[]
     *
     * @JMS\Type("array<Application\ImportBundle\Model\TicketMessage>")
     *
     * @Assert\Valid()
     */
    private $messages = [];

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $log_message = 'Imported';

    /**
     * @return int
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * @param int $ref
     *
     * @return $this
     */
    public function setRef($ref)
    {
        $this->ref = $ref;

        return $this;
    }

    /**
     * @return string
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * @param string $department
     *
     * @return $this
     */
    public function setDepartment($department)
    {
        $this->department = $department;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * {@inheritdoc}
     */
    public function setPerson($person)
    {
        $this->person = $person;

        return $this;
    }

    /**
     * @return int
     */
    public function getAgent()
    {
        return $this->agent;
    }

    /**
     * @param string $agent
     *
     * @return $this
     */
    public function setAgent($agent)
    {
        $this->agent = $agent;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * @param \DateTime $date_created
     *
     * @return $this
     */
    public function setDateCreated(\DateTime $date_created = null)
    {
        $this->date_created = $date_created;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateArchived()
    {
        return $this->date_archived;
    }

    /**
     * @param \DateTime $date_archived
     *
     * @return $this
     */
    public function setDateArchived(\DateTime $date_archived = null)
    {
        $this->date_archived = $date_archived;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateResolved()
    {
        return $this->date_resolved;
    }

    /**
     * @param \DateTime $date_resolved
     *
     * @return $this
     */
    public function setDateResolved(\DateTime $date_resolved = null)
    {
        $this->date_resolved = $date_resolved;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     *
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * {@inheritdoc}
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * @return string
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param string $priority
     *
     * @return $this
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param string $category
     *
     * @return $this
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return string
     */
    public function getWorkflow()
    {
        return $this->workflow;
    }

    /**
     * @param string $workflow
     *
     * @return $this
     */
    public function setWorkflow($workflow)
    {
        $this->workflow = $workflow;

        return $this;
    }

    /**
     * @return string
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param string $product
     *
     * @return $this
     */
    public function setProduct($product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Returns a ticket organization.
     *
     * @return string
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * Set an organization.
     *
     * @param string $organization
     *
     * @return $this
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * @return bool
     */
    public function isHold()
    {
        return $this->is_hold;
    }

    /**
     * @param bool $is_hold
     *
     * @return $this
     */
    public function setAsHold($is_hold)
    {
        $this->is_hold = $is_hold;

        return $this;
    }

    /**
     * @return int
     */
    public function getUrgency()
    {
        return $this->urgency;
    }

    /**
     * @param int $urgency
     *
     * @return $this
     */
    public function setUrgency($urgency)
    {
        $this->urgency = $urgency;

        return $this;
    }

    /**
     * @return array
     */
    public function getParticipants()
    {
        return $this->participants;
    }

    /**
     * @param \string[] $participants
     *
     * @return $this
     */
    public function setParticipants(array $participants)
    {
        $this->participants = $participants;

        return $this;
    }

    /**
     * @param string $participant
     *
     * @return $this
     */
    public function addParticipant($participant)
    {
        $this->participants[] = $participant;

        return $this;
    }

    /**
     * Returns ticket messages.
     *
     * @return TicketMessage[]
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Add a ticket message.
     *
     * @param TicketMessage $message
     *
     * @return $this
     */
    public function addMessage(TicketMessage $message)
    {
        $this->messages[] = $message;

        return $this;
    }

    /**
     * @return string
     */
    public function getLogMessage()
    {
        return $this->log_message;
    }

    /**
     * @param string $log_message
     *
     * @return $this
     */
    public function setLogMessage($log_message)
    {
        $this->log_message = $log_message;

        return $this;
    }
}
