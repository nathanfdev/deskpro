<?php

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
     * @var DateSettings
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\Core\DateSettings")
     */
    private $date;

    /**
     * @var array
     * @JMS\Type("array<string>")
     */
    private $features = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->attachments = new AttachmentsSettings();
        $this->date        = new DateSettings();
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
     *
     * @return $this
     */
    public function setAttachments(AttachmentsSettings $attachments)
    {
        $this->attachments = $attachments;

        return $this;
    }

    /**
     * @return DateSettings
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param DateSettings $date
     *
     * @return $this
     */
    public function setDate(DateSettings $date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return array
     */
    public function getFeatures()
    {
        return $this->features;
    }

    /**
     * @param array $features
     */
    public function setFeatures($features)
    {
        $this->features = $features;
    }
}
