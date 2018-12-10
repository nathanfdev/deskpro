<?php

namespace DeskPRO\Bundle\MessengerBundle\Settings\Model;

class MessengerChat
{
    const NO_ANSWER_SAVE_TICKET   = 'save_ticket';
    const NO_ANSWER_SHOW_BUSY     = '';
    const NO_ANSWER_CREATE_TICKET = 'create_ticket';

    /**
     * @var bool
     */
    private $enabled = true;

    /**
     * @var string
     */
    private $prompt = 'What can we help you with today?';

    /**
     * @var int
     */
    private $timeout = 90;

    /**
     * @var string
     */
    private $noAnswerBehavior = 'save_ticket';

    /**
     * @var string
     */
    private $busyMessage = 'It looks like all of our agents are busy at the moment. You can still send us a ticket below and we will get back to you as soon as possible';

    /**
     * @var int
     */
    private $department = 0;

    /**
     * @var string
     */
    private $ticketSubject = 'Missed chat from {name}';

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     *
     * @return $this
     */
    public function setEnabled($enabled)
    {
        $this->enabled = (bool) $enabled;

        return $this;
    }

    /**
     * @return string
     */
    public function getPrompt()
    {
        return $this->prompt;
    }

    /**
     * @param string $prompt
     *
     * @return $this
     */
    public function setPrompt($prompt)
    {
        $this->prompt = $prompt;

        return $this;
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @param int $timeout
     *
     * @return $this
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * @return string
     */
    public function getNoAnswerBehavior()
    {
        return $this->noAnswerBehavior;
    }

    /**
     * @param string $noAnswerBehavior
     *
     * @return $this
     */
    public function setNoAnswerBehavior($noAnswerBehavior)
    {
        $this->noAnswerBehavior = $noAnswerBehavior;

        return $this;
    }

    /**
     * @return string
     */
    public function getBusyMessage()
    {
        return $this->busyMessage;
    }

    /**
     * @param string $busyMessage
     *
     * @return $this
     */
    public function setBusyMessage($busyMessage)
    {
        $this->busyMessage = $busyMessage;

        return $this;
    }

    /**
     * @return int
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * @param int $department
     *
     * @return $this
     */
    public function setDepartment($department)
    {
        $this->department = $department;

        return $this;
    }

    /**
     * @return string
     */
    public function getTicketSubject()
    {
        return $this->ticketSubject;
    }

    /**
     * @param string $ticketSubject
     *
     * @return $this
     */
    public function setTicketSubject($ticketSubject)
    {
        $this->ticketSubject = $ticketSubject;

        return $this;
    }
}
