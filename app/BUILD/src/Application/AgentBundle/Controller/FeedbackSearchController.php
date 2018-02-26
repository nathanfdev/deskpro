<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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
