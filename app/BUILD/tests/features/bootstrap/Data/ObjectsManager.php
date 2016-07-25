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

namespace DpBehat\Data;

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\BrandSetting;
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\CustomDataFeedback;
use Application\DeskPRO\Entity\CustomDefChat;
use Application\DeskPRO\Entity\CustomDefFeedback;
use Application\DeskPRO\Entity\CustomDefOrganization;
use Application\DeskPRO\Entity\CustomDefPerson;
use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\Entity\DataStore;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\DownloadCategory;
use Application\DeskPRO\Entity\EmailAccount;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\FeedbackCategory;
use Application\DeskPRO\Entity\FeedbackComment;
use Application\DeskPRO\Entity\FeedbackStatusCategory;
use Application\DeskPRO\Entity\GlossaryWord;
use Application\DeskPRO\Entity\GlossaryWordDefinition;
use Application\DeskPRO\Entity\LabelDef;
use Application\DeskPRO\Entity\LabelFeedback;
use Application\DeskPRO\Entity\LabelTask;
use Application\DeskPRO\Entity\LabelTicket;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\NewsCategory;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\OrganizationNote;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;
use Application\DeskPRO\Entity\Product;
use Application\DeskPRO\Entity\Sla;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketAttachment;
use Application\DeskPRO\Entity\TicketCategory;
use Application\DeskPRO\Entity\TicketFlagged;
use Application\DeskPRO\Entity\TicketLayout;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Entity\TicketParticipant;
use Application\DeskPRO\Entity\TicketPriority;
use Application\DeskPRO\Entity\TicketWorkflow;
use Application\DeskPRO\Entity\Usergroup;
use DeskPRO\Bundle\AppBundle\Entity\AgentChat;
use DeskPRO\Bundle\AppBundle\Entity\AgentChatMessage;
use DeskPRO\Bundle\AppBundle\Entity\AgentChatParticipant;
use DeskPRO\Bundle\AppBundle\Entity\ClientDevice;
use DeskPRO\Bundle\AppBundle\Entity\ProjectMember;
use DeskPRO\Bundle\AppBundle\Entity\Task;
use DeskPRO\Bundle\AppBundle\Entity\TaskAssignment;
use DeskPRO\Bundle\AppBundle\Entity\TaskAttachment;
use DeskPRO\Bundle\AppBundle\Entity\TaskComment;
use DeskPRO\Bundle\AppBundle\Entity\TaskLinkedItem\TaskLinkedArticle;
use DeskPRO\Bundle\AppBundle\Entity\TaskLinkedItem\TaskLinkedChat;
use DeskPRO\Bundle\AppBundle\Entity\TaskLinkedItem\TaskLinkedTicket;
use DeskPRO\Bundle\AppBundle\Entity\TaskList;
use DeskPRO\Bundle\AppBundle\Entity\TaskProject;
use DeskPRO\Bundle\AppBundle\Entity\TaskSubtask;
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

            if ($value === 'NULL') {
                $value = null;
            } elseif (($date = \DateTime::createFromFormat('Y-m-d G:i:s', $value)) !== false) {
                $value = $date;
            } elseif (is_array($array = json_decode($value, true))) {
                $value = $array;
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
            'AgentTeam'              => [Factory\CommonFactories::class, 'agentTeam'],
            'Article'                => [Factory\SimpleFactory::class, 'create', Article::class],
            'ArticleCategory'        => [Factory\SimpleFactory::class, 'create', ArticleCategory::class],
            'Chat'                   => [Factory\SimpleFactory::class, 'create', ChatConversation::class],
            'AgentChat'              => [Factory\SimpleFactory::class, 'create', AgentChat::class],
            'AgentChatParticipant'   => [Factory\SimpleFactory::class, 'create', AgentChatParticipant::class],
            'AgentChatMessage'       => [Factory\SimpleFactory::class, 'create', AgentChatMessage::class],
            'CustomDefOrganization'  => [Factory\CommonFactories::class, 'customDef', 'organization'],
            'CustomDefTicket'        => [Factory\CommonFactories::class, 'customDef', 'ticket'],
            'CustomDefPerson'        => [Factory\CommonFactories::class, 'customDef', 'person'],
            'CustomDefChat'          => [Factory\CommonFactories::class, 'customDef', 'conversation'],
            'CustomDefFeedback'      => [Factory\CommonFactories::class, 'customDef', 'feedback'],
            'CustomDataFeedback'     => [Factory\SimpleFactory::class, 'create', CustomDataFeedback::class],
            'Department'             => [Factory\CommonFactories::class, 'department'],
            'Download'               => [Factory\SimpleFactory::class, 'create', Download::class],
            'DownloadCategory'       => [Factory\SimpleFactory::class, 'create', DownloadCategory::class],
            'Feedback'               => [Factory\CommonFactories::class, 'feedback'],
            'FeedbackCategory'       => [Factory\SimpleFactory::class, 'create', FeedbackCategory::class],
            'FeedbackStatusCategory' => [Factory\SimpleFactory::class, 'create', FeedbackStatusCategory::class],
            'FeedbackComment'        => [Factory\SimpleFactory::class, 'create', FeedbackComment::class],
            'GlossaryWord'           => [Factory\SimpleFactory::class, 'create', GlossaryWord::class],
            'Task'                   => [Factory\CommonFactories::class, 'task'],
            'TaskComment'            => [Factory\SimpleFactory::class, 'create', TaskComment::class],
            'TaskAssignment'         => [Factory\SimpleFactory::class, 'create', TaskAssignment::class],
            'TaskProject'            => [Factory\SimpleFactory::class, 'create', TaskProject::class],
            'ProjectMember'          => [Factory\SimpleFactory::class, 'create', ProjectMember::class],
            'TaskList'               => [Factory\SimpleFactory::class, 'create', TaskList::class],
            'TaskLinkedArticle'      => [Factory\SimpleFactory::class, 'create', TaskLinkedArticle::class],
            'TaskLinkedTicket'       => [Factory\SimpleFactory::class, 'create', TaskLinkedTicket::class],
            'TaskLinkedChat'         => [Factory\SimpleFactory::class, 'create', TaskLinkedChat::class],
            'TaskSubtask'            => [Factory\SimpleFactory::class, 'create', TaskSubtask::class],
            'GlossaryWordDefinition' => [Factory\SimpleFactory::class, 'create', GlossaryWordDefinition::class],
            'News'                   => [Factory\SimpleFactory::class, 'create', News::class],
            'NewsCategory'           => [Factory\SimpleFactory::class, 'create', NewsCategory::class],
            'Organization'           => [Factory\SimpleFactory::class, 'create', Organization::class],
            'OrganizationNote'       => [Factory\SimpleFactory::class, 'create', OrganizationNote::class],
            'Product'                => [Factory\CommonFactories::class, 'product'],
            'Ticket'                 => [Factory\CommonFactories::class, 'ticket'],
            'TicketPriority'         => [Factory\SimpleFactory::class, 'create', TicketPriority::class],
            'TicketCategory'         => [Factory\SimpleFactory::class, 'create', TicketCategory::class],
            'TicketWorkflow'         => [Factory\SimpleFactory::class, 'create', TicketWorkflow::class],
            'TicketParticipant'      => [Factory\SimpleFactory::class, 'create', TicketParticipant::class],
            'TicketAttachment'       => [Factory\SimpleFactory::class, 'create', TicketAttachment::class],
            'TicketFlagged'          => [Factory\SimpleFactory::class, 'create', TicketFlagged::class],
            'Sla'                    => [Factory\CommonFactories::class, 'sla'],
            'SLA'                    => [Factory\CommonFactories::class, 'sla'],
            'Usergroup'              => [Factory\SimpleFactory::class, 'create', Usergroup::class],
            'Language'               => [Factory\SimpleFactory::class, 'create', Language::class],
            'TicketMessage'          => [Factory\SimpleFactory::class, 'create', TicketMessage::class],
            'User'                   => [Factory\CommonFactories::class, 'person', 'user'],
            'Agent'                  => [Factory\CommonFactories::class, 'person', 'agent'],
            'Admin'                  => [Factory\CommonFactories::class, 'person', 'admin'],
            'PersonEmail'            => [Factory\SimpleFactory::class, 'create', PersonEmail::class],
            'LabelTicket'            => [Factory\SimpleFactory::class, 'create', LabelTicket::class],
            'LabelDef'               => [Factory\SimpleFactory::class, 'create', LabelDef::class],
            'LabelFeedback'          => [Factory\SimpleFactory::class, 'create', LabelFeedback::class],
            'LabelTask'              => [Factory\SimpleFactory::class, 'create', LabelTask::class],
            'Brand'                  => [Factory\SimpleFactory::class, 'create', Brand::class],
            'BrandSetting'           => [Factory\SimpleFactory::class, 'create', BrandSetting::class],
        ];
    }

    /**
     * Init type locators.
     */
    private function initTypeLocators()
    {
        $this->typeLocators = [
            'Person'                 => [$this, 'find', Person::class],
            'PersonEmail'            => [$this, 'find', PersonEmail::class],
            'User'                   => [$this, 'find', Person::class, ['is_agent' => false, 'can_admin' => false]],
            'Agent'                  => [$this, 'find', Person::class, ['is_agent' => true, 'can_admin' => false]],
            'Admin'                  => [$this, 'find', Person::class, ['is_agent' => false, 'can_admin' => true]],
            'Ticket'                 => [$this, 'find', Ticket::class],
            'TicketLayout'           => [$this, 'find', TicketLayout::class],
            'TicketMessage'          => [$this, 'find', TicketMessage::class],
            'TicketPriority'         => [$this, 'find', TicketPriority::class],
            'TicketWorkflow'         => [$this, 'find', TicketWorkflow::class],
            'TicketCategory'         => [$this, 'find', TicketCategory::class],
            'TicketParticipant'      => [$this, 'find', TicketParticipant::class],
            'TicketAttachment'       => [$this, 'find', TicketAttachment::class],
            'TicketFlagged'          => [$this, 'find', TicketFlagged::class],
            'SLA'                    => [$this, 'find', Sla::class],
            'Organization'           => [$this, 'find', Organization::class],
            'OrganizationNote'       => [$this, 'find', OrganizationNote::class],
            'Product'                => [$this, 'find', Product::class],
            'Chat'                   => [$this, 'find', ChatConversation::class],
            'Department'             => [$this, 'find', Department::class],
            'CustomDefTicket'        => [$this, 'find', CustomDefTicket::class],
            'CustomDefOrganization'  => [$this, 'find', CustomDefOrganization::class],
            'CustomDefPerson'        => [$this, 'find', CustomDefPerson::class],
            'CustomDefChat'          => [$this, 'find', CustomDefChat::class],
            'CustomDefFeedback'      => [$this, 'find', CustomDefFeedback::class],
            'CustomDataFeedback'     => [$this, 'find', CustomDataFeedback::class],
            'Task'                   => [$this, 'find', Task::class],
            'TaskComment'            => [$this, 'find', TaskComment::class],
            'TaskProject'            => [$this, 'find', TaskProject::class],
            'TaskList'               => [$this, 'find', TaskList::class],
            'TaskAttachment'         => [$this, 'find', TaskAttachment::class],
            'TaskAssignment'         => [$this, 'find', TaskAssignment::class],
            'TaskLinkedArticle'      => [$this, 'find', TaskLinkedArticle::class],
            'TaskLinkedTicket'       => [$this, 'find', TaskLinkedTicket::class],
            'TaskLinkedChat'         => [$this, 'find', TaskLinkedChat::class],
            'TaskSubtask'            => [$this, 'find', TaskSubtask::class],
            'ProjectMember'          => [$this, 'find', ProjectMember::class],
            'Article'                => [$this, 'find', Article::class],
            'News'                   => [$this, 'find', News::class],
            'NewsCategory'           => [$this, 'find', NewsCategory::class],
            'Download'               => [$this, 'find', Download::class],
            'ArticleCategory'        => [$this, 'find', ArticleCategory::class],
            'DownloadCategory'       => [$this, 'find', DownloadCategory::class],
            'ClientDevice'           => [$this, 'find', ClientDevice::class],
            'Blob'                   => [$this, 'find', Blob::class],
            'GlossaryWordDefinition' => [$this, 'find', GlossaryWordDefinition::class],
            'GlossaryWord'           => [$this, 'find', GlossaryWord::class],
            'Language'               => [$this, 'find', Language::class],
            'DataStore'              => [$this, 'find', DataStore::class],
            'EmailAccount'           => [$this, 'find', EmailAccount::class],
            'AgentChat'              => [$this, 'find', AgentChat::class],
            'AgentChatParticipant'   => [$this, 'find', AgentChatParticipant::class],
            'AgentChatMessage'       => [$this, 'find', AgentChatMessage::class],
            'Feedback'               => [$this, 'find', Feedback::class],
            'FeedbackStatusCategory' => [$this, 'find', FeedbackStatusCategory::class],
            'FeedbackCategory'       => [$this, 'find', FeedbackCategory::class],
            'FeedbackComment'        => [$this, 'find', FeedbackComment::class],
            'LabelDef'               => [$this, 'find', LabelDef::class],
            'LabelFeedback'          => [$this, 'find', LabelFeedback::class],
            'LabelTicket'            => [$this, 'find', LabelTicket::class],
            'LabelTask'              => [$this, 'find', LabelTask::class],
            'Brand'                  => [$this, 'find', Brand::class],
            'BrandSetting'           => [$this, 'find', BrandSetting::class],
        ];
    }
}
