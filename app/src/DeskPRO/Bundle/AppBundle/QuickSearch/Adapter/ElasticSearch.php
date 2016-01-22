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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\QuickSearch\Adapter;

use Application\DeskPRO\NewSearch\Repository;
use DeskPRO\Bundle\AppBundle\QuickSearch\QuickSearchRequest;
use Doctrine\ORM\EntityManager;
use FOS\ElasticaBundle\Doctrine\RepositoryManager;

/**
 * Class ElasticSearch.
 */
class ElasticSearch implements AdapterInterface
{
    /**
     * @var RepositoryManager
     */
    private $elastica_manager;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param RepositoryManager $elastica_manager
     * @param EntityManager     $em
     */
    public function __construct(RepositoryManager $elastica_manager, EntityManager $em)
    {
        $this->elastica_manager = $elastica_manager;
        $this->em               = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function searchArticles(QuickSearchRequest $request)
    {
        /** @var Repository\ArticleRepository $repository */
        $repository = $this->elastica_manager->getRepository('DeskPRO:Article');

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function searchDownloads(QuickSearchRequest $request)
    {
        /** @var Repository\DownloadRepository $repository */
        $repository = $this->elastica_manager->getRepository('DeskPRO:Download');

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function searchFeedback(QuickSearchRequest $request)
    {
        /** @var Repository\FeedbackRepository $repository */
        $repository = $this->elastica_manager->getRepository('DeskPRO:Feedback');

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function searchNews(QuickSearchRequest $request)
    {
        /** @var Repository\NewsRepository $repository */
        $repository = $this->elastica_manager->getRepository('DeskPRO:News');

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function searchTickets(QuickSearchRequest $request)
    {
        /** @var Repository\TicketRepository $repository */
        $repository = $this->elastica_manager->getRepository('DeskPRO:Ticket');
        $repository->setPersonContext($request->getPerson());

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function searchPeople(QuickSearchRequest $request)
    {
        /** @var Repository\PersonRepository $repository */
        $repository = $this->elastica_manager->getRepository('DeskPRO:Person');

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function searchOrganizations(QuickSearchRequest $request)
    {
        /** @var Repository\OrganizationRepository $repository */
        $repository = $this->elastica_manager->getRepository('DeskPRO:Organization');

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function searchChatConversations(QuickSearchRequest $request)
    {
        /** @var Repository\ChatConversationRepository $repository */
        $repository = $this->elastica_manager->getRepository('DeskPRO:ChatConversation');

        return [];
    }
}
