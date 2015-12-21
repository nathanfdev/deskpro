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
namespace DeskPRO\Bundle\PortalBundle\Controller;

use Application\DeskPRO\Entity\SearchLog;
use Application\DeskPRO\Labels\ContentLabelCloud;
use Application\DeskPRO\NewSearch\SearchEngine\Result\ResultSet;
use Application\DeskPRO\NewSearch\SearchEngine\SearchContextFactory;
use Application\DeskPRO\People\PersonGuest;
use Application\DeskPRO\Search\StickyWordSearch;
use DeskPRO\Bundle\AppBundle\Pagerfanta\Adapter\DeskproSearchAdapter;
use Doctrine\ORM\EntityManager;
use Orb\Util\Numbers;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;

class SearchController extends AbstractController
{
    /**
     * This handles both an ajax version (for paging; i.e. "View More" button) and non-ajax for the actual initial
     * GET request of the search page.
     *
     * @Route("/search", name="portal_search")
     * @Route("/search", name="user_search")
     */
    public function indexAction(Request $request)
    {
        $q = $request->get('q');

        $is_search      = false;
        $person         = $this->getUser() ?: new PersonGuest();
        $sticky_results = array();
        $results        = array();
        $total          = 0;
        $cur_page       = $request->get('page', 1);
        $ajax           = $request->isXmlHttpRequest();
        $per_page       = $ajax ? 10 : 2;
        $type           = $ajax ? $request->get('type', null) : null;

        if ($q) {
            $is_search = true;

            $results = $this->fetchSearchResults($request, $type ? [$type] : null, $person, $q, $cur_page, $per_page);

            $searchlog             = SearchLog::create($q, count($results) + count($sticky_results));
            $searchlog->person     = $this->getUser();
            $searchlog->ip_address = $request->getClientIp();
            $this->getEm()->transactional(
                function (EntityManager $em) use ($searchlog) {
                    $em->persist($searchlog);
                    $em->flush();
                }
            );

            $request->getSession()->set('last_searchlog_id', $searchlog->id);
        }

        $combined_counts = ['total_results' => 0];
        foreach ($results as $result) {
            $pageinfo = $result['pageinfo'];
            $combined_counts['total_results'] += $pageinfo['total_results'];
        }

        if ($ajax) {
            return $this->renderThemeView(
                'Theme:Search:search_results_ajax.html.twig',
                array(
                    'is_search'  => $is_search,
                    'result_set' => $results[$type],
                )
            );
        }

        //$pagination = new Pagerfanta(new DeskproSearchAdapter($pageinfo));
        //$pagination->setMaxPerPage((int) $pageinfo['per_page']);
        //$pagination->setCurrentPage((int) $pageinfo['curpage']);

        $breadcrumbs = $this->getBreadcrumbGenerator()->buildSearch($q);

        return $this->renderThemeView(
            'Theme:Search:search_results.html.twig',
            array(
                'is_search'      => $is_search,
                'results'        => $results,
                'sticky_results' => $sticky_results,
                'query'          => $q,
                'num_results'    => $total,
                'breadcrumbs'    => $breadcrumbs,
                'page_title'     => $this->createPageTitle()->search(),
                'combined'       => $combined_counts,
            )
        );
    }

    /**
     * @Route("/search/omni", name="portal_omnisearch")
     */
    public function omniSearchAction(Request $request)
    {
        $q = $request->get('q');

        $person   = $this->getUser() ?: new PersonGuest();
        $cur_page = $request->get('page', 1);
        $per_page = 10;
        $types    = $request->get('types', null);

        $omnisearch_results = $this->fetchSerializedSearchResults($request, $types, $person, $q, $cur_page, $per_page);

        return $this->makeJsonResponse($omnisearch_results);
    }

