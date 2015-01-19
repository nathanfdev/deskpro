<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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

namespace Application\ImportBundle\Generator;

use Application\DeskPRO\EntityRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Importer generator factory
 *
 * Class GeneratorFactory
 * @package Application\ImportBundle\Generator
 */
class GeneratorFactory
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return GeneratorInterface
     */
    public function createGenerator()
    {
        return new Generator(
            $this->createJsonWriter(),
            $this->createExportersCollection(),
            $this->createValidatorsCollection(),
            $this->createMappersCollection()
        );
    }

    /**
     * Create a json writer
     *
     * @return Writer\Json\JsonWriter
     */
    public function createJsonWriter()
    {
        $mapping = new Writer\Json\Destination\Collection();
        $mapping
            ->attach(new Writer\Json\Destination\Person())
            ->attach(new Writer\Json\Destination\Ticket());

        return new Writer\Json\JsonWriter($mapping);
    }

    /**
     * Returns a collection of exporters
     *
     * @return Exporter\Collection
     */
    private function createExportersCollection()
    {
        $csvFactory      = new Exporter\CsvFactory($this->container);
        $jsonFactory     = new Exporter\JsonFactory($this->container);
        $osTicketFactory = new Exporter\OsTicketFactory($this->container);
        $zenDeskFactory  = new Exporter\ZenDeskFactory($this->container);

        $exporters = new Exporter\Collection();
        $exporters
            ->attach($csvFactory->createExporter())
            ->attach($jsonFactory->createExporter())
            ->attach($osTicketFactory->createExporter())
            ->attach($zenDeskFactory->createExporter());

        return $exporters;
    }

    /**
     * Returns a collection of validators
     *
     * @return Validator\Collection
     */
    private function createValidatorsCollection()
    {
        /** @var \Symfony\Component\Validator\Validator $validator */
        $validator  = $this->container->get('validator');
        $validators = new Validator\Collection();
        $validators
            ->attach(new Validator\Person($validator))
            ->attach(new Validator\Ticket($validator));

        return $validators;
    }

    /**
     * Returns a collection of record mappers
     *
     * @return Mapper\Collection
     */
    private function createMappersCollection()
    {
        /** @var \Doctrine\Bundle\DoctrineBundle\Registry $doctrine */
        $doctrine = $this->container->get('doctrine');

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

        $mappers = new Mapper\Collection();
        $mappers
            ->attach(new Mapper\Article($articleRepository))
            ->attach(new Mapper\ArticleCategory($articleCategoryRepository))
            ->attach(new Mapper\CustomDefPerson($customDefPersonRepository))
            ->attach(new Mapper\CustomDefTicket($customDefTicketRepository))
            ->attach(new Mapper\Department($departmentRepository))
            ->attach(new Mapper\Download($downloadRepository))
            ->attach(new Mapper\DownloadCategory($downloadCategoryRepository))
            ->attach(new Mapper\Feedback($feedbackRepository))
            ->attach(new Mapper\FeedbackCategory($feedbackCategoryRepository))
            ->attach(new Mapper\Language($languageRepository))
            ->attach(new Mapper\News($newsRepository))
            ->attach(new Mapper\NewsCategory($newsCategoryRepository))
            ->attach(new Mapper\Organization($organizationRepository))
            ->attach(new Mapper\Person($personRepository))
            ->attach(new Mapper\Product($productRepository))
            ->attach(new Mapper\TicketCategory($ticketCategoryRepository))
            ->attach(new Mapper\TicketDepartment($departmentRepository))
            ->attach(new Mapper\TicketWorkflow($ticketWorkflowRepository))
            ->attach(new Mapper\UserGroup($userGroupRepository));

        return $mappers;
    }
}
