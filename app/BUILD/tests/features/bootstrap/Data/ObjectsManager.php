<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace DpBehat\Data;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\ApiToken;
use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\ArticlePendingCreate;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\BrandSetting;
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\ChatMessage;
use Application\DeskPRO\Entity\CustomDataFeedback;
use Application\DeskPRO\Entity\CustomDefBilling;
use Application\DeskPRO\Entity\CustomDefChat;
use Application\DeskPRO\Entity\CustomDefFeedback;
use Application\DeskPRO\Entity\CustomDefOrganization;
use Application\DeskPRO\Entity\CustomDefPerson;
use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\Entity\CustomFieldDefinition;
use Application\DeskPRO\Entity\DataStore;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\DownloadCategory;
use Application\DeskPRO\Entity\EmailAccount;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\FeedbackCategory;
use Application\DeskPRO\Entity\FeedbackComment;
use Application\DeskPRO\Entity\FeedbackStatusCategory;
use Application\DeskPRO\Entity\FeedbackSubscription;
use Application\DeskPRO\Entity\GlossaryWord;
use Application\DeskPRO\Entity\GlossaryWordDefinition;
use Application\DeskPRO\Entity\Job;
use Application\DeskPRO\Entity\LabelChatConversation;
use Application\DeskPRO\Entity\LabelDef;
use Application\DeskPRO\Entity\LabelFeedback;
use Application\DeskPRO\Entity\LabelPerson;
use Application\DeskPRO\Entity\LabelTask;
use Application\DeskPRO\Entity\LabelTicket;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\LegacyTicketFilter;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\NewsCategory;
use Application\DeskPRO\Entity\ObjectLang;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\OrganizationNote;
use Application\DeskPRO\Entity\Permission;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;
use Application\DeskPRO\Entity\PersonNote;
use Application\DeskPRO\Entity\PersonPref;
use Application\DeskPRO\Entity\PersonUsersourceAssoc;
use Application\DeskPRO\Entity\Phrase;
use Application\DeskPRO\Entity\Problem;
use Application\DeskPRO\Entity\Product;
use Application\DeskPRO\Entity\ReportDashboard;
use Application\DeskPRO\Entity\ReportDashboardPermission;
use Application\DeskPRO\Entity\ReportDashboardReport;
use Application\DeskPRO\Entity\ReportDashboardWidget;
use Application\DeskPRO\Entity\ReportWidget;
use Application\DeskPRO\Entity\Session;
use Application\DeskPRO\Entity\Sla;
use Application\DeskPRO\Entity\Task;
use Application\DeskPRO\Entity\TaskComment;
use Application\DeskPRO\Entity\TextSnippet;
use Application\DeskPRO\Entity\TextSnippetCategory;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketAttachment;
use Application\DeskPRO\Entity\TicketCategory;
use Application\DeskPRO\Entity\TicketCharge;
use Application\DeskPRO\Entity\TicketFlagged;
use Application\DeskPRO\Entity\TicketLayout;
use Application\DeskPRO\Entity\TicketLog;
use Application\DeskPRO\Entity\TicketMacro;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Entity\TicketParticipant;
use Application\DeskPRO\Entity\TicketPriority;
use Application\DeskPRO\Entity\TicketSla;
use Application\DeskPRO\Entity\TicketWorkflow;
use Application\DeskPRO\Entity\Usergroup;
use Application\DeskPRO\Entity\Usersource;
use DeskPRO\Bundle\AppBundle\Entity\ActionAlert;
use DeskPRO\Bundle\AppBundle\Entity\AgentChat;
use DeskPRO\Bundle\AppBundle\Entity\AgentChatMessage;
use DeskPRO\Bundle\AppBundle\Entity\AgentChatParticipant;
use DeskPRO\Bundle\AppBundle\Entity\AgentData;
use DeskPRO\Bundle\AppBundle\Entity\AppStore\App;
use DeskPRO\Bundle\AppBundle\Entity\AppStore\AppAssetBlob;
use DeskPRO\Bundle\AppBundle\Entity\AppStore\AppState;
use DeskPRO\Bundle\AppBundle\Entity\ClientDevice;
use DeskPRO\Bundle\AppBundle\Entity\Notification;
use DeskPRO\Bundle\AppBundle\Entity\OAuthClient;
use DeskPRO\Bundle\AppBundle\Entity\Report\ScheduledReport;
use DeskPRO\Bundle\AppBundle\Entity\Snippet;
use DeskPRO\Bundle\AppBundle\Entity\SnippetTranslation;
use DeskPRO\Bundle\AppBundle\Entity\TicketFeedbackLink;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilter;
use DeskPRO\Bundle\AppBundle\Entity\TicketFollowUp;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAccount;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAsset\AbstractVoiceAsset;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAsset\VoiceRecordAsset;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAsset\VoiceTextAsset;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAsset\VoiceUploadAsset;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAutoAttendant;
use DeskPRO\Bundle\AppBundle\Entity\VoiceNumber;
use DeskPRO\Bundle\AppBundle\Entity\VoiceQueue;
use DeskPRO\Bundle\AppBundle\Entity\VoiceTarget\VoiceAgentTarget;
use DeskPRO\Bundle\AppBundle\Entity\VoiceTarget\VoiceAutoAttendantTarget;
use DeskPRO\Bundle\AppBundle\Entity\VoiceTarget\VoiceQueueTarget;
use DeskPRO\Bundle\AppBundle\Entity\Webhooks\TicketWebhook;
use Doctrine\ORM\EntityManager;

