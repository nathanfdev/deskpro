<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Controller\Helper;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\ResultCache;
use Application\DeskPRO\Searcher\DownloadSearch;
use Application\DeskPRO\UI\RuleBuilder;
use Orb\Util\Arrays;

class DownloadResults
{
    /**
     * @var Application\AgentBundle\Controller\AbstractController
     */
    protected $controller;

    /**
     * @var array
     */
    protected $download_ids = [];

    /**
     * @var array
     */
    protected $orderBy = null;

    /**
     * @var \Application\DeskPRO\Entity\ResultCache
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
     * @return \Application\AgentBundle\Controller\Helper\DownloadResults
     */
    public static function newFromRequest($controller, array $options = [])
    {
        $resultCache = false;

        if (!$orderBy = $controller->in->getString('order_by')) {
            $orderBy = $controller->person->getPref('agent.ui.download-filter-order-by.0');
        }

        if ($controller->in->getUint('cache_id')) {
            $repo        = $caches        = App::getEntityRepository(ResultCache::class);
            $resultCache = $repo->find($controller->in->getUint('cache_id'));
            if (
                $resultCache['person_id'] != $controller->person['id']
                // the right way to compare is to use whole criteria, but right now downloads are not using it
                // more then this it need to be refactored at all, cause result_cache is almost useless, you can't fetch
                // it without additional checks
                || $resultCache['criteria']['order_by'] != $orderBy
            ) {
                $resultCache = false;
            }

            if (!$resultCache) {
                $caches = $repo->findBy(['person' => $controller->person, 'results_type' => 'download']);
                foreach ($caches as $cache) {
                    if (
                        isset($cache['extra']['category_id'])
                        && $cache['criteria']['order_by'] === $orderBy
                        && $cache['extra']['category_id'] === $options['category']['id']
                    ) {
                        $resultCache = $cache;
                        break;
                    }
                }
            }
        }

        //------------------------------
        // If there's no result set, we're running it for the first time
        //------------------------------

        if (!$resultCache) {
            $term_rules = RuleBuilder::newTermsBuilder();

            if (isset($options['category'])) {
                $terms = [
                    ['type' => 'category_specific', 'op' => 'is', 'options' => ['category' => $options['category']['id']]],
                    ['type' => 'agent_list', 'op' => 'is', 'options' => 1],
                ];
            } elseif (isset($options['show_all'])) {
                $terms = [
                    ['type' => 'agent_list', 'op' => 'is', 'options' => 1],
                ];
            } else {
                $form_terms = $controller->in->getCleanValueArray('terms', 'raw', 'string');
                $form_terms = Arrays::removeFalsey($form_terms);

                $terms = $term_rules->readForm($form_terms);
            }

            $searcher = new DownloadSearch();
            foreach ($terms as $term) {
                $searcher->addTerm($term['type'], $term['op'], $term['options']);
            }

            if ($orderBy) {
                $searcher->setOrderByCode($orderBy);
            } elseif (!empty($options['default_order_by'])) {
                $searcher->setOrderByCode($options['default_order_by']);
            } else {
                $orderBy = 'downloads.date_created:desc';
                $searcher->setOrderByCode($orderBy);
            }

            $results = $searcher->getMatches();

            $resultCache             = new ResultCache();
            $resultCache['person']   = $controller->person;
            $resultCache['criteria'] = ['terms' => $searcher->getTerms(), 'order_by' => $orderBy];
            $resultCache['extra']    = [
                'summary'     => $searcher->getSummary(),
                'category_id' => $options['category']['id'],
            ];
            $resultCache['results']      = $results;
            $resultCache['num_results']  = count($results);
            $resultCache['results_type'] = 'download';

            $controller->em->persist($resultCache);
            $controller->em->flush();
        }

        return new self($controller, $resultCache);
    }

    /**
     * @return \Application\AgentBundle\Controller\Helper\DownloadResults
     */
    public static function newFromResultCache($controller, ResultCache $resultCache)
    {
        $helper = new self($controller);
        $helper->setDownloadIds($resultCache['results']);

        return $helper;
    }

    public function __construct($controller, ResultCache $resultCache = null)
    {
        $this->controller = $controller;

        if ($resultCache) {
            $this->result_cache = $resultCache;
            $this->setDownloadIds($resultCache['results']);
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
     * @param array $download_ids
     */
    public function setDownloadIds(array $download_ids)
    {
        $this->download_ids = $download_ids;
    }

    /**
     * @return array
     */
    public function getDownloadIds()
    {
        return $this->download_ids;
    }

    /**
     * @return array
     */
    public function getDownloadsForPage($page, $per_page = 50)
    {
        return $this->_getPageFromDownloadIds($this->getDownloadIds(), $page, $per_page);
    }

    public function getForPage($page, $per_page = 50)
    {
        return $this->_getPageFromDownloadIds($this->getDownloadIds(), $page, $per_page);
    }

    protected function _getPageFromDownloadIds(array $download_ids, $page, $per_page)
    {
        $page_download_ids = Arrays::getPageChunk($download_ids, $page, $per_page);
        $downloads_raw     = App::getEntityRepository('DeskPRO:Download')->getByResultIds($page_download_ids);

        $downloads = [];
        foreach ($download_ids as $tid) {
            if (isset($downloads_raw[$tid])) {
                $downloads[$tid] = $downloads_raw[$tid];
            }
        }

        return $downloads;
    }
}
