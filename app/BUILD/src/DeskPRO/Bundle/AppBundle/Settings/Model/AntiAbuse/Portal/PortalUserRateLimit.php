<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

use DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\RateLimitGroup;
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
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\RateLimitGroup")
     *
     * @var RateLimitGroup
     */
    private $loginSettings;

    /**
     * Limits for ticket submitting.
     *
     * @Assert\Valid
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\RateLimitGroup")
     *
     * @var RateLimitGroup
     */
    private $submitTicket;

    /**
     * Limits for feedback submitting.
     *
     * @Assert\Valid
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\RateLimitGroup")
     *
     * @var RateLimitGroup
     */
    private $submitFeedback;

    /**
     * Limits for comments submitting.
     *
     * @Assert\Valid
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\RateLimitGroup")
     *
     * @var RateLimitGroup
     */
    private $submitComment;

    /**
     * Limits for attachment submitting.
     *
     * @Assert\Valid
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\RateLimitGroup")
     *
     * @var RateLimitGroup
     */
    private $uploadAttachment;

    /**
     * Limits for sharing content.
     *
     * @Assert\Valid
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\RateLimitGroup")
     *
     * @var RateLimitGroup
     */
    private $shareContent;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->loginSettings    = new RateLimitGroup();
        $this->submitTicket     = new RateLimitGroup();
        $this->submitFeedback   = new RateLimitGroup();
        $this->submitComment    = new RateLimitGroup();
        $this->uploadAttachment = new RateLimitGroup();
        $this->shareContent     = new RateLimitGroup();
    }

    /**
     * @return RateLimitGroup
     */
    public function getLoginSettings()
    {
        return $this->loginSettings;
    }

    /**
     * @return RateLimitGroup
     */
    public function getSubmitTicket()
    {
        return $this->submitTicket;
    }

    /**
     * @return RateLimitGroup
     */
    public function getSubmitFeedback()
    {
        return $this->submitFeedback;
    }

    /**
     * @return RateLimitGroup
     */
    public function getSubmitComment()
    {
        return $this->submitComment;
    }

    /**
     * @return RateLimitGroup
     */
    public function getUploadAttachment()
    {
        return $this->uploadAttachment;
    }

    /**
     * @return RateLimitGroup
     */
    public function getShareContent()
    {
        return $this->shareContent;
    }
}
