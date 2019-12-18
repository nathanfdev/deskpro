<?php

namespace DeskPRO\Bundle\MessengerBundle\Settings\Model;

use JMS\Serializer\Annotation as JMS;

/**
 * Class PreChatFormCustomField.
 */
class PreChatFormCustomField
{
    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $enabled;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $required;

    /**
     * @JMS\Type("integer")
     * @JMS\SerializedName("displayOrder")
     *
     * @var int
     */
    private $displayOrder;

    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $id;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getDisplayOrder()
    {
        return $this->displayOrder;
    }

    /**
     * @param int $displayOrder
     *
     * @return $this
     */
    public function setDisplayOrder($displayOrder)
    {
        $this->displayOrder = $displayOrder;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * @param bool $required
     *
     * @return $this
     */
    public function setRequired($required)
    {
        $this->required = $required;

        return $this;
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
        $this->enabled = $enabled;

        return $this;
    }
}
