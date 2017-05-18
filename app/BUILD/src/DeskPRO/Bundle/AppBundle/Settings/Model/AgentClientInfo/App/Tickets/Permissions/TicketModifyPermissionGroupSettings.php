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
