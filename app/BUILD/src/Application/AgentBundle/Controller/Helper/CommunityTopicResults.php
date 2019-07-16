<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Controller\Helper;

use Application\AgentBundle\Controller\AbstractController;
use Application\DeskPRO\Entity\CommunityTopic;
use Application\DeskPRO\Entity\ResultCache;
use Application\DeskPRO\Searcher\CommunitySearch;
use Application\DeskPRO\UI\RuleBuilder;
use Orb\Util\Arrays;

class CommunityTopicResults
{
    /**
     * @var AbstractController
     */
    protected $controller;

    /**
     * @var array
     */
    protected $topicIds = [];

    /**
     * @var array
     */
    protected $orderBy = null;

    /**
     * @var ResultCache
     */
    protected $resultCache;

    /**
     * $options can have:
     * - default_terms: For when viewing the page that you havent submitted
     * - specific_terms: Always added to the search
     * - default_order_by: The default order by for a page you havent submitted.
     *
     * @param  $controller
     * @param array $options
     *
     * @return CommunityTopicResults
     */
    public static function newFromRequest($controller, array $options = [])
    {
        $resultCache = false;
        if ($controller->in->getUint('cache_id')) {
            $resultCache = $controller->em->getRepository('DeskPRO:ResultCache')->find($controller->in->getUint('cache_id'));
            if ($resultCache['person_id'] != $controller->person['id']) {
                $resultCache = false;
            }
        }

        //------------------------------
        // If there's no result set, we're running it for the first time
        //------------------------------

        if (!$resultCache) {
            $termRules = RuleBuilder::newTermsBuilder();

            $formTerms = $controller->in->getCleanValueArray('terms', 'raw', 'string');
            $formTerms = Arrays::removeFalsey($formTerms);

            if (!$formTerms and !empty($options['default_terms'])) {
                $formTerms = $options['default_terms'];
            }

            if (!empty($options['specific_terms'])) {
                $formTerms = array_merge($formTerms, $options['specific_terms']);
            }

            $terms = $termRules->readForm($formTerms);

            $searcher = new CommunitySearch();
            foreach ($terms as $term) {
                $searcher->addTerm($term['type'], $term['op'], $term['options']);
            }

            $orderBy = $controller->in->getString('order_by');
            if (!$orderBy) {
                $orderBy = $controller->person->getPref('agent.ui.community-filter-order-by.0');
            }

            if ($orderBy) {
                $searcher->setOrderByCode($orderBy);
            } elseif (!empty($options['default_order_by'])) {
                $searcher->setOrderByCode($options['default_order_by']);
            } else {
                $orderBy = 'id:desc';
            }

            $results = $searcher->getMatches();

            $resultCache                = new ResultCache();
            $resultCache['person']      = $controller->person;
            $resultCache['criteria']    = ['terms' => $searcher->getTerms(), 'order_by' => $orderBy];
            $resultCache['results']     = $results;
            $resultCache['num_results'] = count($results);

            /*
             * Usually search forms terms are keyed arbitrarily (usually numerically).
             * The keys are discarded when read in by the RuleBuilder class above.
             * But in the CommunityTopicsController and template, we set specific keys
             * for terms so the values can be easily plugged back into the form.
             *
             * (See CommunityTopicsController setting 'specific_terms', and the 'filter-searhc-form' template)
             *
             * Usually search forms are made with the RuleBuilder JS widget, which
             * adds terms dynamically. But when we want a static form and just want
             * to plug values back in, we do it this way.
             */
            $resultCache['extra'] = [];

            $em = $controller->em;
            $em->persist($resultCache);
            $em->flush();
        }

        return new self($controller, $resultCache);
    }

    /**
     * @return \Application\AgentBundle\Controller\Helper\CommunityTopicResults
     */
    public static function newFromResultCache($controller, ResultCache $resultCache)
    {
        $helper = new self($controller);
        $helper->setTopicIds($resultCache['results']);

        return $helper;
    }

    public function __construct($controller, ResultCache $resultCache = null)
    {
        $this->controller = $controller;

        if ($resultCache) {
            $this->resultCache = $resultCache;
            $this->setTopicIds($resultCache['results']);
        }
    }

    /**
     * @return \Application\DeskPRO\Entity\ResultCache
     */
    public function getResultCache()
    {
        return $this->resultCache;
    }

    /**
     * Set ticket IDs for the search results.
     *
     * @param array $topicIds
     */
    public function setTopicIds(array $topicIds)
    {
        $this->topicIds = $topicIds;
    }

    /**
     * Get ticket IDs.
     *
     * @return array
     */
    public function getTopicIds()
    {
        return $this->topicIds;
    }

    /**
     * Get tickets for a particular page.
     *
     * @return array
     */
    public function getTopicsForPage($page, $perPage = 50)
    {
        return $this->_getPageFromTopicIds($this->getTopicIds(), $page, $perPage);
    }

    public function getForPage($page, $perPage = 50)
    {
        return $this->_getPageFromTopicIds($this->getTopicIds(), $page, $perPage);
    }

    protected function _getPageFromTopicIds(array $topicIds, $page, $perPage)
    {
        $pageTopicIds = Arrays::getPageChunk($topicIds, $page, $perPage);
        $topicsRaw    = $this->controller->em->getRepository(CommunityTopic::class)->getByIds($pageTopicIds);

        // - We'll get a page of results, but that actual page isn't going to be
        // sorted the way we want, because MySQL was just sent a list of ID's.
        // - So we'll re-create the array here according to the order they're supposed to be in.
        $topics = [];
        foreach ($topicIds as $tid) {
            if (isset($topicsRaw[$tid])) {
                $topics[$tid] = $topicsRaw[$tid];
            }
        }

        return $topics;
    }
}
