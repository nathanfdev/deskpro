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

use Application\DeskPRO\EntityRepository;
use Application\ImportBundle\Generator\Writer\AbstractFactory;

/**
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

        /** @var EntityRepository\Article $articleRepository */
        $articleRepository = $doctrine->getRepository('Application\DeskPRO\Entity\Article');
        /** @var EntityRepository\ArticleCategory $articleCategoryRepository */
        $articleCategoryRepository = $doctrine->getRepository('Application\DeskPRO\Entity\ArticleCategory');
        /** @var EntityRepository\CustomDefPerson $customDefPersonRepository */
        $customDefPersonRepository = $doctrine->getRepository('Application\DeskPRO\Entity\CustomDefPerson');
        /** @var EntityRepository\CustomDefTicket $customDefTicketRepository */
        $customDefTicketRepository = $doctrine->getRepository('Application\DeskPRO\Entity\CustomDefTicket');
        /** @var EntityRepository\Department $departmentRepository */
        $departmentRepository = $doctrine->getRepository('Application\DeskPRO\Entity\Department');
        /** @var EntityRepository\Download $downloadRepository */
        $downloadRepository = $doctrine->getRepository('Application\DeskPRO\Entity\Download');
        /** @var EntityRepository\DownloadCategory $downloadCategoryRepository */
        $downloadCategoryRepository = $doctrine->getRepository('Application\DeskPRO\Entity\DownloadCategory');
        /** @var EntityRepository\Feedback $feedbackRepository */
        $feedbackRepository = $doctrine->getRepository('Application\DeskPRO\Entity\Feedback');
        /** @var EntityRepository\FeedbackCategory $feedbackCategoryRepository */
        $feedbackCategoryRepository = $doctrine->getRepository('Application\DeskPRO\Entity\FeedbackCategory');
        /** @var EntityRepository\Language $languageRepository */
        $languageRepository = $doctrine->getRepository('Application\DeskPRO\Entity\Language');
        /** @var EntityRepository\News $newsRepository */
        $newsRepository = $doctrine->getRepository('Application\DeskPRO\Entity\News');
        /** @var EntityRepository\NewsCategory $newsCategoryRepository */
        $newsCategoryRepository = $doctrine->getRepository('Application\DeskPRO\Entity\NewsCategory');
        /** @var EntityRepository\Organization $organizationRepository */
        $organizationRepository = $doctrine->getRepository('Application\DeskPRO\Entity\Organization');
        /** @var EntityRepository\Person $personRepository */
        $personRepository = $doctrine->getRepository('Application\DeskPRO\Entity\Person');
        /** @var EntityRepository\Product $productRepository */
        $productRepository = $doctrine->getRepository('Application\DeskPRO\Entity\Product');
        /** @var EntityRepository\TicketCategory $ticketCategoryRepository */
        $ticketCategoryRepository = $doctrine->getRepository('Application\DeskPRO\Entity\TicketCategory');
        /** @var EntityRepository\TicketWorkflow $ticketWorkflowRepository */
        $ticketWorkflowRepository = $doctrine->getRepository('Application\DeskPRO\Entity\TicketWorkflow');
        /** @var EntityRepository\Usergroup $userGroupRepository */
        $userGroupRepository = $doctrine->getRepository('Application\DeskPRO\Entity\Usergroup');

        $mappers = new Importer\Mapper\Collection();
        $mappers
            ->attach(new Importer\Mapper\Article($articleRepository))
            ->attach(new Importer\Mapper\ArticleCategory($articleCategoryRepository))
            ->attach(new Importer\Mapper\CustomDefPerson($customDefPersonRepository))
            ->attach(new Importer\Mapper\CustomDefTicket($customDefTicketRepository))
            ->attach(new Importer\Mapper\Department($departmentRepository))
            ->attach(new Importer\Mapper\Download($downloadRepository))
            ->attach(new Importer\Mapper\DownloadCategory($downloadCategoryRepository))
            ->attach(new Importer\Mapper\Feedback($feedbackRepository))
            ->attach(new Importer\Mapper\FeedbackCategory($feedbackCategoryRepository))
            ->attach(new Importer\Mapper\Language($languageRepository))
            ->attach(new Importer\Mapper\News($newsRepository))
            ->attach(new Importer\Mapper\NewsCategory($newsCategoryRepository))
            ->attach(new Importer\Mapper\Organization($organizationRepository))
            ->attach(new Importer\Mapper\Person($personRepository))
            ->attach(new Importer\Mapper\Product($productRepository))
            ->attach(new Importer\Mapper\TicketCategory($ticketCategoryRepository))
            ->attach(new Importer\Mapper\TicketDepartment($departmentRepository))
            ->attach(new Importer\Mapper\TicketWorkflow($ticketWorkflowRepository))
            ->attach(new Importer\Mapper\UserGroup($userGroupRepository));

        $importers = new Importer\Collection();
        $importers
            ->attach(new Importer\Person($mappers))
            ->attach(new Importer\Ticket($mappers));

        return new DeskProWriter($importers, $entity_manager);
    }
}
