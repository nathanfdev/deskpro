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

namespace Application\ImportBundle\Writer\Mapper;

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleAttachment;
use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\ArticleComment;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\ChatMessage;
use Application\DeskPRO\Entity\CustomDefArticle;
use Application\DeskPRO\Entity\CustomDefChat;
use Application\DeskPRO\Entity\CustomDefFeedback;
use Application\DeskPRO\Entity\CustomDefOrganization;
use Application\DeskPRO\Entity\CustomDefPerson;
use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\DownloadCategory;
use Application\DeskPRO\Entity\EmailAccount;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\FeedbackAttachment;
use Application\DeskPRO\Entity\FeedbackCategory;
use Application\DeskPRO\Entity\ImportMap;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\NewsCategory;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\OrganizationContactData;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonContactData;
use Application\DeskPRO\Entity\Product;
use Application\DeskPRO\Entity\Setting;
use Application\DeskPRO\Entity\TextSnippet;
use Application\DeskPRO\Entity\TextSnippetCategory;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketAttachment;
use Application\DeskPRO\Entity\TicketCategory;
use Application\DeskPRO\Entity\TicketLayout;
use Application\DeskPRO\Entity\TicketLog;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Entity\TicketPriority;
use Application\DeskPRO\Entity\TicketWorkflow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class MapperRegistry.
 */
