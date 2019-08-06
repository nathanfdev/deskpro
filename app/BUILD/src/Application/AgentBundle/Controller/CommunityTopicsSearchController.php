<?php

namespace Application\AgentBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\CommunityTopic;
use Application\DeskPRO\EntityRepository\CommunityTopic as CommunityTopicRepository;
use Application\DeskPRO\Searcher\CommunitySearch;
use DeskPRO\Component\Util\ListUtils;
use Orb\Util\Arrays;

/**
 * Handles CommunityTopic search json response.
 */
class CommunityTopicsSearchController extends AbstractController
{
    public function quickSearchAction()
    {
        $limit = $this->in->getUInt('limit');
        if (!$limit) {
            $limit = 10;
        }
        $limit = min($limit, 100);

        $q = $this->in->getString('q');
        if (!$q) {
            $q = $this->in->getString('term');
        }

        if (!$q) {
            return $this->createJsonResponse([]);
        }

        $searcher = new CommunitySearch();
        $searcher->setPerson($this->person);
        $searcher->setOrderBy('community_topics.date_created');
        $searcher->addTerm('deleted', 'not', 1);
        $searcher->addTerm('query', 'is', [
            'query' => $q,
        ]);

        $results = $searcher->getMatches();
        $results = Arrays::castToType($results, 'integer');
        $results = array_slice($results, 0, $limit);

        $output = [];

        if (ctype_digit($q)) {
            $communityTopicsById = App::getEntityRepository(CommunityTopic::class)->find($q);
            if ($communityTopicsById) {
                $results = ListUtils::filterOutValues($results, [$communityTopicsById->getId()]);
                array_unshift($output, $this->formatCommunityTopicResultRow($communityTopicsById));
            }
        }

        /** @var CommunityTopicRepository $entityRepository */
        $entityRepository = $this->get('doctrine.orm.default_entity_manager')->getRepository(CommunityTopic::class);
        foreach ($entityRepository->getByIds($results, true) as $topic) {
            //@TODO: prefetch Community Channels and StatusCategories
            $output[] = $this->formatCommunityTopicResultRow($topic);
        }

        return $this->createJsonResponse($output);
    }

    private function formatCommunityTopicResultRow(CommunityTopic $communityTopic)
    {
        return [
            'id'    => $communityTopic->getId(),
            'value' => $communityTopic->getId(),
            'title' => $communityTopic->getTitle(),
            'type'  => $communityTopic->getCategory() ? $communityTopic->getCategory()->getTitle() : '',
            //'status'  => $communityTopic->status_category ? $communityTopic->status_category->title : ''
        ];
    }
}
