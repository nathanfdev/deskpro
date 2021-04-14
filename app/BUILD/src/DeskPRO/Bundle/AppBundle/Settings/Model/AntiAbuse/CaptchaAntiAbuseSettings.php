<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse;

use JMS\Serializer\Annotation as JMS;

/**
 * Class CaptchaAntiAbuseSettings.
 */
class CaptchaAntiAbuseSettings
{
    const TYPE_BASED_RATE_LIMITS = false;
    const TYPE_GUESTS            = 'guests';
    const TYPE_EVERYONE          = 'everyone';

    const RecaptchaVersion2 = '2';
    const RecaptchaVersion3 = '3';

    /**
     * True if set to use Google`s recaptcha.
     *
     * @var bool
     *
     * @JMS\Type("integer")
     */
    private $useRecaptcha2 = false;

    /**
     * Recaptcha key.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    private $recaptcha2SiteKey = '';

    /**
     * Recaptcha key.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    private $recaptcha2SecretKey = '';

    /**
     * Recaptcha version.
     *
     * @var string
     *
     * @JMS\Type("integer")
     */
    private $recaptchaVersion = '';

    /**
     * Use it for tickets.
     *
     * @var bool|string
     *
     * @JMS\Type("string")
     */
    private $tickets = self::TYPE_BASED_RATE_LIMITS;

    /**
     * Use it for comments.
     *
     * @var bool|string
     *
     * @JMS\Type("string")
     */
    private $comments = self::TYPE_BASED_RATE_LIMITS;

    /**
     * Use it for community topics.
     *
     * @var bool|string
     *
     * @JMS\Type("string")
     */
    private $community = self::TYPE_BASED_RATE_LIMITS;

    /**
     * Use it for registration.
     *
     * @var bool|string
     *
     * @JMS\Type("string")
     */
    private $register = self::TYPE_BASED_RATE_LIMITS;

    /**
     * Use it for sharing.
     *
     * @var bool|string
     *
     * @JMS\Type("string")
     */
    private $sharing = self::TYPE_BASED_RATE_LIMITS;

    /**
     * @return mixed
     */
    public function getUseRecaptcha2()
    {
        return $this->useRecaptcha2;
    }

    /**
     * @param mixed $useRecaptcha2
     *
     * @return $this
     */
    public function setUseRecaptcha2($useRecaptcha2)
    {
        $this->useRecaptcha2 = $useRecaptcha2;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRecaptcha2SiteKey()
    {
        return $this->recaptcha2SiteKey;
    }

    /**
     * @param mixed $recaptcha2SiteKey
     *
     * @return $this
     */
    public function setRecaptcha2SiteKey($recaptcha2SiteKey)
    {
        $this->recaptcha2SiteKey = $recaptcha2SiteKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getRecaptcha2SecretKey()
    {
        return $this->recaptcha2SecretKey;
    }

    /**
     * @return string
     */
    public function getRecaptchaVersion()
    {
        return $this->recaptchaVersion;
    }

    /**
     * @param string $recaptcha2SecretKey
     *
     * @return $this
     */
    public function setRecaptcha2SecretKey($recaptcha2SecretKey)
    {
        $this->recaptcha2SecretKey = $recaptcha2SecretKey;

        return $this;
    }

    /**
     * @param string $recaptchaVersion
     *
     * @return $this
     */
    public function setRecaptchaVersion($recaptchaVersion)
    {
        $this->recaptchaVersion = $recaptchaVersion;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTickets()
    {
        return $this->tickets;
    }

    /**
     * @param mixed $tickets
     *
     * @return $this
     */
    public function setTickets($tickets)
    {
        $this->tickets = $tickets;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param mixed $comments
     *
     * @return $this
     */
    public function setComments($comments)
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCommunity()
    {
        return $this->community;
    }

    /**
     * @param mixed $community
     *
     * @return $this
     */
    public function setCommunity($community)
    {
        $this->community = $community;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRegister()
    {
        return $this->register;
    }

    /**
     * @param mixed $register
     *
     * @return $this
     */
    public function setRegister($register)
    {
        $this->register = $register;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSharing()
    {
        return $this->sharing;
    }

    /**
     * @param mixed $sharing
     *
     * @return $this
     */
    public function setSharing($sharing)
    {
        $this->sharing = $sharing;

        return $this;
    }
}