class MapperRegistry
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var array
     */
    private $containerMappers = [];

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param array $containerMappers
     */
    public function setContainerMappers(array $containerMappers)
    {
        $this->containerMappers = $containerMappers;
    }

    /**
     * @param string $entityClass
     *
     * @throws \Exception
     *
     * @return MapperInterface
     */
    public function getMapper($entityClass)
    {
        if (isset($this->containerMappers[$entityClass])) {
            return $this->container->get($this->containerMappers[$entityClass]);
        }

        return new CommonMapper($this->container->get('doctrine.orm.entity_manager'), $entityClass);
    }

    /**
     * Returns the person mapper.
     *
     * @return PersonMapper
     */
    public function getPersonMapper()
    {
        return $this->getMapper(Person::class);
    }

    /**
     * @return CommonMapper
     */
    public function getPersonContactDataMapper()
    {
        return $this->getMapper(PersonContactData::class);
    }

    /**
     * Returns the person custom def mapper.
     *
     * @return CustomDefPersonMapper
     */
    public function getPersonCustomDefMapper()
    {
        return $this->getMapper(CustomDefPerson::class);
    }

    /**
     * Returns the article mapper.
     *
     * @return CommonMapper
     */
    public function getArticleMapper()
    {
        return $this->getMapper(Article::class);
    }

    /**
     * Returns the article comment mapper.
     *
     * @return CommonMapper
     */
    public function getArticleCommentMapper()
    {
        return $this->getMapper(ArticleComment::class);
    }

    /**
     * Returns the article category mapper.
     *
     * @return ArticleCategoryMapper
     */
    public function getArticleCategoryMapper()
    {
        return $this->getMapper(ArticleCategory::class);
    }

    /**
     * Returns the article attachment mapper.
     *
     * @return ArticleCategoryMapper
     */
    public function getArticleAttachmentMapper()
    {
        return $this->getMapper(ArticleAttachment::class);
    }

    /**
     * Returns the article custom def mapper.
     *
     * @return CustomDefArticleMapper
     */
    public function getArticleCustomDefMapper()
    {
        return $this->getMapper(CustomDefArticle::class);
    }

    /**
     * Returns the download mapper.
     *
     * @return CommonMapper
     */
    public function getDownloadMapper()
    {
        return $this->getMapper(Download::class);
    }

    /**
     * Returns the download category mapper.
     *
     * @return DownloadCategoryMapper
     */
    public function getDownloadCategoryMapper()
    {
        return $this->getMapper(DownloadCategory::class);
    }

    /**
     * Returns the news mapper.
     *
     * @return CommonMapper
     */
    public function getNewsMapper()
    {
        return $this->getMapper(News::class);
    }

    /**
     * Returns the news category mapper.
     *
     * @return NewsCategoryMapper
     */
    public function getNewsCategoryMapper()
    {
        return $this->getMapper(NewsCategory::class);
    }

    /**
     * Returns the feedback mapper.
     *
     * @return CommonMapper
     */
    public function getFeedbackMapper()
    {
        return $this->getMapper(Feedback::class);
    }

    /**
     * Returns the feedback custom def mapper.
     *
     * @return CommonMapper
     */
    public function getFeedbackAttachmentMapper()
    {
        return $this->getMapper(FeedbackAttachment::class);
    }

    /**
     * Returns the feedback custom def mapper.
     *
     * @return CustomDefFeedbackMapper
     */
    public function getFeedbackCustomDefMapper()
    {
        return $this->getMapper(CustomDefFeedback::class);
    }

    /**
     * Returns the feedback category mapper.
     *
     * @return FeedbackCategoryMapper
     */
    public function getFeedbackCategoryMapper()
    {
        return $this->getMapper(FeedbackCategory::class);
    }

    /**
     * Returns the ticket mapper.
     *
     * @return TicketMapper
     */
    public function getTicketMapper()
    {
        return $this->getMapper(Ticket::class);
    }

    /**
     * Returns the department mapper.
     *
     * @return DepartmentMapper
     */
    public function getDepartmentMapper()
    {
        return $this->getMapper(Department::class);
    }

    /**
     * Returns the brand mapper.
     *
     * @return BrandMapper
     */
    public function getBrandMapper()
    {
        return $this->getMapper(Brand::class);
    }

    /**
     * Returns the ticket priority mapper.
     *
     * @return CommonMapper
     */
    public function getTicketPriorityMapper()
    {
        return $this->getMapper(TicketPriority::class);
    }

    /**
     * Returns the ticket category mapper.
     *
     * @return TicketCategoryMapper
     */
    public function getTicketCategoryMapper()
    {
        return $this->getMapper(TicketCategory::class);
    }

    /**
     * Returns the ticket workflow mapper.
     *
     * @return CommonMapper
     */
    public function getTicketWorkflowMapper()
    {
        return $this->getMapper(TicketWorkflow::class);
    }

    /**
     * Returns the ticket product mapper.
     *
     * @return TicketProductMapper
     */
    public function getTicketProductMapper()
    {
        return $this->getMapper(Product::class);
    }

    /**
     * Returns the ticket category mapper.
     *
     * @return CommonMapper
     */
    public function getTicketAttachmentMapper()
    {
        return $this->getMapper(TicketAttachment::class);
    }

    /**
     * Returns the ticket message mapper.
     *
     * @return CommonMapper
     */
    public function getTicketMessageMapper()
    {
        return $this->getMapper(TicketMessage::class);
    }

    /**
     * Returns the ticket message mapper.
     *
     * @return CommonMapper
     */
    public function getTicketLogMapper()
    {
        return $this->getMapper(TicketLog::class);
    }

    /**
     * Returns the organization custom def mapper.
     *
     * @return TicketLayoutMapper
     */
    public function getTicketLayoutMapper()
    {
        return $this->getMapper(TicketLayout::class);
    }

    /**
     * Returns the ticket custom def mapper.
     *
     * @return CustomDefTicketMapper
     */
    public function getTicketCustomDefMapper()
    {
        return $this->getMapper(CustomDefTicket::class);
    }

    /**
     * Returns the organization mapper.
     *
     * @return OrganizationMapper
     */
    public function getOrganizationMapper()
    {
        return $this->getMapper(Organization::class);
    }

    /**
     * @return CommonMapper
     */
    public function getOrganizationContactDataMapper()
    {
        return $this->getMapper(OrganizationContactData::class);
    }

    /**
     * Returns the organization custom def mapper.
     *
     * @return CustomDefOrganizationMapper
     */
    public function getOrganizationCustomDefMapper()
    {
        return $this->getMapper(CustomDefOrganization::class);
    }

    /**
     * Returns the import map mapper.
     *
     * @return ImportMapMapper
     */
    public function getImportMapMapper()
    {
        return $this->getMapper(ImportMap::class);
    }

    /**
     * @return EmailAccountMapper
     */
    public function getEmailAccountMapper()
    {
        return $this->getMapper(EmailAccount::class);
    }

    /**
     * @return CommonMapper
     */
    public function getTextSnippetMapper()
    {
        return $this->getMapper(TextSnippet::class);
    }

    /**
     * @return TextSnippetCategoryMapper
     */
    public function getTextSnippetCategoryMapper()
    {
        return $this->getMapper(TextSnippetCategory::class);
    }

    /**
     * @return CommonMapper
     */
    public function getChatMapper()
    {
        return $this->getMapper(ChatConversation::class);
    }

    /**
     * @return CommonMapper
     */
    public function getChatMessageMapper()
    {
        return $this->getMapper(ChatMessage::class);
    }

    /**
     * @return CustomDefChatMapper
     */
    public function getChatCustomDefMapper()
    {
        return $this->getMapper(CustomDefChat::class);
    }

    /**
     * @return CommonMapper
     */
    public function getSettingMapper()
    {
        return $this->getMapper(Setting::class);
    }
}