    /**
     * @Route("/search/labels/{type}/{label}", name="portal_search_labels", defaults={"type": "all", "label": ""}, requirements={"label":".*"})
     * @Route("/search/labels/{type}/{label}", name="user_search_labels", defaults={"type": "all", "label": ""}, requirements={"label":".*"})
     */
    public function labelSearchAction(Request $request, $type, $label)
    {
        if ($request->getMethod() === 'POST') {
            $t = $request->request->get('type');
            $l = $request->request->get('label');
            if (!in_array($type, array(
                    'all',
                    'articles',
                    'feedback',
                    'downloads',
                    'news', ))
            ) {
                $type = 'all';
            }

            return $this->redirectToRoute('portal_search_labels', array('type' => $t, 'label' => $l));
        }

        if (!$type or !in_array($type, array('all', 'articles', 'feedback', 'downloads', 'news'))) {
            $type = 'all';
        }

        $total    = 0;
        $per_page = 25;
        $cur_page = $request->query->get('page', 1);

        switch ($type) {
            case 'all':
                $search_types = array('article', 'feedback', 'download', 'news');
                break;
            case 'articles':
                $search_types = array('article');
                break;
            case 'feedback':
                $search_types = array('feedback');
                break;
            case 'downloads':
                $search_types = array('download');
                break;
            case 'news':
                $search_types = array('news');
                break;
            default:
                $search_types = array();
        }

        $results  = null;
        $pageinfo = null;
        if ($label) {
            $search_adapter = $this->get('deskpro.search_adapter');
            $search_adapter->setPersonContext($this->getCurrentPerson());
            $result_set = $search_adapter->getContentSearcher()->labelled(array($label), $per_page, $cur_page, $search_types);
            $results    = $search_adapter->getResultSetObjects($result_set, true);

            $total    = $result_set->totalCount();
            $pageinfo = Numbers::getPaginationPages($total, $cur_page, $per_page);
        }

        #------------------------------
        # Make combined search cloud
        #------------------------------

        $content_cloud = new ContentLabelCloud();
        $cloud         = $content_cloud->getCloud();

        $pagination = new Pagerfanta(new DeskproSearchAdapter($pageinfo ?: array()));
        $pagination->setMaxPerPage($pageinfo ? (int) $pageinfo['per_page'] : $per_page);
        $pagination->setCurrentPage($pageinfo ? (int) $pageinfo['curpage'] : $cur_page);

        $breadcrumbs = $this->getBreadcrumbGenerator()->buildLabelSearch($type, $label);

        return $this->renderThemeView(
            'Theme:Search:search_labels_results.html.twig',
            array(
                'pager'       => $pagination,
                'cloud'       => $cloud,
                'label'       => $label,
                'results'     => $results,
                'type'        => $type,
                'pageinfo'    => $pageinfo,
                'num_results' => $total,
                'breadcrumbs' => $breadcrumbs,
                'page_title'  => $this->createPageTitle()->labelSearch(),
            )
        );
    }

    /**
     * @Route("/search/similar/{content_type}", name="portal_search_similar", defaults={"content_type":null})
     * @Route("/search/similar/{content_type}", name="user_search_similarto", defaults={"content_type":null})
     */
    public function similarToAction(Request $request, $content_type = null)
    {
        $content = $request->get('content', '');

        if (!$content) {
            return $this->makeJsonResponse(
                array(
                    'results' => array(),
                    'words'   => array(),
                )
            );
        }

        $allowed_types = array('article', 'news', 'download', 'feedback');

        if (null === $content_type) {
            $content_type = $allowed_types;
        } else {
            $content_type = array($content_type);
        }

        $person         = $this->getUser() ?: new PersonGuest();
        $se             = $this->get('search_engine');
        $contextFactory = new SearchContextFactory($this->getContainer());
        $context        = $contextFactory->createUserSearchContext($person);
        $sticky_search  = new StickyWordSearch($this->getEm());
        /** @var ResultSet $results */
        $results = $se->getUserSearch()->similarTo(
            $context,
            $content,
            array('limit_types' => $content_type)
        );

        $search_results = $results->getTypedResults();

        // filter out the unwanted types from response and get the "words" for allowed objects
        $property_accessor = PropertyAccess::createPropertyAccessor();
        $typed_results     = array();
        $words             = array();
        foreach ($search_results as $result) {
            if (isset($result['type']) && in_array($result['type'], $allowed_types)) {
                $typed_results[] = $result;

                $object = $result['object'];
                if (is_object($object)) {
                    $class = get_class($object);
                    $type  = 'DeskPRO:'.substr($class, strrpos($class, '\\') + 1);
                    $id    = $property_accessor->getValue($object, 'id');
                    foreach ($sticky_search->getStickyWords($type, $id) as $word) {
                        if (count($words) < 100) {
                            $words[] = $word;
                        }
                    }
                }
            }
        }
        $serialized_results = $this->get('portal_search_serializer')->serializeArray($typed_results);

        return $this->makeJsonResponse(
            array(
                'results' => $serialized_results,
                'words'   => $words,
            )
        );
    }

