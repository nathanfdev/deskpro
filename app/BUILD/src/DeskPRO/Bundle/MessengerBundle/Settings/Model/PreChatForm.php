<?php

namespace DeskPRO\Bundle\MessengerBundle\Settings\Model;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;

/**
 * Class PreChatForm.
 */
class PreChatForm
{
    /**
     * Are tickets enabled.
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $enabled = false;

    /**
     * Is name enabled as pre-chat field?
     *
     * @JMS\Type("boolean")
     * @JMS\SerializedName("isNameEnabled")
     *
     * @var bool
     */
    private $isNameEnabled = true;

    /**
     * Is email enabled as pre-chat field?
     *
     * @JMS\Type("boolean")
     * @JMS\SerializedName("isEmailEnabled")
     *
     * @var bool
     */
    private $isEmailEnabled = true;

    /**
     * Is name required as pre-chat field?
     *
     * @JMS\Type("boolean")
     * @JMS\SerializedName("isNameRequired")
     *
     * @var bool
     */
    private $isNameRequired = false;

    /**
     * Is name required as pre-chat field?
     *
     * @JMS\Type("boolean")
     * @JMS\SerializedName("isDepartmentSelectable")
     *
     * @var bool
     */
    private $isDepartmentSelectable = true;

    /**
     * @var array
     * @JMS\Type("map<DeskPRO\Bundle\MessengerBundle\Settings\Model\PreChatFormCustomField>")
     */
    private $fields;

    /**
     * Is email required as pre-chat field?.
     *
     * @JMS\Type("boolean")
     * @JMS\SerializedName("isEmailRequired")
     *
     * @var bool
     */
    private $isEmailRequired = false;

    public function __construct()
    {
        $this->fields = new ArrayCollection();
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
    public function isNameEnabled()
    {
        return $this->isNameEnabled;
    }

    /**
     * @param bool $isNameEnabled
     *
     * @return $this
     */
    public function setIsNameEnabled($isNameEnabled)
    {
        $this->isNameEnabled = $isNameEnabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEmailEnabled()
    {
        return $this->isEmailEnabled;
    }

    /**
     * @param bool $isEmailEnabled
     *
     * @return $this
     */
    public function setIsEmailEnabled($isEmailEnabled)
    {
        $this->isEmailEnabled = $isEmailEnabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function isNameRequired()
    {
        return $this->isNameRequired;
    }

    /**
     * @param bool $isNameRequired
     *
     * @return $this
     */
    public function setIsNameRequired($isNameRequired)
    {
        $this->isNameRequired = $isNameRequired;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEmailRequired()
    {
        return $this->isEmailRequired;
    }

    /**
     * @param bool $isEmailRequired
     *
     * @return $this
     */
    public function setIsEmailRequired($isEmailRequired)
    {
        $this->isEmailRequired = $isEmailRequired;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDepartmentSelectable()
    {
        return $this->isDepartmentSelectable;
    }

    /**
     * @param bool $isDepartmentSelectable
     *
     * @return $this
     */
    public function setIsDepartmentSelectable($isDepartmentSelectable)
    {
        $this->isDepartmentSelectable = $isDepartmentSelectable;

        return $this;
    }

    /**
     * @return PreChatFormCustomField[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param PreChatFormCustomField[] $fields
     *
     * @return $this
     */
    public function setFields(ArrayCollection $fields)
    {
        foreach ($fields as $f) {
            $this->fields[$f->getId()] = $f;
        }

        return $this;
    }
}
