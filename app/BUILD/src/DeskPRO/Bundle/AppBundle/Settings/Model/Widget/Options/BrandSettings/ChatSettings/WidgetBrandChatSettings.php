<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\BrandSettings\ChatSettings;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\GroupSequenceProviderInterface;

/**
 * Class WidgetBrandChatSettings.
 *
 * @Assert\GroupSequenceProvider
 */
class WidgetBrandChatSettings implements GroupSequenceProviderInterface
{
    const BEGIN_MODE_CONVERSATION = 'conversation';
    const BEGIN_MODE_FORM         = 'form';

    const SELECT_DEFAULT = 'default';
    const SELECT_CUSTOM  = 'custom';

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $requestUserInfo = false;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $proactive = true;

    /**
     * @var WidgetBrandChatPopupSettings
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\BrandSettings\ChatSettings\WidgetBrandChatPopupSettings")
     * @Assert\Valid()
     */
    private $popup;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $beginMode = self::BEGIN_MODE_FORM;

    /**
     * @var int
     *
     * @JMS\Type("integer")
     * @Assert\NotNull(groups={"DefaultDepartment"})
     */
    private $defaultDepartment;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @Assert\NotBlank(groups={"Common"})
     */
    private $selectDepartment = self::SELECT_CUSTOM;

    /**
     * @var int
     *
     * @JMS\Type("integer")
     * @Assert\GreaterThanOrEqual(value=30, groups={"Common"})
     */
    private $waitingTimeout = 150;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $requiredName = false;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $requiredEmail = false;

    /**
     * @var WidgetBrandChatCustomField[]
     *
     * @Assert\Valid()
     */
    private $customFields;

    /**
     * @var int[]
     */
    private $userGroups;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->popup        = new WidgetBrandChatPopupSettings();
        $this->customFields = new ArrayCollection();
        $this->userGroups   = new ArrayCollection();
    }

    /**
     * @return bool
     */
    public function isRequestUserInfo()
    {
        return $this->requestUserInfo;
    }

    /**
     * @param bool $requestUserInfo
     *
     * @return $this
     */
    public function setRequestUserInfo($requestUserInfo)
    {
        $this->requestUserInfo = (bool) $requestUserInfo;

        return $this;
    }

    /**
     * @return bool
     */
    public function isProactive()
    {
        return $this->proactive;
    }

    /**
     * @param bool $proactive
     *
     * @return $this
     */
    public function setProactive($proactive)
    {
        $this->proactive = (bool) $proactive;

        return $this;
    }

    /**
     * @return WidgetBrandChatPopupSettings
     */
    public function getPopup()
    {
        return $this->popup;
    }

    /**
     * @param WidgetBrandChatPopupSettings $popup
     *
     * @return $this
     */
    public function setPopup(WidgetBrandChatPopupSettings $popup)
    {
        $this->popup = $popup;

        return $this;
    }

    /**
     * @return string
     */
    public function getBeginMode()
    {
        return $this->beginMode;
    }

    /**
     * @param string $beginMode
     *
     * @return $this
     */
    public function setBeginMode($beginMode)
    {
        $this->beginMode = $beginMode;

        return $this;
    }

    /**
     * @return int
     */
    public function getWaitingTimeout()
    {
        return $this->waitingTimeout;
    }

    /**
     * @param int $waitingTimeout
     *
     * @return $this
     */
    public function setWaitingTimeout($waitingTimeout)
    {
        $this->waitingTimeout = $waitingTimeout;

        return $this;
    }

    /**
     * @return int
     */
    public function getDefaultDepartment()
    {
        return $this->defaultDepartment;
    }

    /**
     * @param int $defaultDepartment
     *
     * @return $this
     */
    public function setDefaultDepartment($defaultDepartment)
    {
        $this->defaultDepartment = $defaultDepartment;

        return $this;
    }

    /**
     * @return string
     */
    public function getSelectDepartment()
    {
        return $this->selectDepartment;
    }

    /**
     * @param string $selectDepartment
     *
     * @return $this
     */
    public function setSelectDepartment($selectDepartment)
    {
        $this->selectDepartment = $selectDepartment;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRequiredName()
    {
        return $this->requiredName;
    }

    /**
     * @param bool $requiredName
     *
     * @return $this
     */
    public function setRequiredName($requiredName)
    {
        $this->requiredName = $requiredName;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRequiredEmail()
    {
        return $this->requiredEmail;
    }

    /**
     * @param bool $requiredEmail
     *
     * @return $this
     */
    public function setRequiredEmail($requiredEmail)
    {
        $this->requiredEmail = $requiredEmail;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupSequence()
    {
        $groups = ['Common'];
        if ($this->selectDepartment === self::SELECT_DEFAULT) {
            $groups[] = 'DefaultDepartment';
        }

        return $groups;
    }

    /**
     * @return WidgetBrandChatCustomField[]
     */
    public function getCustomFields()
    {
        return $this->customFields;
    }

    /**
     * @param int $fieldId
     *
     * @return ArrayCollection|\Doctrine\Common\Collections\Collection
     */
    public function getCustomField($fieldId)
    {
        return $this->customFields->filter(function (WidgetBrandChatCustomField $customField) use ($fieldId) {
            return $customField->getId() === $fieldId;
        })->first();
    }

    /**
     * @param WidgetBrandChatCustomField $customField
     *
     * @return $this
     */
    public function addCustomField(WidgetBrandChatCustomField $customField)
    {
        $this->customFields->add($customField);

        return $this;
    }

    /**
     * @param WidgetBrandChatCustomField $customField
     *
     * @return $this
     */
    public function removeCustomField(WidgetBrandChatCustomField $customField)
    {
        $this->customFields->removeElement($customField);

        return $this;
    }

    /**
     * @param ArrayCollection|WidgetBrandChatCustomField[] $customFields
     *
     * @return $this
     */
    public function setCustomFields(ArrayCollection $customFields)
    {
        $this->customFields = $customFields;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getUserGroups()
    {
        return $this->userGroups;
    }

    /**
     * @param ArrayCollection $userGroups
     *
     * @return $this
     */
    public function setUserGroups(ArrayCollection $userGroups)
    {
        $this->userGroups = $userGroups;

        return $this;
    }

    /**
     * @param $userGroup
     *
     * @return $this
     */
    public function addUserGroup($userGroup)
    {
        $this->userGroups->add($userGroup);

        return $this;
    }

    /**
     * @param $userGroup
     *
     * @return $this
     */
    public function removeUserGroup($userGroup)
    {
        $this->userGroups->removeElement($userGroup);

        return $this;
    }
}
