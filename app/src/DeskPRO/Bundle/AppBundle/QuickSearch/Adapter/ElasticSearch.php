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

use DeskPRO\Bundle\AppBundle\QuickSearch\QuickSearchRequest;
use Doctrine\ORM\EntityManager;
use FOS\ElasticaBundle\Doctrine\RepositoryManager;
use FOS\ElasticaBundle\Repository as ElasticaRepository;

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
        return $this->doElasticaRequest($request, 'DeskPRO:Article');
    }

    /**
     * {@inheritdoc}
     */
    public function searchDownloads(QuickSearchRequest $request)
    {
        return $this->doElasticaRequest($request, 'DeskPRO:Download');
    }

    /**
     * {@inheritdoc}
     */
    public function searchFeedback(QuickSearchRequest $request)
    {
        return $this->doElasticaRequest($request, 'DeskPRO:Feedback');
    }

    /**
     * {@inheritdoc}
     */
    public function searchNews(QuickSearchRequest $request)
    {
        return $this->doElasticaRequest($request, 'DeskPRO:News');
    }

    /**
     * {@inheritdoc}§
     */
    public function searchTickets(QuickSearchRequest $request)
    {
        $ids = [];

        /** @var \Application\DeskPRO\EntityRepository\Ticket $em_repository */
        $em_repository = $this->em->getRepository('DeskPRO:Ticket');

        if ($request->isTicketRef()) {
            $ticket = $em_repository->findTicketRef($request->getQuery());
            if ($ticket) {
                $ids[] = $ticket->getId();
            }
        } elseif ($request->isId()) {
            $ticket = $em_repository->findTicketId((int) $request->getQuery());
            if ($ticket) {
                $request->getPerson()->loadHelper('PermissionsManager');
                if ($request->getPerson()->PermissionsManager->TicketChecker->canView($ticket)) {
                    $ids[] = $ticket->getId();
                }
            }
        }

        return array_merge($ids, $this->doElasticaRequest($request, 'DeskPRO:Ticket'));
    }

    /**
     * {@inheritdoc}
     */
    public function searchPeople(QuickSearchRequest $request)
    {
        return $this->doElasticaRequest($request, 'DeskPRO:Person');
    }

    /**
     * {@inheritdoc}
     */
    public function searchOrganizations(QuickSearchRequest $request)
    {
        return $this->doElasticaRequest($request, 'DeskPRO:Organization');
    }

    /**
     * {@inheritdoc}
     */
    public function searchChatConversations(QuickSearchRequest $request)
    {
        return $this->doElasticaRequest($request, 'DeskPRO:ChatConversation');
    }

    /**
     * @param QuickSearchRequest $request
     * @param string             $repository_name
     *
     * @return int[]
     */
    protected function doElasticaRequest(QuickSearchRequest $request, $repository_name)
    {
        /** @var ElasticaRepository $repository */
        $repository = $this->elastica_manager->getRepository($repository_name);
        if (method_exists($repository, 'setPersonContext')) {
            $repository->setPersonContext($request->getPerson());
        }

        $result = $repository->find($request->getQuery(), null, [
            'sort_type' => $request->getSort(),
        ]);

        return $result;
    }
}
