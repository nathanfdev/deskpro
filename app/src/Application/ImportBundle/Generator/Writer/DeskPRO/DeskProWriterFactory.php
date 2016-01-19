<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace Application\ImportBundle\Generator\Writer\DeskPRO;

use Application\DeskPRO\Entity\ImportMap;
use Application\DeskPRO\EntityRepository;
use Application\DeskPRO\Search\EntityWatcher\EntityWatcher;
use Application\ImportBundle\Generator\Writer\AbstractWriterFactory;
use Application\ImportBundle\Generator\Writer\DeskPRO\Importer\BlobAdapter;
use Application\ImportBundle\Generator\Writer\DeskPRO\Importer\Mapper\OidMapper;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;

/**
 * Generator DeskPRO writer factory.
 *
 * Class DeskProWriterFactory
 */
class DeskProWriterFactory extends AbstractWriterFactory
{
    /**
     * {@inheritdoc}
     */
    public function createWriter()
    {
        /** @var EntityManager $entity_manager */
        $entity_manager = $this->container->get('doctrine.orm.entity_manager');

        /** @var EntityRepository\Article $article_repository */
        $article_repository = $entity_manager->getRepository('DeskPRO:Article');
        /** @var EntityRepository\ArticleCategory $article_category_repository */
        $article_category_repository = $entity_manager->getRepository('DeskPRO:ArticleCategory');
        /** @var EntityRepository\LabelArticle $article_label_repository */
        $article_label_repository = $entity_manager->getRepository('DeskPRO:LabelArticle');
        /** @var EntityRepository\ArticleComment $article_comment_repository */
        $article_comment_repository = $entity_manager->getRepository('DeskPRO:ArticleComment');
        /** @var EntityRepository\CustomDefPerson $custom_def_person_repository */
        $custom_def_person_repository = $entity_manager->getRepository('DeskPRO:CustomDefPerson');
        /** @var EntityRepository\CustomDefTicket $custom_def_ticket_repository */
        $custom_def_ticket_repository = $entity_manager->getRepository('DeskPRO:CustomDefTicket');
        /** @var EntityRepository\CustomDefFeedback $custom_def_feedback_repository */
        $custom_def_feedback_repository = $entity_manager->getRepository('DeskPRO:CustomDefFeedback');
        /** @var EntityRepository\CustomDefOrganization $custom_def_organization_repository */
        $custom_def_organization_repository = $entity_manager->getRepository('DeskPRO:CustomDefOrganization');
        /** @var EntityRepository\CustomDefArticle $custom_def_article_repository */
        $custom_def_article_repository = $entity_manager->getRepository('DeskPRO:CustomDefArticle');
        /** @var EntityRepository\Department $department_repository */
        $department_repository = $entity_manager->getRepository('DeskPRO:Department');
        /** @var EntityRepository\Download $download_repository */
        $download_repository = $entity_manager->getRepository('DeskPRO:Download');
        /** @var EntityRepository\DownloadCategory $download_category_repository */
        $download_category_repository = $entity_manager->getRepository('DeskPRO:DownloadCategory');
        /** @var EntityRepository\LabelDownload $download_label_repository */
        $download_label_repository = $entity_manager->getRepository('DeskPRO:LabelDownload');
        /** @var EntityRepository\Feedback $feedback_repository */
        $feedback_repository = $entity_manager->getRepository('DeskPRO:Feedback');
        /** @var EntityRepository\FeedbackCategory $feedback_category_repository */
        $feedback_category_repository = $entity_manager->getRepository('DeskPRO:FeedbackCategory');
        /** @var EntityRepository\LabelFeedback $feedback_label_repository */
        $feedback_label_repository = $entity_manager->getRepository('DeskPRO:LabelFeedback');
        /** @var EntityRepository\Language $language_repository */
        $language_repository = $entity_manager->getRepository('DeskPRO:Language');
        /** @var EntityRepository\News $news_repository */
        $news_repository = $entity_manager->getRepository('DeskPRO:News');
        /** @var EntityRepository\NewsCategory $news_category_repository */
        $news_category_repository = $entity_manager->getRepository('DeskPRO:NewsCategory');
        /** @var EntityRepository\LabelNews $news_label_repository */
        $news_label_repository = $entity_manager->getRepository('DeskPRO:LabelNews');
        /** @var EntityRepository\Organization $organization_repository */
        $organization_repository = $entity_manager->getRepository('DeskPRO:Organization');
        /** @var EntityRepository\LabelOrganization $organization_label_repository */
        $organization_label_repository = $entity_manager->getRepository('DeskPRO:LabelOrganization');
        /** @var EntityRepository\Person $person_repository */
        $person_repository = $entity_manager->getRepository('DeskPRO:Person');
        /** @var EntityRepository\LabelPerson $person_label_repository */
        $person_label_repository = $entity_manager->getRepository('DeskPRO:LabelPerson');
        /** @var EntityRepository\PersonEmail $person_email_repository */
        $person_email_repository = $entity_manager->getRepository('DeskPRO:PersonEmail');
        /** @var EntityRepository\Product $product_repository */
        $product_repository = $entity_manager->getRepository('DeskPRO:Product');
        /** @var EntityRepository\Ticket $ticket_repository */
        $ticket_repository = $entity_manager->getRepository('DeskPRO:Ticket');
        /** @var EntityRepository\TicketPriority $ticket_priority_repository */
        $ticket_priority_repository = $entity_manager->getRepository('DeskPRO:TicketPriority');
        /** @var EntityRepository\TicketCategory $ticket_category_repository */
        $ticket_category_repository = $entity_manager->getRepository('DeskPRO:TicketCategory');
        /** @var EntityRepository\LabelTicket $ticket_label_repository */
        $ticket_label_repository = $entity_manager->getRepository('DeskPRO:LabelTicket');
        /** @var ObjectRepository $ticket_layout_repository */
        $ticket_layout_repository = $entity_manager->getRepository('DeskPRO:TicketLayout');
        /** @var EntityRepository\TicketWorkflow $ticket_workflow_repository */
        $ticket_workflow_repository = $entity_manager->getRepository('DeskPRO:TicketWorkflow');
        /** @var EntityRepository\TicketMessage $ticket_message_repository */
        $ticket_message_repository = $entity_manager->getRepository('DeskPRO:TicketMessage');
        /** @var EntityRepository\Usergroup $user_group_repository */
        $user_group_repository = $entity_manager->getRepository('DeskPRO:Usergroup');
        /** @var ObjectRepository $object_lang_repository */
        $object_lang_repository = $entity_manager->getRepository('DeskPRO:ObjectLang');
        /** @var EntityRepository\ImportMap $import_map_repository */
        $import_map_repository = $entity_manager->getRepository('DeskPRO:ImportMap');

        $email_account_manager = $this->container->getEmailAccountManager();

        $mappers = new Importer\Mapper\Collection();
        $mappers
            ->attach(new Importer\Mapper\Article($article_repository))
            ->attach(new Importer\Mapper\ArticleCategory($article_category_repository))
            ->attach(new Importer\Mapper\ArticleLabel($article_label_repository))
            ->attach(new Importer\Mapper\ArticleComment($article_comment_repository, $entity_manager))
            ->attach(new Importer\Mapper\CustomDefPerson($custom_def_person_repository, $import_map_repository))
            ->attach(new Importer\Mapper\CustomDefTicket($custom_def_ticket_repository, $import_map_repository))
            ->attach(new Importer\Mapper\CustomDefFeedback($custom_def_feedback_repository, $import_map_repository))
            ->attach(new Importer\Mapper\CustomDefOrganization($custom_def_organization_repository, $import_map_repository))
            ->attach(new Importer\Mapper\CustomDefArticle($custom_def_article_repository, $import_map_repository))
            ->attach(new Importer\Mapper\Department($department_repository))
            ->attach(new Importer\Mapper\Download($download_repository))
            ->attach(new Importer\Mapper\DownloadCategory($download_category_repository))
            ->attach(new Importer\Mapper\DownloadLabel($download_label_repository))
            ->attach(new Importer\Mapper\Feedback($feedback_repository))
            ->attach(new Importer\Mapper\FeedbackCategory($feedback_category_repository))
            ->attach(new Importer\Mapper\FeedbackLabel($feedback_label_repository))
            ->attach(new Importer\Mapper\Language($language_repository))
            ->attach(new Importer\Mapper\News($news_repository))
            ->attach(new Importer\Mapper\NewsCategory($news_category_repository))
            ->attach(new Importer\Mapper\NewsLabel($news_label_repository))
            ->attach(new Importer\Mapper\Organization($organization_repository))
            ->attach(new Importer\Mapper\OrganizationLabel($organization_label_repository))
            ->attach(new Importer\Mapper\Person($person_repository))
            ->attach(new Importer\Mapper\PersonLabel($person_label_repository))
            ->attach(new Importer\Mapper\PersonEmail($person_email_repository))
            ->attach(new Importer\Mapper\Product($product_repository))
            ->attach(new Importer\Mapper\Ticket($ticket_repository))
            ->attach(new Importer\Mapper\TicketMessage($ticket_message_repository, $import_map_repository))
            ->attach(new Importer\Mapper\TicketPriority($ticket_priority_repository))
            ->attach(new Importer\Mapper\TicketCategory($ticket_category_repository))
            ->attach(new Importer\Mapper\TicketLabel($ticket_label_repository))
            ->attach(new Importer\Mapper\TicketLayout($ticket_layout_repository))
            ->attach(new Importer\Mapper\TicketDepartment($department_repository))
            ->attach(new Importer\Mapper\TicketWorkflow($ticket_workflow_repository))
            ->attach(new Importer\Mapper\UserGroup($user_group_repository))
            ->attach(new Importer\Mapper\BlobData())
            ->attach(new Importer\Mapper\EmailAccount($email_account_manager))
            ->attach(new Importer\Mapper\ObjectLang($object_lang_repository, $entity_manager))
            ->attach(new Importer\Mapper\ImportMap($import_map_repository))
        ;

        $blob_storage = $this->container->getBlobStorage();
        $blob_adapter = new BlobAdapter($blob_storage, new Importer\Mapper\BlobData());

        $ticket_manager = $this->container->getTicketManager();
        $oid_mapper     = new OidMapper($import_map_repository, $entity_manager);

        $importers = new Importer\Collection();
        $importers
            ->attach(new Importer\Download($mappers, $blob_adapter))
            ->attach(new Importer\DownloadLabel($mappers))
            ->attach(new Importer\FeedbackCustomDef($mappers))
            ->attach(new Importer\Feedback($mappers, $blob_adapter))
            ->attach(new Importer\FeedbackLabel($mappers))
            ->attach(new Importer\ArticleCustomDef($mappers))
            ->attach(new Importer\Article($mappers, $blob_adapter))
            ->attach(new Importer\ArticleLabel($mappers))
            ->attach(new Importer\ArticleTranslation($mappers))
            ->attach(new Importer\ArticleCategory($mappers, $entity_manager))
            ->attach(new Importer\News($mappers))
            ->attach(new Importer\NewsLabel($mappers))
            ->attach(new Importer\PersonCustomDef($mappers))
            ->attach(new Importer\Person($mappers))
            ->attach(new Importer\PersonLabel($mappers))
            ->attach(new Importer\TicketCustomDef($mappers))
            ->attach(new Importer\TicketCustomDefLayout($mappers))
            ->attach(new Importer\Ticket($mappers, $ticket_manager, $blob_adapter))
            ->attach(new Importer\TicketLabel($mappers))
            ->attach(new Importer\OrganizationCustomDef($mappers))
            ->attach(new Importer\Organization($mappers, $blob_adapter))
            ->attach(new Importer\OrganizationLabel($mappers))
        ;

        /** @var EntityWatcher $entity_watcher */
        $entity_watcher = $this->container->get('deskpro.search.entity_listener');

        return new DeskProWriter($importers, $entity_manager, $oid_mapper, $entity_watcher);
    }
}
