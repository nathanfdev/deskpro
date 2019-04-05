<?php

namespace DeskPRO\Bundle\AppBundle\Settings;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\DataService\Tickets\TicketStatusDataService;
use DeskPRO\Bundle\AppBundle\Features\BetaFeatureInterface;
use DeskPRO\Bundle\AppBundle\Features\FeaturesCollection;
use DeskPRO\Bundle\AppBundle\Model\TicketGrouping;
use DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\AccountInfo\AccountInfo;
use DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\AgentClientInfoSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\ChatSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\CRM\CRMSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\FeedbackSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\PublishSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\TasksSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\Tickets\Fields\TicketGroupFieldSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\Tickets\Fields\TicketOrderFieldSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\Tickets\TicketsSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\Core\Attachments\AttachmentsSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\Core\CoreSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\Core\DateSettings;
use DeskPRO\Component\Util\ListUtils;
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
     * @var TicketStatusDataService
     */
    private $ticketStatusDataService;

    /**
     * @var FeaturesCollection
     */
    protected $featuresCollection;

    /**
     * AgentClientInfoSettingsResolver constructor.
     *
     * @param BrandAwareSettingsResolver $settingsResolver
     * @param TokenStorageInterface      $tokenStorage
     * @param EntityManager              $em
     * @param TicketStatusDataService    $ticketStatusDataService
     * @param FeaturesCollection         $featuresCollection
     */
    public function __construct(
        BrandAwareSettingsResolver $settingsResolver,
        TokenStorageInterface      $tokenStorage,
        EntityManager              $em,
        TicketStatusDataService    $ticketStatusDataService,
        FeaturesCollection         $featuresCollection
    ) {
        parent::__construct($settingsResolver);

        $this->tokenStorage            = $tokenStorage;
        $this->em                      = $em;
        $this->ticketStatusDataService = $ticketStatusDataService;
        $this->featuresCollection      = $featuresCollection;
    }

    /**
     * @return AgentClientInfoSettings
     */
    public function getSettings()
    {
        $model = new AgentClientInfoSettings();
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
            ->setDate($this->getDateSettings())
            ->setFeatures(ListUtils::filterMap($this->featuresCollection, function (BetaFeatureInterface $f) {
                if ($this->featuresCollection->isFeatureEnabled($f->getId())) {
                    return $f->getId();
                }
            }))
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
     * @return DateSettings
     */
    public function getDateSettings()
    {
        $model = new DateSettings();
        $model
            ->setFullTime($this->getSetting('core.date_fulltime'))
            ->setFull($this->getSetting('core.date_full'))
            ->setDay($this->getSetting('core.date_day'))
            ->setDayShort($this->getSetting('core.date_day_short'))
            ->setTime($this->getSetting('core.date_time'))
            ->setDisableRelativeTimes((bool) $this->getSetting('core.disable_relative_times'))
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
            ->setEnabled($this->hasPerm('agent_tickets.use'))
            ->setRefCode($this->getSetting('core_tickets.use_ref'))
            ->setArchiving($this->getSetting('core_tickets.use_archive'))
        ;

        $model->setTicketStatuses(array_values($this->ticketStatusDataService->getTopLevelStatuses(true)));

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
            'status',
            TicketGrouping::DEPARTMENT,
            TicketGrouping::AGENT,
            TicketGrouping::AGENT_TEAM,
            TicketGrouping::URGENCY,
            TicketGrouping::DATE_CREATED,
            TicketGrouping::LANGUAGE,
        ];

        foreach ($groupFields as $groupField) {
            // "id" and "type" store the same value
            $model->addGroupByField(new TicketGroupFieldSettings($groupField, $groupField, null));
        }

        foreach ($customDefRepo->getEnabledTopFields() as $customDef) {
            if ($customDef->isChoiceType()) {
                $model->addGroupByField(
                    new TicketGroupFieldSettings(
                        TicketGrouping::CUSTOM_FIELD_COLUMN_PREFIX.'.'.$customDef->getId(),
                        TicketGrouping::CUSTOM_FIELD_COLUMN_PREFIX,
                        $customDef->getId()
                    )
                );
            }
        }

        $permissions = $model->getPermissions();
        $permissions
            ->setCreate($this->hasPerm('agent_tickets.create'))
            ->setCreateLabels($this->hasPerm('agent_tickets.create_labels'))
            ->setReplyMass($this->hasPerm('agent_tickets.reply_mass'))
            ->setModifySetArchived($this->hasPerm('agent_tickets.modify_set_archived'))
        ;

        $modifyOwnPermissions = $model->getPermissions()->getModify()->getOwn();
        $modifyOwnPermissions
            ->setView(true)
            ->setReply($this->hasPerm('agent_tickets.reply_own'))
            ->setModify($this->hasPerm('agent_tickets.modify_own'))
            ->setModifyMessages($this->hasPerm('agent_tickets.modify_messages_own'))
            ->setDelete($this->hasPerm('agent_tickets.delete_own'))
        ;

        $modifyFollowingPermissions = $model->getPermissions()->getModify()->getFollowing();
        $modifyFollowingPermissions
            ->setView(true)
            ->setReply($this->hasPerm('agent_tickets.reply_to_followed'))
            ->setModify($this->hasPerm('agent_tickets.modify_followed'))
            ->setModifyMessages($this->hasPerm('agent_tickets.modify_messages_followed'))
            ->setDelete($this->hasPerm('agent_tickets.delete_followed'))
        ;

        $modifyUnassignedPermissions = $model->getPermissions()->getModify()->getUnassing();
        $modifyUnassignedPermissions
            ->setView($this->hasPerm('agent_tickets.view_unassigned'))
            ->setReply($this->hasPerm('agent_tickets.reply_unassigned'))
            ->setModify($this->hasPerm('agent_tickets.modify_unassigned'))
            ->setModifyMessages($this->hasPerm('agent_tickets.modify_messages_unassigned'))
            ->setDelete($this->hasPerm('agent_tickets.delete_unassigned'))
        ;

        $modifyOthersPermissions = $model->getPermissions()->getModify()->getOthers();
        $modifyOthersPermissions
            ->setView($this->hasPerm('agent_tickets.view_others'))
            ->setReply($this->hasPerm('agent_tickets.reply_others'))
            ->setModify($this->hasPerm('agent_tickets.modify_others'))
            ->setModifyMessages($this->hasPerm('agent_tickets.modify_messages_others'))
            ->setDelete($this->hasPerm('agent_tickets.delete_others'))
        ;

        return $model;
    }

    /**
     * @return ChatSettings
     */
    public function getChatSettings()
    {
        $model = new ChatSettings();
        $model->setEnabled($this->getSetting('core.apps_chat') && $this->hasPerm('agent_chat.use'));

        $permissions = $model->getPermissions();
        $permissions
            ->setCreateLabels($this->hasPerm('agent_chat.create_labels'))
            ->setViewOthers($this->hasPerm('agent_chat.view_others'))
            ->setViewTranscripts($this->hasPerm('agent_chat.view_transcripts'))
            ->setViewUnassigned($this->hasPerm('agent_chat.view_unassigned'))
            ->setDelete($this->hasPerm('agent_chat.delete'))
        ;

        return $model;
    }

    /**
     * @return CRMSettings
     */
    public function getCrmSettings()
    {
        $model = new CRMSettings();
        $model->setEnabled($this->hasPerm('agent_people.use'));

        $personPermissions = $model->getPermissions()->getPerson();
        $personPermissions
            ->setCreateLabels($this->hasPerm('agent_people.create_labels'))
            ->setCreate($this->hasPerm('agent_people.create'))
            ->setEdit($this->hasPerm('agent_people.edit'))
            ->setValidate($this->hasPerm('agent_people.validate'))
            ->setManageEmails($this->hasPerm('agent_people.manage_emails'))
            ->setResetPassword($this->hasPerm('agent_people.reset_password'))
            ->setNotes($this->hasPerm('agent_people.notes'))
            ->setDisable($this->hasPerm('agent_people.disable'))
            ->setDelete($this->hasPerm('agent_people.delete'))
            ->setLoginAs($this->hasPerm('agent_people.login_as'))
            ->setMerge($this->hasPerm('agent_people.merge'))
        ;

        $organizationSettings = $model->getPermissions()->getOrganization();
        $organizationSettings
            ->setCreateLabels('agent_org.create_labels')
            ->setCreate('agent_org.create')
            ->setEdit('agent_org.edit')
            ->setNotes('agent_org.notes')
            ->setDelete('agent_org.delete')
        ;

        return $model;
    }

    /**
     * @return FeedbackSettings
     */
    public function getFeedbackSettings()
    {
        $model = new FeedbackSettings();
        $model->setEnabled($this->hasPerm('core.apps_feedback'));

        $permissions = $model->getPermissions();
        $permissions->setCreateLabels($this->hasPerm('agent_publish.feedback_create_labels'));

        return $model;
    }

    /**
     * @return PublishSettings
     */
    public function getPublishSettings()
    {
        $model = new PublishSettings();
        $model->setEnabled($this->hasPerm('core.apps_kb'));

        $permissions = $model->getPermissions();
        $permissions
            ->setCreate($this->hasPerm('agent_publish.create'))
            ->setEdit($this->hasPerm('agent_publish.edit'))
            ->setValidate($this->hasPerm('agent_publish.validate'))
            ->setDelete($this->hasPerm('agent_publish.delete'))
            ->setCanInsertHtml($this->hasPerm('agent_publish.can_insert_html'))
        ;

        return $model;
    }

    /**
     * @return TasksSettings
     */
    public function getTasksSettings()
    {
        $model = new TasksSettings();
        $model->setEnabled($this->hasPerm('core.apps_tasks'));

        return $model;
    }

    /**
     * @return AccountInfo
     */
    public function getAccountInfo()
    {
        $user          = $this->getUser();
        $signatureHtml = $user->getHelper('Agent')->getSignatureHtml();

        $language = $user->getLanguage();
        if (!$language) {
            $language = $this->em->getRepository(Language::class)->findOneBy([
                'sys_name' => 'default',
            ]);
        }

        $accountInfo = new AccountInfo();
        $accountInfo
            ->setLanguage($language)
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

    /**
     * @param string $name
     *
     * @return bool
     */
    private function hasPerm($name)
    {
        return $this->getUser()->hasPerm($name);
    }
}
