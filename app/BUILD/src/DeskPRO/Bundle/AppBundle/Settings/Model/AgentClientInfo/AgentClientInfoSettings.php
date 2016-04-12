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

namespace DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo;

use DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\AccountInfo\AccountInfo;
use DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\ChatSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\CRMSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\FeedbackSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\PublishSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\TasksSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\Tickets\TicketsSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\Core\CoreSettings;
use JMS\Serializer\Annotation as JMS;

/**
 * Class AgentClientInfoSettings.
 */
class AgentClientInfoSettings
{
    /**
     * @var CoreSettings
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\Core\CoreSettings")
     */
    private $settings;

    /**
     * @var AccountInfo
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\AccountInfo\AccountInfo")
     */
    private $accountInfo;

    /**
     * @var ChatSettings
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\ChatSettings")
     */
    private $chat;

    /**
     * @var CRMSettings
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\CRMSettings")
     */
    private $crm;

    /**
     * @var FeedbackSettings
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\FeedbackSettings")
     */
    private $feedback;

    /**
     * @var PublishSettings
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\PublishSettings")
     */
    private $publish;

    /**
     * @var TasksSettings
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\TasksSettings")
     */
    private $tasks;

    /**
     * @var TicketsSettings
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\Tickets\TicketsSettings")
     */
    private $tickets;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->accountInfo = new AccountInfo();
        $this->settings    = new CoreSettings();
        $this->chat        = new ChatSettings();
        $this->crm         = new CRMSettings();
        $this->feedback    = new FeedbackSettings();
        $this->publish     = new PublishSettings();
        $this->tasks       = new TasksSettings();
        $this->tickets     = new TicketsSettings();
    }

    /**
     * @return CoreSettings
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param CoreSettings $settings
     *
     * @return $this
     */
    public function setSettings(CoreSettings $settings)
    {
        $this->settings = $settings;

        return $this;
    }

    /**
     * @return AccountInfo
     */
    public function getAccountInfo()
    {
        return $this->accountInfo;
    }

    /**
     * @param AccountInfo $accountInfo
     *
     * @return $this
     */
    public function setAccountInfo(AccountInfo $accountInfo)
    {
        $this->accountInfo = $accountInfo;

        return $this;
    }

    /**
     * @return ChatSettings
     */
    public function getChat()
    {
        return $this->chat;
    }

    /**
     * @param ChatSettings $chat
     *
     * @return $this
     */
    public function setChat(ChatSettings $chat)
    {
        $this->chat = $chat;

        return $this;
    }

    /**
     * @return CRMSettings
     */
    public function getCrm()
    {
        return $this->crm;
    }

    /**
     * @param CRMSettings $crm
     *
     * @return $this
     */
    public function setCrm(CRMSettings $crm)
    {
        $this->crm = $crm;

        return $this;
    }

    /**
     * @return FeedbackSettings
     */
    public function getFeedback()
    {
        return $this->feedback;
    }

    /**
     * @param FeedbackSettings $feedback
     *
     * @return $this
     */
    public function setFeedback(FeedbackSettings $feedback)
    {
        $this->feedback = $feedback;

        return $this;
    }

    /**
     * @return PublishSettings
     */
    public function getPublish()
    {
        return $this->publish;
    }

    /**
     * @param PublishSettings $publish
     *
     * @return $this
     */
    public function setPublish(PublishSettings $publish)
    {
        $this->publish = $publish;

        return $this;
    }

    /**
     * @return TasksSettings
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * @param TasksSettings $tasks
     *
     * @return $this
     */
    public function setTasks(TasksSettings $tasks)
    {
        $this->tasks = $tasks;

        return $this;
    }

    /**
     * @return TicketsSettings
     */
    public function getTickets()
    {
        return $this->tickets;
    }

    /**
     * @param TicketsSettings $tickets
     *
     * @return $this
     */
    public function setTickets(TicketsSettings $tickets)
    {
        $this->tickets = $tickets;

        return $this;
    }
}
