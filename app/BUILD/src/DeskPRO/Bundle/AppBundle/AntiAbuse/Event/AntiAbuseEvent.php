<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\AntiAbuse\Event;

use Application\DeskPRO\Entity\Person;
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
    protected $recommend_captcha;

    /**
     * @var bool
     */
    protected $recommend_lockout;

    /**
     * @var Response|null
     */
    protected $recommend_response;

    /**
     * @var bool
     */
    protected $require_response;

    /**
     * @var bool
     */
    protected $check_only;

    public function __construct($person_or_email, $ip = null)
    {
        if ($person_or_email instanceof Person) {
            $this->person = $person_or_email;
        } else {
            $this->email = $person_or_email;
        }

        $this->ip                 = $ip;
        $this->recommend_captcha  = false;
        $this->recommend_lockout  = false;
        $this->recommend_response = null;
        $this->require_response   = false;
        $this->check_only         = false;
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
     * @param Person|null $person
     */
    public function setPerson(Person $person)
    {
        $this->person = $person;
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
     * @param string|null $ip
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    /**
     * @return bool
     */
    public function isCaptchaRecommended()
    {
        return $this->recommend_captcha;
    }

    /**
     * @return bool
     */
    public function isLockoutRecommended()
    {
        return $this->recommend_lockout;
    }

    public function isResponseRecommended()
    {
        return null !== $this->getRecommendedResponse();
    }

    /**
     * @return bool
     */
    public function isResponseRequired()
    {
        return $this->require_response;
    }

    /**
     * @return Response|null
     */
    public function getRecommendedResponse()
    {
        return $this->recommend_response;
    }

    /**
     * @param null|Response $recommend_response
     */
    public function setResponse(Response $recommend_response)
    {
        $this->recommend_response = $recommend_response;
    }

    public function markResponseRequired()
    {
        $this->require_response = true;
    }

    public function markLockoutRecommended()
    {
        $this->recommend_lockout = true;
    }

    public function markCaptchaRecommended()
    {
        $this->recommend_captcha = true;
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
        return $this->check_only;
    }

    /**
     * If the event is marked as "check only" then it will ONLY CHECK the state of the abuse system
     * but it will NOT contribute to the logging/metrics that would influce the anti-abuse system.
     *
     * Without marking your event as "check only" then it will potentially add logs to the db that are then
     * used to calculate wether or not there is abuse. For this reason, tests will use an event that is
     * "check only" so that it can verify the state of the anti-abuse system.
     *
     * @return bool
     */
    public function markAsCheckOnly()
    {
        $this->check_only = true;
    }
}
