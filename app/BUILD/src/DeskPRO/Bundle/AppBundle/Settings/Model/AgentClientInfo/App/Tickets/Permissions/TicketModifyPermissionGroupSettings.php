<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\Tickets\Permissions;

use JMS\Serializer\Annotation as JMS;

/**
 * Class TicketModifyPermissionGroupSettings.
 */
class TicketModifyPermissionGroupSettings
{
    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $view;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $reply;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $modify;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $modifyMessages;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $delete;

    /**
     * @return bool
     */
    public function isView()
    {
        return $this->view;
    }

    /**
     * @param bool $view
     *
     * @return $this
     */
    public function setView($view)
    {
        $this->view = $view;

        return $this;
    }

    /**
     * @return bool
     */
    public function isReply()
    {
        return $this->reply;
    }

    /**
     * @param bool $reply
     *
     * @return $this
     */
    public function setReply($reply)
    {
        $this->reply = $reply;

        return $this;
    }

    /**
     * @return bool
     */
    public function isModify()
    {
        return $this->modify;
    }

    /**
     * @param bool $modify
     *
     * @return $this
     */
    public function setModify($modify)
    {
        $this->modify = $modify;

        return $this;
    }

    /**
     * @return bool
     */
    public function isModifyMessages()
    {
        return $this->modifyMessages;
    }

    /**
     * @param bool $modifyMessages
     *
     * @return $this
     */
    public function setModifyMessages($modifyMessages)
    {
        $this->modifyMessages = $modifyMessages;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDelete()
    {
        return $this->delete;
    }

    /**
     * @param bool $delete
     *
     * @return $this
     */
    public function setDelete($delete)
    {
        $this->delete = $delete;

        return $this;
    }
}
