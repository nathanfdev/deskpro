<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Controller\Helper;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\ResultCache;
use Application\DeskPRO\Searcher\NewsSearch;
use Application\DeskPRO\UI\RuleBuilder;
use Orb\Util\Arrays;

class NewsResults
{
    /**
     * @var Application\AgentBundle\Controller\AbstractController
     */
    protected $controller;

    /**
     * @var array
     */
    protected $news_ids = [];

    /**
     * @var array
     */
    protected $order_by = null;

    /**
     * @var \Application\DeskPRO\Entity\ResultCache
     */
    protected $result_cache;

    /**
     * $options can have:
     * - default_terms: For when viewing the page that you havent submitted
     * - specific_terms: Always added to the search
     * - default_order_by: The default order by for a page you havent submitted.
     *
     * @param  $controller
     * @param array $options
     *
     * @return \Application\AgentBundle\Controller\Helper\NewsResults
     */
    public static function newFromRequest($controller, array $options = [])
    {
        $result_cache = false;
        if ($controller->in->getUint('cache_id')) {
            $result_cache = App::getEntityRepository('DeskPRO:ResultCache')->find($controller->in->getUint('cache_id'));
            if ($result_cache['person_id'] != $controller->person['id']) {
                $result_cache = false;
            }
        }

        //------------------------------
        // If there's no result set, we're running it for the first time
        //------------------------------

        if (!$result_cache) {
            $term_rules = RuleBuilder::newTermsBuilder();

            // A category with this action is a shortcut for searching on the category,
            // and published
            if (isset($options['category'])) {
                $terms = [
                    ['type' => 'category_specific', 'op' => 'is', 'options' => ['category' => $options['category']['id']]],
                    ['type' => 'agent_list', 'op' => 'is', 'options' => 1],
                ];

            // "all" is published but no category term
            } elseif (isset($options['show_all'])) {
                $terms = [
                    ['type' => 'agent_list', 'op' => 'is', 'options' => 1],
                ];

            // Otherwise its a user filter with custom terms
            } else {
                $form_terms = $controller->in->getCleanValueArray('terms', 'raw', 'string');
                $form_terms = Arrays::removeFalsey($form_terms);

                $terms = $term_rules->readForm($form_terms);
            }

            $searcher = new NewsSearch();
            foreach ($terms as $term) {
                $searcher->addTerm($term['type'], $term['op'], $term['options']);
            }

            $order_by = $controller->in->getString('order_by');

            if ($order_by) {
                $searcher->setOrderByCode($order_by);
            }

            $results = $searcher->getMatches();

            $result_cache                = new ResultCache();
            $result_cache['person']      = $controller->person;
            $result_cache['criteria']    = ['terms' => $searcher->getTerms(), 'order_by' => $order_by];
            $result_cache['extra']       = ['summary' => $searcher->getSummary()];
            $result_cache['results']     = $results;
            $result_cache['num_results'] = count($results);

            $controller->em->persist($result_cache);
            $controller->em->flush();
        }

        return new self($controller, $result_cache);
    }

    /**
     * @return \Application\AgentBundle\Controller\Helper\NewsResults
     */
    public static function newFromResultCache($controller, ResultCache $result_cache)
    {
        $helper = new self($controller);
        $helper->setNewsIds($result_cache['results']);

        return $helper;
    }

    public function __construct($controller, ResultCache $result_cache = null)
    {
        $this->controller = $controller;

        if ($result_cache) {
            $this->result_cache = $result_cache;
            $this->setNewsIds($result_cache['results']);
        }
    }

    /**
     * @return \Application\DeskPRO\Entity\ResultCache
     */
    public function getResultCache()
    {
        return $this->result_cache;
    }

    /**
     * @param array $news_ids
     */
    public function setNewsIds(array $news_ids)
    {
        $this->news_ids = $news_ids;
    }

    /**
     * @return array
     */
    public function getNewsIds()
    {
        return $this->news_ids;
    }

    /**
     * @return array
     */
    public function getNewsForPage($page, $per_page = 50)
    {
        return $this->_getPageFromNewsIds($this->getNewsIds(), $page, $per_page);
    }

    public function getForPage($page, $per_page = 50)
    {
        return $this->_getPageFromNewsIds($this->getNewsIds(), $page, $per_page);
    }

    protected function _getPageFromNewsIds(array $news_ids, $page, $per_page)
    {
        $page_news_ids = Arrays::getPageChunk($news_ids, $page, $per_page);
        $news_raw      = App::getEntityRepository('DeskPRO:News')->getByResultIds($page_news_ids);

        // Real order that we got when executing the search
        $news = [];
        foreach ($news_ids as $tid) {
            if (isset($news_raw[$tid])) {
                $news[$tid] = $news_raw[$tid];
            }
        }

        return $news;
    }
}
