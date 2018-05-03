<?php

namespace DeskPRO\Bundle\AppBundle\AntiAbuse;

use Application\DeskPRO\Entity\Person;

/**
 * Class AntiAbuseConfig.
 */
class AntiAbuseConfig
{
    const RESPONSE_LOCKOUT = 'lockout';
    const RESPONSE_CAPTCHA = 'captcha';

    /**
     * @var Person
     */
    private $person;

    /**
     * @var string
     */
    private $action;

    /**
     * @var string
     */
    private $response;

    /**
     * @var int
     */
    private $lockoutTime;

    /**
     * @var int
     */
    private $limit;

    /**
     * @var int
     */
    private $time;

    /**
     * @var bool
     */
    private $enabled = false;

    /**
     * AntiAbuseConfig constructor.
     *
     * @param Person $person
     * @param string $action
     */
    public function __construct(Person $person, $action)
    {
        $this->person = $person;
        $this->action = $action;
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param string $response
     *
     * @return $this
     */
    public function setResponse($response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * @return int
     */
    public function getLockoutTime()
    {
        return $this->lockoutTime;
    }

    /**
     * @param int $lockoutTime
     *
     * @return $this
     */
    public function setLockoutTime($lockoutTime)
    {
        $this->lockoutTime = (int) $lockoutTime;

        return $this;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     *
     * @return $this
     */
    public function setLimit($limit)
    {
        $this->limit = (int) $limit;

        return $this;
    }

    /**
     * @return int
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param int $time
     *
     * @return $this
     */
    public function setTime($time)
    {
        $this->time = (int) $time;

        return $this;
    }

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
     * @return bool
     */
    public function isValid()
    {
        return $this->time && $this->limit;
    }

    public function accountOnlySettings()
    {
        return $this->action === AntiAbuse::ACTION_LOGIN && !$this->person->isGuest();
    }
}
