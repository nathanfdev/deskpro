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
