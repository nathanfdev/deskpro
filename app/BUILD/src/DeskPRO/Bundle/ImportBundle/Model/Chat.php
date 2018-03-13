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

namespace DeskPRO\Bundle\ImportBundle\Model;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Chat.
 */
class Chat implements PrimaryImportModelInterface, LabelAwareModelInterface, CustomDataAwareModelInterface
{
    use PrimaryImportModelTrait, LabelAwareTrait, CustomDataAwareTrait;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $subject = '';

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $person;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $agent;

    /**
     * @var ChatMessage[]
     *
     * @JMS\Type("array<DeskPRO\Bundle\ImportBundle\Model\ChatMessage>")
     *
     * @Assert\Valid()
     * @Assert\Count(min="1")
     */
    private $messages = [];

    /**
     * @var \DateTime
     *
     * @JMS\Type("DateTime")
     */
    private $dateCreated;

    /**
     * @var \DateTime
     *
     * @JMS\Type("DateTime")
     */
    private $dateEnded;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     * @Assert\Choice(choices={"timeout", "abandoned", "agent", "user"})
     */
    private $endedBy;

    /**
     * @var int
     *
     * @JMS\Type("integer")
     *
     * @Assert\NotNull()
     * @Assert\Range(min="0", max="10")
     */
    private $ratingOverall = 0;

    /**
     * @var string
     *
     * @Assert\NotNull()
     * @JMS\Type("string")
     */
    private $ratingComment = '';

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject ?: '';
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
     * @return string
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param string $person
     *
     * @return $this
     */
    public function setPerson($person)
    {
        $this->person = $person;

        return $this;
    }

    /**
     * @return string
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
     * @return ChatMessage[]
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param ChatMessage[] $messages
     *
     * @return $this
     */
    public function setMessages(array $messages)
    {
        $this->messages = $messages;

        return $this;
    }

    /**
     * @param ChatMessage $message
     *
     * @return $this
     */
    public function addMessage(ChatMessage $message)
    {
        $this->messages[] = $message;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * @param \DateTime $dateCreated
     *
     * @return $this
     */
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateEnded()
    {
        return $this->dateEnded;
    }

    /**
     * @param \DateTime $dateEnded
     *
     * @return $this
     */
    public function setDateEnded($dateEnded)
    {
        $this->dateEnded = $dateEnded;

        return $this;
    }

    /**
     * @return string
     */
    public function getEndedBy()
    {
        return $this->endedBy;
    }

    /**
     * @param string $endedBy
     *
     * @return $this
     */
    public function setEndedBy($endedBy)
    {
        $this->endedBy = $endedBy;

        return $this;
    }

    /**
     * @return int
     */
    public function getRatingOverall()
    {
        return $this->ratingOverall;
    }

    /**
     * @param int $ratingOverall
     *
     * @return $this
     */
    public function setRatingOverall($ratingOverall)
    {
        $this->ratingOverall = $ratingOverall;

        return $this;
    }

    /**
     * @return string
     */
    public function getRatingComment()
    {
        return $this->ratingComment;
    }

    /**
     * @param string $ratingComment
     *
     * @return $this
     */
    public function setRatingComment($ratingComment)
    {
        $this->ratingComment = $ratingComment;

        return $this;
    }
}
