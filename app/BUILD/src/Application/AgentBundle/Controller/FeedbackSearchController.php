<?php

namespace Application\AgentBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Searcher\FeedbackSearch;
use DeskPRO\Component\Util\ListUtils;
use Orb\Util\Arrays;

/**
 * Handles Feedback search json response.
 */
class FeedbackSearchController extends AbstractController
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

        $searcher = new FeedbackSearch();
        $searcher->setPerson($this->person);
        $searcher->setOrderBy('feedback.date_created');
        $searcher->addTerm('deleted', 'not', 1);
        $searcher->addTerm('query', 'is', [
            'query' => $q,
        ]);

        $results = $searcher->getMatches();
        $results = Arrays::castToType($results, 'integer');
        $results = array_slice($results, 0, $limit);

        $output = [];

        if (ctype_digit($q)) {
            $feedbackById = App::getEntityRepository(Feedback::class)->find($q);
            if ($feedbackById) {
                $results = ListUtils::filterOutValues($results, [$feedbackById->getId()]);
                array_unshift($output, $this->formatFeedbackResultRow($feedbackById));
            }
        }

        foreach (App::getEntityRepository(Feedback::class)->getByIds($results, true) as $feedback) {
            //@TODO: prefetch Feedback Categories and StatusCategories
            $output[] = $this->formatFeedbackResultRow($feedback);
        }

        return $this->createJsonResponse($output);
    }

    private function formatFeedbackResultRow(Feedback $feedback)
    {
        return [
            'id'    => $feedback->id,
            'value' => $feedback->id,
            'title' => $feedback->title,
            'type'  => $feedback->category ? $feedback->category->title : '',
            //'status'  => $feedback->status_category ? $feedback->status_category->title : ''
        ];
    }
}