/**
 * Class ObjectsManager.
 */
class ObjectsManager
{
    /**
     * @var array Map of record name to its' factory
     */
    private $typeFactories;

    /**
     * @var array Map of record name to its' locator
     */
    private $typeLocators;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->initTypeFactories();
        $this->initTypeLocators();
    }

    /**
     * @return bool
     */
    public function isClosed()
    {
        return null !== $this->em->getConnection();
    }

    /**
     * @param string $type
     * @param array  $data
     *
     * @throws \Exception
     *
     * @return object
     */
    public function create($type, array $data)
    {
        if (!array_key_exists($type, $this->typeFactories)) {
            throw new \Exception("'$type' factory is missing");
        }

        $data = $this->preProcessValues($data);
        $data = DataNormalizer::namedKeysToUnderscore($data);

        $factory = $this->typeFactories[$type];
        if (is_array($factory) && count($factory) > 2) {
            $args    = array_slice($factory, 2);
            $factory = array_slice($factory, 0, 2);
        } else {
            $args = [];
        }

        $args[] = $data;
        $object = call_user_func_array($factory, $args);

        return $object;
    }

    /**
     * @param string $type
     *
     * @throws \Exception
     *
     * @return array
     */
    public function locate($type)
    {
        if (!array_key_exists($type, $this->typeLocators)) {
            throw new \Exception("Locator for the '$type' type is missing");
        }

        $locator = $this->typeLocators[$type];
        if (is_array($locator) && count($locator) > 2) {
            $args    = array_slice($locator, 2);
            $locator = array_slice($locator, 0, 2);
        } else {
            $args = [];
        }

        $objects = call_user_func_array($locator, $args);

        return $objects;
    }

    /**
     * @param string $class
     * @param array  $criteria
     *
     * @return array
     */
    protected function find($class, array $criteria = [])
    {
        return $this->em->getRepository($class)->findBy($criteria);
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public static function preProcessValues(array $data)
    {
        foreach ($data as &$value) {
            $value = self::preProcessValue($value);
        }

        return $data;
    }

    /**
     * @param string $value
     *
     * @return \DateTime|mixed|null
     */
    public static function preProcessValue($value)
    {
        if (is_scalar($value)) {
            $value = DataContext::replace($value, true);

            if (strpos($value, 'raw: ') === 0) {
                $value = preg_replace('/^raw:\s/', '', $value);
            } elseif ($value === 'NULL') {
                $value = null;
            } elseif (preg_match('/^NOW\(\)(.*?)$/', $value, $matches)) {
                $value = new \DateTime();
                if (isset($matches[1])) {
                    $value->modify($matches[1]);
                }
            } elseif (($date = \DateTime::createFromFormat('Y-m-d G:i:s', $value)) !== false) {
                $value = $date;
            } elseif (is_array($array = json_decode($value, true))) {
                $value = $array;
            } elseif (is_numeric($value)) {
                $value = (int) $value;
            }
        }

        return $value;
    }

    /**
     * Init type factories.
     */
    private function initTypeFactories()
    {
        $this->typeFactories = [
            'AgentTeam'                 => [Factory\CommonFactories::class, 'agentTeam'],
            'Article'                   => [Factory\SimpleFactory::class, 'create', Article::class],
            'ArticleCategory'           => [Factory\SimpleFactory::class, 'create', ArticleCategory::class],
            'PendingArticle'            => [Factory\SimpleFactory::class, 'create', ArticlePendingCreate::class],
            'Chat'                      => [Factory\CommonFactories::class, 'chat'],
            'ChatMessage'               => [Factory\SimpleFactory::class, 'create', ChatMessage::class],
            'AgentChat'                 => [Factory\SimpleFactory::class, 'create', AgentChat::class],
            'AgentChatParticipant'      => [Factory\SimpleFactory::class, 'create', AgentChatParticipant::class],
            'AgentChatMessage'          => [Factory\SimpleFactory::class, 'create', AgentChatMessage::class],
            'CustomDefOrganization'     => [Factory\CommonFactories::class, 'customDef', 'organization'],
            'CustomDefTicket'           => [Factory\CommonFactories::class, 'customDef', 'ticket'],
            'CustomDefPerson'           => [Factory\CommonFactories::class, 'customDef', 'person'],
            'CustomDefChat'             => [Factory\CommonFactories::class, 'customDef', 'conversation'],
            'CustomDefFeedback'         => [Factory\CommonFactories::class, 'customDef', 'feedback'],
            'CustomDefBilling'          => [Factory\CommonFactories::class, 'customDef', 'billing'],
            'CustomDataFeedback'        => [Factory\SimpleFactory::class, 'create', CustomDataFeedback::class],
            'CustomFieldDefinition'     => [Factory\SimpleFactory::class, 'create', CustomFieldDefinition::class],
            'CustomPerUserDef'          => [Factory\CommonFactories::class, 'customPerDef', Person::class],
            'CustomPerOrgDef'           => [Factory\CommonFactories::class, 'customPerDef', Organization::class],
            'Department'                => [Factory\CommonFactories::class, 'department'],
            'Download'                  => [Factory\SimpleFactory::class, 'create', Download::class],
            'DownloadCategory'          => [Factory\SimpleFactory::class, 'create', DownloadCategory::class],
            'Feedback'                  => [Factory\CommonFactories::class, 'feedback'],
            'FeedbackSubscription'      => [Factory\SimpleFactory::class, 'create', FeedbackSubscription::class],
            'FeedbackCategory'          => [Factory\SimpleFactory::class, 'create', FeedbackCategory::class],
            'FeedbackStatusCategory'    => [Factory\SimpleFactory::class, 'create', FeedbackStatusCategory::class],
            'FeedbackComment'           => [Factory\SimpleFactory::class, 'create', FeedbackComment::class],
            'GlossaryWord'              => [Factory\SimpleFactory::class, 'create', GlossaryWord::class],
            'GlossaryWordDefinition'    => [Factory\SimpleFactory::class, 'create', GlossaryWordDefinition::class],
            'News'                      => [Factory\SimpleFactory::class, 'create', News::class],
            'NewsCategory'              => [Factory\SimpleFactory::class, 'create', NewsCategory::class],
            'Organization'              => [Factory\SimpleFactory::class, 'create', Organization::class],
            'OrganizationNote'          => [Factory\SimpleFactory::class, 'create', OrganizationNote::class],
            'Product'                   => [Factory\CommonFactories::class, 'product'],
            'Task'                      => [Factory\SimpleFactory::class, 'create', Task::class],
            'TaskComment'               => [Factory\CommonFactories::class, 'task_comment'],
            'Ticket'                    => [Factory\CommonFactories::class, 'ticket'],
            'TicketPriority'            => [Factory\SimpleFactory::class, 'create', TicketPriority::class],
            'TicketCategory'            => [Factory\SimpleFactory::class, 'create', TicketCategory::class],
            'TicketWorkflow'            => [Factory\SimpleFactory::class, 'create', TicketWorkflow::class],
            'TicketParticipant'         => [Factory\SimpleFactory::class, 'create', TicketParticipant::class],
            'TicketAttachment'          => [Factory\SimpleFactory::class, 'create', TicketAttachment::class],
            'TicketFlagged'             => [Factory\SimpleFactory::class, 'create', TicketFlagged::class],
            'TicketMacro'               => [Factory\SimpleFactory::class, 'create', TicketMacro::class],
            'TicketMessage'             => [Factory\SimpleFactory::class, 'create', TicketMessage::class],
            'TicketFeedbackLink'        => [Factory\SimpleFactory::class, 'create', TicketFeedbackLink::class],
            'TicketSla'                 => [Factory\SimpleFactory::class, 'create', TicketSla::class],
            'TicketLog'                 => [Factory\SimpleFactory::class, 'create', TicketLog::class],
            'TicketFollowUp'            => [Factory\SimpleFactory::class, 'create', TicketFollowUp::class],
            'TicketCharge'              => [Factory\SimpleFactory::class, 'create', TicketCharge::class],
            'Sla'                       => [Factory\CommonFactories::class, 'sla'],
            'SLA'                       => [Factory\CommonFactories::class, 'sla'],
            'Usergroup'                 => [Factory\SimpleFactory::class, 'create', Usergroup::class],
            'Language'                  => [Factory\SimpleFactory::class, 'create', Language::class],
            'Guest'                     => [Factory\CommonFactories::class, 'person', 'guest'],
            'User'                      => [Factory\CommonFactories::class, 'person', 'user'],
            'Agent'                     => [Factory\CommonFactories::class, 'person', 'agent'],
            'Admin'                     => [Factory\CommonFactories::class, 'person', 'admin'],
            'PersonEmail'               => [Factory\SimpleFactory::class, 'create', PersonEmail::class],
            'AgentData'                 => [Factory\SimpleFactory::class, 'create', AgentData::class],
            'LabelTicket'               => [Factory\SimpleFactory::class, 'create', LabelTicket::class],
            'LabelPerson'               => [Factory\SimpleFactory::class, 'create', LabelPerson::class],
            'LabelDef'                  => [Factory\SimpleFactory::class, 'create', LabelDef::class],
            'LabelFeedback'             => [Factory\SimpleFactory::class, 'create', LabelFeedback::class],
            'LabelTask'                 => [Factory\SimpleFactory::class, 'create', LabelTask::class],
            'LabelChatConversation'     => [Factory\SimpleFactory::class, 'create', LabelChatConversation::class],
            'Brand'                     => [Factory\SimpleFactory::class, 'create', Brand::class],
            'BrandSetting'              => [Factory\SimpleFactory::class, 'create', BrandSetting::class],
            'Usersource'                => [Factory\SimpleFactory::class, 'create', Usersource::class],
            'UsersourceAssoc'           => [Factory\SimpleFactory::class, 'create', PersonUsersourceAssoc::class],
            'PersonNote'                => [Factory\SimpleFactory::class, 'create', PersonNote::class],
            'VoiceAccount'              => [Factory\SimpleFactory::class, 'create', VoiceAccount::class],
            'VoiceNumber'               => [Factory\SimpleFactory::class, 'create', VoiceNumber::class],
            'VoiceQueue'                => [Factory\SimpleFactory::class, 'create', VoiceQueue::class],
            'VoiceTextAsset'            => [Factory\SimpleFactory::class, 'create', VoiceTextAsset::class],
            'VoiceUploadAsset'          => [Factory\SimpleFactory::class, 'create', VoiceUploadAsset::class],
            'VoiceRecordAsset'          => [Factory\SimpleFactory::class, 'create', VoiceRecordAsset::class],
            'VoiceAutoAttendant'        => [Factory\SimpleFactory::class, 'create', VoiceAutoAttendant::class],
            'VoiceQueueTarget'          => [Factory\SimpleFactory::class, 'create', VoiceQueueTarget::class],
            'VoiceAgentTarget'          => [Factory\SimpleFactory::class, 'create', VoiceAgentTarget::class],
            'VoiceAutoAttendantTarget'  => [Factory\SimpleFactory::class, 'create', VoiceAutoAttendantTarget::class],
            'ClientDevice'              => [Factory\SimpleFactory::class, 'create', ClientDevice::class],
            'LegacyTicketFilter'        => [Factory\SimpleFactory::class, 'create', LegacyTicketFilter::class],
            'TicketFilter'              => [Factory\SimpleFactory::class, 'create', TicketFilter::class],
            'Problem'                   => [Factory\SimpleFactory::class, 'create', Problem::class],
            'PersonPref'                => [Factory\SimpleFactory::class, 'create', PersonPref::class],
            'Blob'                      => [Factory\SimpleFactory::class, 'create', Blob::class],
            'Snippet'                   => [Factory\SimpleFactory::class, 'create', Snippet::class],
            'SnippetTranslation'        => [Factory\SimpleFactory::class, 'create', SnippetTranslation::class],
            'TextSnippet'               => [Factory\SimpleFactory::class, 'create', TextSnippet::class],
            'TextSnippetCategory'       => [Factory\SimpleFactory::class, 'create', TextSnippetCategory::class],
            'ObjectLang'                => [Factory\SimpleFactory::class, 'create', ObjectLang::class],
            'Phrase'                    => [Factory\SimpleFactory::class, 'create', Phrase::class],
            'ActionAlert'               => [Factory\SimpleFactory::class, 'create', ActionAlert::class],
            'Notification'              => [Factory\SimpleFactory::class, 'create', Notification::class],
            'EmailAccount'              => [Factory\SimpleFactory::class, 'create', EmailAccount::class],
            'Session'                   => [Factory\SimpleFactory::class, 'create', Session::class],
            'OAuthClient'               => [Factory\SimpleFactory::class, 'create', OAuthClient::class],
            'ReportWidget'              => [Factory\SimpleFactory::class, 'create', ReportWidget::class],
            'ReportDashboard'           => [Factory\SimpleFactory::class, 'create', ReportDashboard::class],
            'ReportDashboardPermission' => [Factory\SimpleFactory::class, 'create', ReportDashboardPermission::class],
            'ReportDashboardReport'     => [Factory\SimpleFactory::class, 'create', ReportDashboardReport::class],
            'ReportDashboardWidget'     => [Factory\SimpleFactory::class, 'create', ReportDashboardWidget::class],
            'ScheduledReport'           => [Factory\SimpleFactory::class, 'create', ScheduledReport::class],
            'Job'                       => [Factory\SimpleFactory::class, 'create', Job::class],
        ];
    }

    /**
     * Init type locators.
     */
    private function initTypeLocators()
    {
        $this->typeLocators = [
            'AppAssetBlob'              => [$this, 'find', AppAssetBlob::class],
            'App'                       => [$this, 'find', App::class],
            'AppState'                  => [$this, 'find', AppState::class],
            'AgentTeam'                 => [$this, 'find', AgentTeam::class],
            'Person'                    => [$this, 'find', Person::class],
            'PersonEmail'               => [$this, 'find', PersonEmail::class],
            'Guest'                     => [$this, 'find', Person::class, ['is_user' => false]],
            'User'                      => [$this, 'find', Person::class, ['is_agent' => false, 'can_admin' => false]],
            'Agent'                     => [$this, 'find', Person::class, ['is_agent' => true, 'can_admin' => false]],
            'Admin'                     => [$this, 'find', Person::class, ['is_agent' => false, 'can_admin' => true]],
            'AgentData'                 => [$this, 'find', AgentData::class],
            'Task'                      => [$this, 'find', Task::class],
            'TaskComment'               => [$this, 'find', TaskComment::class],
            'Ticket'                    => [$this, 'find', Ticket::class],
            'TicketLayout'              => [$this, 'find', TicketLayout::class],
            'TicketMessage'             => [$this, 'find', TicketMessage::class],
            'TicketFeedbackLink'        => [$this, 'find', TicketFeedbackLink::class],
            'TicketPriority'            => [$this, 'find', TicketPriority::class],
            'TicketWorkflow'            => [$this, 'find', TicketWorkflow::class],
            'TicketCategory'            => [$this, 'find', TicketCategory::class],
            'TicketParticipant'         => [$this, 'find', TicketParticipant::class],
            'TicketAttachment'          => [$this, 'find', TicketAttachment::class],
            'TicketFlagged'             => [$this, 'find', TicketFlagged::class],
            'TicketMacro'               => [$this, 'find', TicketMacro::class],
            'TicketFilter'              => [$this, 'find', TicketFilter::class],
            'TicketSla'                 => [$this, 'find', TicketSla::class],
            'TicketLog'                 => [$this, 'find', TicketLog::class],
            'TicketFollowUp'            => [$this, 'find', TicketFollowUp::class],
            'TicketCharge'              => [$this, 'find', TicketCharge::class],
            'SLA'                       => [$this, 'find', Sla::class],
            'Organization'              => [$this, 'find', Organization::class],
            'OrganizationNote'          => [$this, 'find', OrganizationNote::class],
            'Product'                   => [$this, 'find', Product::class],
            'Chat'                      => [$this, 'find', ChatConversation::class],
            'ChatMessage'               => [$this, 'find', ChatMessage::class],
            'Department'                => [$this, 'find', Department::class],
            'CustomDefTicket'           => [$this, 'find', CustomDefTicket::class],
            'CustomDefOrganization'     => [$this, 'find', CustomDefOrganization::class],
            'CustomDefPerson'           => [$this, 'find', CustomDefPerson::class],
            'CustomDefChat'             => [$this, 'find', CustomDefChat::class],
            'CustomDefFeedback'         => [$this, 'find', CustomDefFeedback::class],
            'CustomDefBilling'          => [$this, 'find', CustomDefBilling::class],
            'CustomDataFeedback'        => [$this, 'find', CustomDataFeedback::class],
            'CustomFieldDefinition'     => [$this, 'find', CustomFieldDefinition::class],
            'CustomPerUserDef'          => [$this, 'find', CustomFieldDefinition::class, ['context_class' => Person::class]],
            'CustomPerOrgDef'           => [$this, 'find', CustomFieldDefinition::class, ['context_class' => Organization::class]],
            'Article'                   => [$this, 'find', Article::class],
            'PendingArticle'            => [$this, 'find', ArticlePendingCreate::class],
            'News'                      => [$this, 'find', News::class],
            'NewsCategory'              => [$this, 'find', NewsCategory::class],
            'Download'                  => [$this, 'find', Download::class],
            'ArticleCategory'           => [$this, 'find', ArticleCategory::class],
            'DownloadCategory'          => [$this, 'find', DownloadCategory::class],
            'ClientDevice'              => [$this, 'find', ClientDevice::class],
            'Blob'                      => [$this, 'find', Blob::class],
            'GlossaryWordDefinition'    => [$this, 'find', GlossaryWordDefinition::class],
            'GlossaryWord'              => [$this, 'find', GlossaryWord::class],
            'Language'                  => [$this, 'find', Language::class],
            'DataStore'                 => [$this, 'find', DataStore::class],
            'EmailAccount'              => [$this, 'find', EmailAccount::class],
            'AgentChat'                 => [$this, 'find', AgentChat::class],
            'AgentChatParticipant'      => [$this, 'find', AgentChatParticipant::class],
            'AgentChatMessage'          => [$this, 'find', AgentChatMessage::class],
            'Feedback'                  => [$this, 'find', Feedback::class],
            'FeedbackSubscription'      => [$this, 'find', FeedbackSubscription::class],
            'FeedbackStatusCategory'    => [$this, 'find', FeedbackStatusCategory::class],
            'FeedbackCategory'          => [$this, 'find', FeedbackCategory::class],
            'FeedbackComment'           => [$this, 'find', FeedbackComment::class],
            'LabelDef'                  => [$this, 'find', LabelDef::class],
            'LabelFeedback'             => [$this, 'find', LabelFeedback::class],
            'LabelTicket'               => [$this, 'find', LabelTicket::class],
            'LabelTask'                 => [$this, 'find', LabelTask::class],
            'LabelPerson'               => [$this, 'find', LabelPerson::class],
            'LabelChatConversation'     => [$this, 'find', LabelChatConversation::class],
            'Brand'                     => [$this, 'find', Brand::class],
            'BrandSetting'              => [$this, 'find', BrandSetting::class],
            'Usergroup'                 => [$this, 'find', Usergroup::class],
            'Session'                   => [$this, 'find', Session::class],
            'ApiToken'                  => [$this, 'find', ApiToken::class],
            'Usersource'                => [$this, 'find', Usersource::class],
            'UsersourceAssoc'           => [$this, 'find', PersonUsersourceAssoc::class],
            'PersonNote'                => [$this, 'find', PersonNote::class],
            'Permission'                => [$this, 'find', Permission::class],
            'VoiceAccount'              => [$this, 'find', VoiceAccount::class],
            'VoiceNumber'               => [$this, 'find', VoiceNumber::class],
            'VoiceQueue'                => [$this, 'find', VoiceQueue::class],
            'VoiceAsset'                => [$this, 'find', AbstractVoiceAsset::class],
            'VoiceTextAsset'            => [$this, 'find', VoiceTextAsset::class],
            'VoiceRecordAsset'          => [$this, 'find', VoiceRecordAsset::class],
            'VoiceUploadAsset'          => [$this, 'find', VoiceUploadAsset::class],
            'VoiceAutoAttendant'        => [$this, 'find', VoiceAutoAttendant::class],
            'VoiceQueueTarget'          => [$this, 'find', VoiceQueueTarget::class],
            'VoiceAgentTarget'          => [$this, 'find', VoiceAgentTarget::class],
            'VoiceAutoAttendantTarget'  => [$this, 'find', VoiceAutoAttendantTarget::class],
            'LegacyTicketFilter'        => [$this, 'find', LegacyTicketFilter::class],
            'Problem'                   => [$this, 'find', Problem::class],
            'PersonPref'                => [$this, 'find', PersonPref::class],
            'Sla'                       => [$this, 'find', Sla::class],
            'Snippet'                   => [$this, 'find', Snippet::class],
            'SnippetTranslation'        => [$this, 'find', SnippetTranslation::class],
            'TextSnippet'               => [$this, 'find', TextSnippet::class],
            'TextSnippetCategory'       => [$this, 'find', TextSnippetCategory::class],
            'TicketWebhook'             => [$this, 'find', TicketWebhook::class],
            'ObjectLang'                => [$this, 'find', ObjectLang::class],
            'Phrase'                    => [$this, 'find', Phrase::class],
            'ActionAlert'               => [$this, 'find', ActionAlert::class],
            'Notification'              => [$this, 'find', Notification::class],
            'OAuthClient'               => [$this, 'find', OAuthClient::class],
            'ReportWidget'              => [$this, 'find', ReportWidget::class],
            'ReportDashboard'           => [$this, 'find', ReportDashboard::class],
            'ReportDashboardPermission' => [$this, 'find', ReportDashboardPermission::class],
            'ReportDashboardReport'     => [$this, 'find', ReportDashboardReport::class],
            'ReportDashboardWidget'     => [$this, 'find', ReportDashboardWidget::class],
            'ScheduledReport'           => [$this, 'find', ScheduledReport::class],
            'Job'                       => [$this, 'find', Job::class],
        ];
    }
}
