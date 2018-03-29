<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\Portal;

use DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\RateLimitOptionsGroup;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class PortalUserRateLimit.
 */
class PortalUserRateLimit
{
    /**
     * Settings for login.
     *
     * @Assert\Valid
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\RateLimitOptionsGroup")
     *
     * @var RateLimitOptionsGroup
     */
    private $loginSettings;

    /**
     * Limits for ticket submitting.
     *
     * @Assert\Valid
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\RateLimitOptionsGroup")
     *
     * @var RateLimitOptionsGroup
     */
    private $submitTicket;

    /**
     * Limits for feedback submitting.
     *
     * @Assert\Valid
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\RateLimitOptionsGroup")
     *
     * @var RateLimitOptionsGroup
     */
    private $submitFeedback;

    /**
     * Limits for comments submitting.
     *
     * @Assert\Valid
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\RateLimitOptionsGroup")
     *
     * @var RateLimitOptionsGroup
     */
    private $submitComment;

    /**
     * Limits for attachment submitting.
     *
     * @Assert\Valid
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\RateLimitOptionsGroup")
     *
     * @var RateLimitOptionsGroup
     */
    private $uploadAttachment;

    /**
     * Limits for sharing content.
     *
     * @Assert\Valid
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\RateLimitOptionsGroup")
     *
     * @var RateLimitOptionsGroup
     */
    private $shareContent;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->loginSettings    = new RateLimitOptionsGroup();
        $this->submitTicket     = new RateLimitOptionsGroup();
        $this->submitFeedback   = new RateLimitOptionsGroup();
        $this->submitComment    = new RateLimitOptionsGroup();
        $this->uploadAttachment = new RateLimitOptionsGroup();
        $this->shareContent     = new RateLimitOptionsGroup();
    }

    /**
     * @return RateLimitOptionsGroup
     */
    public function getLoginSettings()
    {
        return $this->loginSettings;
    }

    /**
     * @return RateLimitOptionsGroup
     */
    public function getSubmitTicket()
    {
        return $this->submitTicket;
    }

    /**
     * @return RateLimitOptionsGroup
     */
    public function getSubmitFeedback()
    {
        return $this->submitFeedback;
    }

    /**
     * @return RateLimitOptionsGroup
     */
    public function getSubmitComment()
    {
        return $this->submitComment;
    }

    /**
     * @return RateLimitOptionsGroup
     */
    public function getUploadAttachment()
    {
        return $this->uploadAttachment;
    }

    /**
     * @return RateLimitOptionsGroup
     */
    public function getShareContent()
    {
        return $this->shareContent;
    }
}
