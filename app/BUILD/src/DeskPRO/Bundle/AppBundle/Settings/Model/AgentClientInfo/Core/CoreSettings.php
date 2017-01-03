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

namespace DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\Core;

use DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\Core\Attachments\AttachmentsSettings;
use JMS\Serializer\Annotation as JMS;

/**
 * Class CoreSettings.
 */
class CoreSettings
{
    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $multiLang = false;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $brands = false;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $helpdeskName;

    /**
     * @var AttachmentsSettings
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\Core\Attachments\AttachmentsSettings")
     */
    private $attachments;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->attachments = new AttachmentsSettings();
    }

    /**
     * @return bool
     */
    public function getMultiLang()
    {
        return $this->multiLang;
    }

    /**
     * @param bool $multiLang
     *
     * @return $this
     */
    public function setMultiLang($multiLang)
    {
        $this->multiLang = $multiLang;

        return $this;
    }

    /**
     * @return bool
     */
    public function isBrands()
    {
        return $this->brands;
    }

    /**
     * @param bool $brands
     *
     * @return $this
     */
    public function setBrands($brands)
    {
        $this->brands = $brands;

        return $this;
    }

    /**
     * @return string
     */
    public function getHelpdeskName()
    {
        return $this->helpdeskName;
    }

    /**
     * @param string $helpdeskName
     *
     * @return $this
     */
    public function setHelpdeskName($helpdeskName)
    {
        $this->helpdeskName = $helpdeskName;

        return $this;
    }

    /**
     * @return AttachmentsSettings
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * @param AttachmentsSettings $attachments
     */
    public function setAttachments(AttachmentsSettings $attachments)
    {
        $this->attachments = $attachments;
    }
}