    /**
     * @param Request $request
     * @param $type
     * @param $q
     * @param $person
     * @param $cur_page
     * @param $per_page
     *
     * @throws \Exception
     *
     * @return array
     */
    protected function doSearch(Request $request, $type, $q, $person, $cur_page, $per_page, $context)
    {
        $total   = 0;
        $results = [];

        if ($q) {
            $se = $this->get('search_engine');

            /** @var \Application\DeskPRO\NewSearch\SearchEngine\Result\ResultSet $result_set */
            $result_set = $se->getUserSearch()->search(
                $context,
                $q,
               ['page' => $cur_page, 'per_page' => $per_page, 'limit_types' => array($type)]
            );

            $total   = $result_set->getTotal();
            $results = $result_set->getTypedResults();

            $sticky_search = new StickyWordSearch($this->getEm());
            $sticky_search->setPersonContext($person);
            $sticky_results = $sticky_search->getResults($q, 5);

            if ($sticky_results) {
                $got_sticky = array();
                foreach ($sticky_results as $sitem) {
                    ++$total;
                    $got_sticky[get_class($sitem['object']).$sitem['object']->getId()] = true;
                }
                $results = array_filter(
                    $results,
                    function ($r) use ($got_sticky) {
                        return !isset($got_sticky[get_class($r['object']).$r['object']->getId()]);
                    }
                );
            }

            $searchlog             = SearchLog::create($q, count($results) + count($sticky_results));
            $searchlog->person     = $this->getUser();
            $searchlog->ip_address = $request->getClientIp();
            $this->getEm()->transactional(
                function (EntityManager $em) use ($searchlog) {
                    $em->persist($searchlog);
                    $em->flush();
                }
            );

            $request->getSession()->set('last_searchlog_id', $searchlog->id);
        }

        $pageinfo = Numbers::getPaginationPages($total, $cur_page, $per_page);

        return [$pageinfo, $results];
    }

    /**
     * @param Request $request
     * @param $types
     * @param $person
     * @param $q
     * @param $cur_page
     * @param $per_page
     *
     * @return array
     */
    private function fetchSearchResults(Request $request, $types, $person, $q, $cur_page, $per_page)
    {
        ////////////////////////////////////////////////////////////////////////
        // search types
        $allowed_search_types = array('article', 'news', 'download', 'feedback');
        if (!$limit_types_array = $types) {
            $limit_types_array = $allowed_search_types;
        }
        if (!is_array($limit_types_array)) {
            $limit_types_array = explode(',', $limit_types_array);
        }
        $limit_types_array = array_filter($limit_types_array, function ($value) use ($allowed_search_types) {
            return in_array($value, $allowed_search_types);
        });

        $contextFactory = new SearchContextFactory($this->getContainer());
        $context        = $contextFactory->createUserSearchContext($person);

        $omnisearch_results = [];
        foreach ($limit_types_array as $type) {
            list($pageinfo, $results) = $this->doSearch(
                $request,
                $type,
                $q,
                $person,
                $cur_page,
                $per_page,
                $context
            );
            $omnisearch_results[$type] = ['results' => $results, 'pageinfo' => $pageinfo];
        }

        return $omnisearch_results;
    }

    private function fetchSerializedSearchResults(Request $request, $types, $person, $q, $cur_page, $per_page)
    {
        $results = $this->fetchSearchResults($request, $types, $person, $q, $cur_page, $per_page);

        return $this->get('portal_search_serializer')->serializeArray($results);
    }
}
