<?php

namespace DeskPRO\Bundle\AppBundle\AntiAbuse\Event;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\AntiAbuse\AntiAbuseConfig;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Exception\AntiAbuseException;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Response;

/**
 * All AntiAbuse checks are made by firing one of these events.
 */
abstract class AntiAbuseEvent extends Event
{
    /**
     * @var Person|null
     */
    protected $person;

    /**
     * @var string|null
     */
    protected $email;

    /**
     * @var string|null
     */
    protected $ip;

    /**
     * @var bool
     */
    protected $recommendCaptcha = false;

    /**
     * @var bool
     */
    protected $recommendLockout = false;

    /**
     * @var Response|null
     */
    protected $recommendResponse;

    /**
     * @var bool
     */
    protected $requireResponse;

    /**
     * @var bool
     */
    protected $limited = false;

    /**
     * @var bool
     */
    protected $checkOnly = false;

    /**
     * @var int
     */
    protected $lockoutTime = 0;

    /** @var AntiAbuseConfig */
    protected $config;

    /**
     * AntiAbuseEvent constructor.
     *
     * @param      $personOrEmail
     * @param null $ip
     */
    public function __construct($personOrEmail, $ip = null)
    {
        if ($personOrEmail instanceof Person) {
            $this->person = $personOrEmail;
        } elseif (is_string($personOrEmail)) {
            $this->email = $personOrEmail;
        }

        $this->ip = $ip;
    }

    /**
     * Each implementor represents a certain type of AntiAbuse check.
     *
     * @return string
     */
    abstract public function getType();

    /**
     * @return Person|null
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param Person $person
     *
     * @return $this
     */
    public function setPerson(Person $person)
    {
        $this->person = $person;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string|null
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     *
     * @return $this
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * @return bool
     */
    public function isCaptchaRecommended()
    {
        return $this->recommendCaptcha;
    }

    /**
     * @return bool
     */
    public function isLockoutRecommended()
    {
        return $this->recommendLockout;
    }

    /**
     * @param bool $minutes true if you want to get rounded time in minutes
     *
     * @return int
     */
    public function getLockoutTime($minutes = false)
    {
        if ($minutes) {
            return ceil($this->lockoutTime / 60);
        }

        return $this->lockoutTime;
    }

    /**
     * @return bool
     */
    public function isLimited()
    {
        return $this->limited;
    }

    /**
     * @return bool
     */
    public function isResponseRecommended()
    {
        return null !== $this->getRecommendedResponse();
    }

    /**
     * @return bool
     */
    public function isResponseRequired()
    {
        return $this->requireResponse;
    }

    /**
     * @return Response|null
     */
    public function getRecommendedResponse()
    {
        return $this->recommendResponse;
    }

    /**
     * @param Response $recommendResponse
     *
     * @return $this
     */
    public function setResponse(Response $recommendResponse)
    {
        $this->recommendResponse = $recommendResponse;

        return $this;
    }

    /**
     * @return $this
     */
    public function markResponseRequired()
    {
        $this->requireResponse = true;

        return $this;
    }

    /**
     * @param int $lockoutTime
     *
     * @return $this
     */
    public function markLockoutRecommended($lockoutTime = 0)
    {
        if ($lockoutTime > 0) {
            $this->lockoutTime = $lockoutTime;
        }
        $this->recommendLockout = true;
        $this->limited          = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function markCaptchaRecommended()
    {
        $this->recommendCaptcha = true;
        $this->limited          = true;

        return $this;
    }

    /**
     * @return AntiAbuseException
     */
    public function generateException()
    {
        return new AntiAbuseException($this);
    }

    /**
     * @return bool
     */
    public function isCheckOnly()
    {
        return $this->checkOnly;
    }

    /**
     * If the event is marked as "check only" then it will ONLY CHECK the state of the abuse system
     * but it will NOT contribute to the logging/metrics that would influence the anti-abuse system.
     *
     * Without marking your event as "check only" then it will potentially add logs to the db that are then
     * used to calculate whether or not there is abuse. For this reason, tests will use an event that is
     * "check only" so that it can verify the state of the anti-abuse system.
     *
     * @param bool $checkOnly
     *
     * @return $this
     */
    public function markAsCheckOnly($checkOnly = true)
    {
        $this->checkOnly = (bool) $checkOnly;

        return $this;
    }

    /**
     * @return AntiAbuseConfig
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param AntiAbuseConfig $config
     *
     * @return $this
     */
    public function setConfig(AntiAbuseConfig $config)
    {
        $this->config = $config;

        return $this;
    }
}
