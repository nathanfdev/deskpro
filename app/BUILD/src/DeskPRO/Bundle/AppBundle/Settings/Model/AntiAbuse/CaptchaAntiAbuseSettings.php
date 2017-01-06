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
     * Use it for feedback.
     *
     * @var bool|string
     *
     * @JMS\Type("string")
     */
    private $feedback = self::TYPE_BASED_RATE_LIMITS;

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
    public function getFeedback()
    {
        return $this->feedback;
    }

    /**
     * @param mixed $feedback
     *
     * @return $this
     */
    public function setFeedback($feedback)
    {
        $this->feedback = $feedback;

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
