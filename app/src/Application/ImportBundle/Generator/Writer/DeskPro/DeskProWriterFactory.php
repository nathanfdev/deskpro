<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

namespace Application\ImportBundle\Generator\Writer\DeskPro;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\EntityRepository;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\ImportBundle\Generator\Writer\AbstractFactory;
use Application\ImportBundle\Generator\Writer\DeskPro\Importer\BlobAdapter;
use Exception;

/**
 * Generator deskpro writer factory
 *
 * Class DeskProWriterFactory
 * @package Application\ImportBundle\Generator\Writer\DeskPro
 */
class DeskProWriterFactory extends AbstractFactory
{
    /**
     * {@inheritdoc}
     */
    public function createWriter()
    {
        /** @var \Doctrine\Bundle\DoctrineBundle\Registry $doctrine */
        $doctrine = $this->container->get('doctrine');
        /** @var \Doctrine\Common\Persistence\ObjectManager $entity_manager */
        $entity_manager = $this->container->get('doctrine.orm.entity_manager');

        /** @var EntityRepository\Article $article_repository */
        $article_repository = $doctrine->getRepository('Application\DeskPRO\Entity\Article');
        /** @var EntityRepository\ArticleCategory $article_category_repository */
        $article_category_repository = $doctrine->getRepository('Application\DeskPRO\Entity\ArticleCategory');
        /** @var EntityRepository\LabelArticle $article_label_repository */
        $article_label_repository = $doctrine->getRepository('Application\DeskPRO\Entity\LabelArticle');
        /** @var EntityRepository\CustomDefPerson $custom_def_person_repository */
        $custom_def_person_repository = $doctrine->getRepository('Application\DeskPRO\Entity\CustomDefPerson');
        /** @var EntityRepository\CustomDefTicket $custom_def_ticket_repository */
        $custom_def_ticket_repository = $doctrine->getRepository('Application\DeskPRO\Entity\CustomDefTicket');
        /** @var EntityRepository\Department $departmentRepository */
        $departmentRepository = $doctrine->getRepository('Application\DeskPRO\Entity\Department');
        /** @var EntityRepository\Download $download_repository */
        $download_repository = $doctrine->getRepository('Application\DeskPRO\Entity\Download');
        /** @var EntityRepository\DownloadCategory $download_category_repository */
        $download_category_repository = $doctrine->getRepository('Application\DeskPRO\Entity\DownloadCategory');
        /** @var EntityRepository\LabelDownload $download_label_repository */
        $download_label_repository = $doctrine->getRepository('Application\DeskPRO\Entity\LabelDownload');
        /** @var EntityRepository\Feedback $feedback_repository */
        $feedback_repository = $doctrine->getRepository('Application\DeskPRO\Entity\Feedback');
        /** @var EntityRepository\FeedbackCategory $feedback_category_repository */
        $feedback_category_repository = $doctrine->getRepository('Application\DeskPRO\Entity\FeedbackCategory');
        /** @var EntityRepository\LabelFeedback $feedback_label_repository */
        $feedback_label_repository = $doctrine->getRepository('Application\DeskPRO\Entity\LabelFeedback');
        /** @var EntityRepository\Language $language_repository */
        $language_repository = $doctrine->getRepository('Application\DeskPRO\Entity\Language');
        /** @var EntityRepository\News $news_repository */
        $news_repository = $doctrine->getRepository('Application\DeskPRO\Entity\News');
        /** @var EntityRepository\NewsCategory $news_category_repository */
        $news_category_repository = $doctrine->getRepository('Application\DeskPRO\Entity\NewsCategory');
        /** @var EntityRepository\LabelNews $news_label_repository */
        $news_label_repository = $doctrine->getRepository('Application\DeskPRO\Entity\LabelNews');
        /** @var EntityRepository\Organization $organization_repository */
        $organization_repository = $doctrine->getRepository('Application\DeskPRO\Entity\Organization');
        /** @var EntityRepository\Person $person_repository */
        $person_repository = $doctrine->getRepository('Application\DeskPRO\Entity\Person');
        /** @var EntityRepository\LabelPerson $person_label_repository */
        $person_label_repository = $doctrine->getRepository('Application\DeskPRO\Entity\LabelPerson');
        /** @var EntityRepository\PersonEmail $person_email_repository */
        $person_email_repository = $doctrine->getRepository('Application\DeskPRO\Entity\PersonEmail');
        /** @var EntityRepository\Product $product_repository */
        $product_repository = $doctrine->getRepository('Application\DeskPRO\Entity\Product');
        /** @var EntityRepository\Ticket $ticket_repository */
        $ticket_repository = $doctrine->getRepository('Application\DeskPRO\Entity\Ticket');
        /** @var EntityRepository\TicketPriority $ticket_priority_repository */
        $ticket_priority_repository = $doctrine->getRepository('Application\DeskPRO\Entity\TicketPriority');
        /** @var EntityRepository\TicketCategory $ticket_category_repository */
        $ticket_category_repository = $doctrine->getRepository('Application\DeskPRO\Entity\TicketCategory');
        /** @var EntityRepository\LabelTicket $ticket_label_repository */
        $ticket_label_repository = $doctrine->getRepository('Application\DeskPRO\Entity\LabelTicket');
        /** @var EntityRepository\TicketWorkflow $ticket_workflow_repository */
        $ticket_workflow_repository = $doctrine->getRepository('Application\DeskPRO\Entity\TicketWorkflow');
        /** @var EntityRepository\Usergroup $user_group_repository */
        $user_group_repository = $doctrine->getRepository('Application\DeskPRO\Entity\Usergroup');

        if ($this->container instanceof DeskproContainer) {
            $email_account_manager = $this->container->getEmailAccountManager();
        } else {
            throw new Exception('Unable to get the email account manager');
        }

        $mappers = new Importer\Mapper\Collection();
        $mappers
            ->attach(new Importer\Mapper\Article($article_repository))
            ->attach(new Importer\Mapper\ArticleCategory($article_category_repository))
            ->attach(new Importer\Mapper\ArticleLabel($article_label_repository))
            ->attach(new Importer\Mapper\CustomDefPerson($custom_def_person_repository))
            ->attach(new Importer\Mapper\CustomDefTicket($custom_def_ticket_repository))
            ->attach(new Importer\Mapper\Department($departmentRepository))
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
            ->attach(new Importer\Mapper\Person($person_repository))
            ->attach(new Importer\Mapper\PersonLabel($person_label_repository))
            ->attach(new Importer\Mapper\PersonEmail($person_email_repository))
            ->attach(new Importer\Mapper\Product($product_repository))
            ->attach(new Importer\Mapper\Ticket($ticket_repository))
            ->attach(new Importer\Mapper\TicketPriority($ticket_priority_repository))
            ->attach(new Importer\Mapper\TicketCategory($ticket_category_repository))
            ->attach(new Importer\Mapper\TicketLabel($ticket_label_repository))
            ->attach(new Importer\Mapper\TicketDepartment($departmentRepository))
            ->attach(new Importer\Mapper\TicketWorkflow($ticket_workflow_repository))
            ->attach(new Importer\Mapper\UserGroup($user_group_repository))
            ->attach(new Importer\Mapper\BlobData())
            ->attach(new Importer\Mapper\EmailAccount($email_account_manager));

        /** @var DeskproBlobStorage $blob_storage */
        if ($this->container instanceof DeskproContainer) {
            $blob_storage = $this->container->getBlobStorage();
            $blob_adapter = new BlobAdapter($blob_storage, new Importer\Mapper\BlobData());

            $ticket_manager = $this->container->getTicketManager();
        } else {
            throw new Exception('Unable to get the blob storage');
        }

        $importers = new Importer\Collection();
        $importers
            ->attach(new Importer\Download($mappers, $blob_adapter))
            ->attach(new Importer\DownloadLabel($mappers))
            ->attach(new Importer\Feedback($mappers))
            ->attach(new Importer\FeedbackLabel($mappers))
            ->attach(new Importer\Article($mappers))
            ->attach(new Importer\ArticleLabel($mappers))
            ->attach(new Importer\News($mappers))
            ->attach(new Importer\NewsLabel($mappers))
            ->attach(new Importer\Person($mappers))
            ->attach(new Importer\PersonLabel($mappers))
            ->attach(new Importer\Ticket($mappers, $ticket_manager, $blob_adapter))
            ->attach(new Importer\TicketLabel($mappers));

        return new DeskProWriter($importers, $entity_manager);
    }
}
