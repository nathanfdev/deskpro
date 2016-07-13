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

namespace DeskPRO\Bundle\AppBundle\Settings;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Model\TicketGrouping;
use DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\AccountInfo\AccountInfo;
use DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\AgentClientInfoSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\ChatSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\CRMSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\FeedbackSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\PublishSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\TasksSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\Tickets\Fields\TicketGroupFieldSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\Tickets\Fields\TicketOrderFieldSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\Tickets\TicketsSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\Core\Attachments\AttachmentsSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\Core\CoreSettings;
use Doctrine\ORM\EntityManager;
use Orb\Util\Arrays;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class AgentClientInfoSettingsResolver.
 */
class AgentClientInfoSettingsResolver extends AbstractBrandAwareSettingsResolver
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var Person
     */
    private $user;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param BrandAwareSettingsResolver $settingsResolver
     * @param TokenStorageInterface      $tokenStorage
     * @param EntityManager              $em
     */
    public function __construct(
        BrandAwareSettingsResolver $settingsResolver,
        TokenStorageInterface      $tokenStorage,
        EntityManager              $em
    ) {
        parent::__construct($settingsResolver);

        $this->tokenStorage = $tokenStorage;
        $this->em           = $em;
    }

    /**
     * @return AgentClientInfoSettings
     */
    public function getSettings()
    {
        $model = new AgentClientInfoSettings($this->getUser());
        $model
            ->setSettings($this->getCoreSettings())
            ->setAccountInfo($this->getAccountInfo())
            ->setTickets($this->getTicketsSettings())
            ->setChat($this->getChatSettings())
            ->setCrm($this->getCrmSettings())
            ->setFeedback($this->getFeedbackSettings())
            ->setPublish($this->getPublishSettings())
            ->setTasks($this->getTasksSettings())
        ;

        return $model;
    }

    /**
     * @return CoreSettings
     */
    public function getCoreSettings()
    {
        $model = new CoreSettings();
        $model
            ->setMultiLang($this->getSetting('core.enable_languages'))
            ->setBrands($this->em->getRepository(Brand::class)->countAll() > 1)
            ->setHelpdeskName($this->getSetting('core.deskpro_name'))
            ->setAttachments($this->getAttachmentsSettings())
        ;

        return $model;
    }

    /**
     * @return AttachmentsSettings
     */
    public function getAttachmentsSettings()
    {
        $whiteList = Arrays::removeEmptyString(explode(',', $this->getSetting('core.attach_agent_must_exts') ?: ''));
        $blackList = Arrays::removeEmptyString(explode(',', $this->getSetting('core.attach_agent_not_exts') ?: ''));

        $model = new AttachmentsSettings();

        $agentsSettings = $model->getAgents();
        $agentsSettings
            ->setMaxSize($this->getSetting('core.attach_agent_maxsize'))
            ->setWhitelist($whiteList)
            ->setBlacklist($blackList)
        ;

        return $model;
    }

    /**
     * @return TicketsSettings
     */
    public function getTicketsSettings()
    {
        $model = new TicketsSettings();
        $model
            ->setEnabled($this->getUser()->hasPerm('agent_tickets.use'))
            ->setRefCode($this->getSetting('core_tickets.use_ref'))
            ->setArchiving($this->getSetting('core_tickets.use_archive'))
        ;

        // set fields info
        $fields = $model->getFieldInfo();

        $product = $fields->getProduct();
        $product->setEnabled($this->getSetting('core.use_product'));
        $product->setDefaultId($this->getSetting('core.default_prod_id'));

        $category = $fields->getCategory();
        $category->setEnabled($this->getSetting('core.use_ticket_category'));
        $category->setDefaultId($this->getSetting('core.default_ticket_cat'));

        $workflow = $fields->getWorkflow();
        $workflow->setEnabled($this->getSetting('core.use_ticket_workflow'));
        $workflow->setDefaultId($this->getSetting('core.default_ticket_work'));

        $priority = $fields->getPriority();
        $priority->setEnabled($this->getSetting('core.use_ticket_priority'));
        $priority->setDefaultId($this->getSetting('core.default_ticket_pri'));

        /** @var \Application\DeskPRO\EntityRepository\CustomDefTicket $customDefRepo */
        $customDefRepo = $this->em->getRepository(CustomDefTicket::class);

        $custom = $fields->getCustom();
        $custom->setHasAny(count($customDefRepo->getEnabledFields()) > 0);

        // set billing info
        $billing = $model->getBilling();
        $billing->setEnabled($this->getSetting('core_tickets.enable_billing'));

        if ($billing->isEnabled()) {
            $billing->setCurrencyName($this->getSetting('core_tickets.billing_currency'));
        }

        // set timelog info
        $timelog = $model->getTimelog();
        $timelog->setEnabled($this->getSetting('core_tickets.enable_timelog'));

        // set order fields
        $orderFields = [
            'urgency',
            'date_created',
            'date_last_agent_reply',
            'date_last_user_reply',
            'date_last_reply',
            'date_user_waiting',
            'total_user_waiting',
        ];

        foreach ($orderFields as $orderField) {
            // "id" and "type" store the same value
            $model->addOrderByField(new TicketOrderFieldSettings($orderField, $orderField));
        }

        // set group fields
        $groupFields = [
            TicketGrouping::DEPARTMENT,
            TicketGrouping::AGENT,
            TicketGrouping::AGENT_TEAM,
            TicketGrouping::URGENCY,
            TicketGrouping::WAITING_TIME,
            TicketGrouping::ALL_WAITING_TIME,
            TicketGrouping::DATE_CREATED,
            TicketGrouping::LANGUAGE,
            TicketGrouping::ORGANIZATION,
            TicketGrouping::PERSON,
            // not supported by legacy ticket grouping counter
            // temporary disabled until we are using legacy filters
            // TicketGrouping::OPEN_TIME,
        ];

        foreach ($groupFields as $groupField) {
            // "id" and "type" store the same value
            $model->addGroupByField(new TicketGroupFieldSettings($groupField, $groupField, null));
        }

        foreach ($customDefRepo->getEnabledTopFields() as $customDef) {
            $model->addGroupByField(
                new TicketGroupFieldSettings(
                    TicketGrouping::CUSTOM_FIELD_COLUMN_PREFIX.'.'.$customDef->getId(),
                    TicketGrouping::CUSTOM_FIELD_COLUMN_PREFIX,
                    $customDef->getId()
                )
            );
        }

        return $model;
    }

    /**
     * @return ChatSettings
     */
    public function getChatSettings()
    {
        $model = new ChatSettings();
        $model->setEnabled($this->getSetting('core.apps_chat') && $this->getUser()->hasPerm('agent_chat.use'));

        return $model;
    }

    /**
     * @return CRMSettings
     */
    public function getCrmSettings()
    {
        $model = new CRMSettings();
        $model->setEnabled($this->getUser()->hasPerm('agent_people.use'));

        return $model;
    }

    /**
     * @return FeedbackSettings
     */
    public function getFeedbackSettings()
    {
        $model = new FeedbackSettings();
        $model->setEnabled($this->getUser()->hasPerm('core.apps_feedback'));

        return $model;
    }

    /**
     * @return PublishSettings
     */
    public function getPublishSettings()
    {
        $model = new PublishSettings();
        $model->setEnabled($this->getUser()->hasPerm('core.apps_kb'));

        return $model;
    }

    /**
     * @return TasksSettings
     */
    public function getTasksSettings()
    {
        $model = new TasksSettings();
        $model->setEnabled($this->getUser()->hasPerm('core.apps_tasks'));

        return $model;
    }

    /**
     * @return AccountInfo
     */
    public function getAccountInfo()
    {
        $user          = $this->getUser();
        $signatureHtml = $user->getHelper('Agent')->getSignatureHtml();
        $accountInfo   = new AccountInfo();
        $accountInfo
            ->setLanguage($user->getLanguage())
            ->setTimezone($user->getTimezone())
            ->setSignatureHtml($signatureHtml);

        return $accountInfo;
    }

    /**
     * @return Person
     */
    private function getUser()
    {
        if ($this->user === null) {
            /** @var Person $user */
            $user = $this->tokenStorage->getToken()->getUser();

            $user->loadHelper('Agent');
            $user->loadHelper('AgentTeam');
            $user->loadHelper('AgentPermissions');
            $user->loadHelper('PermissionsManager');

            $this->user = $user;
        }

        return $this->user;
    }
}
