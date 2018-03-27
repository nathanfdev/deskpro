<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model;

/**
 * Class IMSettings.
 */
class IMSettings
{
    /**
     * @var array
     */
    private $chatsOrder;

    /**
     * @return array
     */
    public function getChatsOrder()
    {
        return $this->chatsOrder;
    }

    /**
     * @param array| $chatsOrder
     *
     * @return $this
     */
    public function setChatsOrder(array $chatsOrder)
    {
        $this->chatsOrder = $chatsOrder;

        return $this;
    }
}
