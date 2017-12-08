<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

use Application\DeskPRO\Entity;
use Application\DeskPRO\Entity\SearchLog;
use Application\DeskPRO\Labels\ContentLabelCloud;
use Application\DeskPRO\NewSearch\SearchEngine\Result\ResultSet;
use Application\DeskPRO\NewSearch\SearchEngine\SearchContextFactory;
use Application\DeskPRO\NewSearch\SearchEngine\SearchEngine;
use Application\DeskPRO\NewSearch\SearchEngine\UserSearchInterface;
use Application\DeskPRO\People\PersonGuest;
use Application\DeskPRO\Search\Adapter\AbstractAdapter;
use Application\DeskPRO\Search\StickyWordSearch;
use DeskPRO\Bundle\AppBundle\Pagerfanta\Adapter\DeskproSearchAdapter;
use DeskPRO\Bundle\AppBundle\Settings\BrandAwareSettingsResolver;
use DeskPRO\Bundle\AppBundle\Settings\PortalSettingsResolver;
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
     *
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $q = $request->get('q');

        $isSearch      = false;
        $person        = $this->getUser() ?: new PersonGuest();
        $stickyResults = [];
        $results       = [];
        $total         = 0;
        $curPage       = $request->get('page', 1);
        $ajax          = $request->isXmlHttpRequest();
        $perPage       = $ajax ? 10 : 2;
        $type          = $ajax ? $request->get('type', null) : null;

        if ($q) {
            $isSearch = true;

            $results = $this->fetchSearchResults($request, $type ? [$type] : null, $person, $q, $curPage, $perPage);

            $searchlog             = SearchLog::create($q, count($results) + count($stickyResults));
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

        $combinedCounts = ['total_results' => 0];
        foreach ($results as $result) {
            $pageinfo = $result['pageinfo'];
            $combinedCounts['total_results'] += $pageinfo['total_results'];
        }

        if ($ajax) {
            return $this->renderThemeView(
                'Theme:Search:search_results_ajax.html.twig',
                [
                    'is_search'  => $isSearch,
                    'result_set' => isset($results[$type]) ? $results[$type] : null,
                ]
            );
        }

        //$pagination = new Pagerfanta(new DeskproSearchAdapter($pageinfo));
        //$pagination->setMaxPerPage((int) $pageinfo['per_page']);
        //$pagination->setCurrentPage((int) $pageinfo['curpage']);

        $breadcrumbs = $this->getBreadcrumbGenerator()->buildSearch($q);

        return $this->renderThemeView(
            'Theme:Search:search_results.html.twig',
            [
                'is_search'      => $isSearch,
                'results'        => $results,
                'sticky_results' => $stickyResults,
                'query'          => $q,
                'num_results'    => $total,
                'breadcrumbs'    => $breadcrumbs,
                'page_title'     => $this->createPageTitle()->search(),
                'combined'       => $combinedCounts,
            ]
        );
    }

    /**
     * @Route("/search/omni", name="portal_omnisearch")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function omniSearchAction(Request $request)
    {
        $q = $request->get('q');

        $person  = $this->getUser() ?: new PersonGuest();
        $curPage = $request->get('page', 1);
        $perPage = 10;
        $types   = $request->get('types', null);

        $omnisearchResults = $this->fetchSerializedSearchResults($request, $types, $person, $q, $curPage, $perPage);

        return $this->makeJsonResponse($omnisearchResults);
    }

    /**
     * @Route("/search/labels/{type}/{label}", name="portal_search_labels", defaults={"type": "all", "label": ""}, requirements={"label":".*"})
     * @Route("/search/labels/{type}/{label}", name="user_search_labels", defaults={"type": "all", "label": ""}, requirements={"label":".*"})
     *
     * @param Request $request
     * @param         $type
     * @param         $label
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function labelSearchAction(Request $request, $type, $label)
    {
        if ($request->getMethod() === 'POST') {
            $type  = $request->request->get('type');
            $label = $request->request->get('label');

            return $this->redirectToRoute('portal_search_labels', ['type' => $type, 'label' => $label]);
        }

        if (!$type or !in_array($type, ['all', 'articles', 'feedback', 'downloads', 'news'])) {
            $type = 'all';
        }

        $total   = 0;
        $perPage = 25;
        $curPage = $request->query->getInt('page', 1);

        switch ($type) {
            case 'all':
                $searchTypes = ['article', 'feedback', 'download', 'news'];
                break;
            case 'articles':
                $searchTypes = ['article'];
                break;
            case 'feedback':
                $searchTypes = ['feedback'];
                break;
            case 'downloads':
                $searchTypes = ['download'];
                break;
            case 'news':
                $searchTypes = ['news'];
                break;
            default:
                $searchTypes = [];
        }

        $results  = null;
        $pageinfo = null;
        if ($label) {
            /** @var AbstractAdapter $searchAdapter */
            $searchAdapter = $this->get('deskpro.search_adapter');
            $searchAdapter->setPersonContext($this->getCurrentPerson());
            $resultSet = $searchAdapter->getContentSearcher()->labelled([$label], $perPage, $curPage, $searchTypes);
            $results   = $searchAdapter->getResultSetObjects($resultSet, true);

            $total    = $resultSet->totalCount();
            $pageinfo = Numbers::getPaginationPages($total, $curPage, $perPage);
        }

        //------------------------------
        // Make combined search cloud
        //------------------------------

        $contentCloud = new ContentLabelCloud();
        $cloud        = $contentCloud->getCloud();

        $pagination = new Pagerfanta(new DeskproSearchAdapter($pageinfo ?: []));
        $pagination->setMaxPerPage($pageinfo ? (int) $pageinfo['per_page'] : $perPage);
        $pagination->setCurrentPage($pageinfo ? (int) $pageinfo['curpage'] : $curPage);

        $breadcrumbs = $this->getBreadcrumbGenerator()->buildLabelSearch($type, $label);

        return $this->renderThemeView(
            'Theme:Search:search_labels_results.html.twig',
            [
                'pager'       => $pagination,
                'cloud'       => $cloud,
                'label'       => $label,
                'results'     => $results,
                'type'        => $type,
                'pageinfo'    => $pageinfo,
                'num_results' => $total,
                'breadcrumbs' => $breadcrumbs,
                'page_title'  => $this->createPageTitle()->labelSearch(),
            ]
        );
    }

    /**
     * @Route("/search/similar/{content_type}", name="portal_search_similar", defaults={"content_type":null})
     * @Route("/search/similar/{content_type}", name="user_search_similarto", defaults={"content_type":null})
     *
     * @param Request $request
     * @param null    $contentType
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function similarToAction(Request $request, $contentType = null)
    {
        $content = $request->get('content', '');

        if (!$content) {
            return $this->makeJsonResponse(
                [
                    'results' => [],
                    'words'   => [],
                ]
            );
        }

        $allowedTypes = ['article', 'news', 'download', 'feedback', 'topic'];

        if (null === $contentType) {
            $contentType = $allowedTypes;
        } else {
            $contentType = [$contentType];
        }

        $person         = $this->getUser() ?: new PersonGuest();
        $se             = $this->get('search_engine');
        $contextFactory = new SearchContextFactory($this->getContainer());
        $context        = $contextFactory->createUserSearchContext($person);
        $stickySearch   = new StickyWordSearch($this->getEm());
        /** @var ResultSet $results */
        $results = $se->getUserSearch()->similarTo(
            $context,
            $content,
            ['limit_types' => $contentType]
        );

        $searchResults = $results->getTypedResults();

        // filter out the unwanted types from response and get the "words" for allowed objects
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $typedResults     = [];
        $words            = [];
        foreach ($searchResults as $result) {
            if (isset($result['type']) && in_array($result['type'], $allowedTypes)) {
                $typedResults[] = $result;

                $object = $result['object'];
                if (is_object($object)) {
                    $class = get_class($object);
                    $type  = 'DeskPRO:'.substr($class, strrpos($class, '\\') + 1);
                    $id    = $propertyAccessor->getValue($object, 'id');
                    foreach ($stickySearch->getStickyWords($type, $id) as $word) {
                        if (count($words) < 100) {
                            $words[] = $word;
                        }
                    }
                }
            }
        }
        $serialized_results = $this->get('portal_search_serializer')->serializeArray($typedResults);

        return $this->makeJsonResponse(
            [
                'results' => $serialized_results,
                'words'   => $words,
            ]
        );
    }

    /**
     * @param Request $request
     * @param         $type
     * @param         $q
     * @param         $person
     * @param         $curPage
     * @param         $perPage
     * @param         $context
     *
     * @throws \Exception
     *
     * @return array
     */
    protected function doSearch(Request $request, $type, $q, $person, $curPage, $perPage, $context)
    {
        $total   = 0;
        $results = [];

        if ($q) {
            /** @var SearchEngine $se */
            $se = $this->get('search_engine');

            /** @var UserSearchInterface $userSearch */
            $userSearch = $se->getUserSearch();

            /** @var \Application\DeskPRO\NewSearch\SearchEngine\Result\ResultSet $resultSet */
            $resultSet = $userSearch->search(
                $context,
                $q,
                ['page' => $curPage, 'per_page' => $perPage, 'limit_types' => [$type]]
            );

            $total   = $resultSet->getTotal();
            $results = $resultSet->getTypedResults();

            $stickySearch = new StickyWordSearch($this->getEm());
            $stickySearch->setPersonContext($person);
            $stickyResults = $stickySearch->getResults($q, 5, [$type]);

            if ($stickyResults) {
                $gotSticky = [];
                foreach ($stickyResults as $sItem) {
                    ++$total;
                    $gotSticky[get_class($sItem['object']).$sItem['object']->getId()] = true;
                }
                // remove results that might have matched normally
                $results = array_filter(
                    $results,
                    function ($r) use ($gotSticky) {
                        return !isset($gotSticky[get_class($r['object']).$r['object']->getId()]);
                    }
                );
                // then add the sticky results to the top
                foreach ($stickyResults as $sItem) {
                    array_unshift($results, [
                        'type'   => $this->getTypeKey($sItem['object']),
                        'object' => $sItem['object'],
                    ]);
                }
            }

            $searchLog             = SearchLog::create($q, count($results) + count($stickyResults));
            $searchLog->person     = $this->getUser();
            $searchLog->ip_address = $request->getClientIp();
            $this->getEm()->transactional(
                function (EntityManager $em) use ($searchLog) {
                    $em->persist($searchLog);
                    $em->flush();
                }
            );

            $request->getSession()->set('last_searchlog_id', $searchLog->id);
        }

        $pageInfo = Numbers::getPaginationPages($total, $curPage, $perPage);

        return [$pageInfo, $results];
    }

    private function getTypeKey($r)
    {
        if ($r instanceof Entity\Article) {
            $type = 'article';
        } elseif ($r instanceof Entity\News) {
            $type = 'news';
        } elseif ($r instanceof Entity\Download) {
            $type = 'download';
        } elseif ($r instanceof Entity\Feedback) {
            $type = 'feedback';
        } elseif ($r instanceof Entity\Topic) {
            $type = 'topic';
        } elseif ($r instanceof Entity\Ticket) {
            $type = 'ticket';
        } elseif ($r instanceof Entity\Person) {
            $type = 'person';
        } elseif ($r instanceof Entity\ChatConversation) {
            $type = 'chat_conversation';
        } else {
            $type = 'unknown';
        }

        return $type;
    }

    /**
     * @param Request $request
     * @param         $types
     * @param         $person
     * @param         $q
     * @param         $curPage
     * @param         $perPage
     *
     * @return array
     */
    private function fetchSearchResults(Request $request, $types, $person, $q, $curPage, $perPage)
    {
        ////////////////////////////////////////////////////////////////////////
        // search types
        $allowedSearchTypes = ['article', 'news', 'download', 'feedback', 'topic', 'ticket', 'chat_conversation'];
        if (!$limitTypesArray = $types) {
            $limitTypesArray = $allowedSearchTypes;
        }
        if (!is_array($limitTypesArray)) {
            $limitTypesArray = explode(',', $limitTypesArray);
        }
        $limitTypesArray = array_filter($limitTypesArray, function ($value) use ($allowedSearchTypes) {
            return in_array($value, $allowedSearchTypes);
        });

        /** @var BrandAwareSettingsResolver $brandSettingsResolver */
        $brandSettingsResolver = $this->get('brand_aware_settings_resolver');

        $appSettings = [
            'article'  => PortalSettingsResolver::APPS_KB,
            'news'     => PortalSettingsResolver::APPS_NEWS,
            'download' => PortalSettingsResolver::APPS_DOWNLOADS,
            'feedback' => PortalSettingsResolver::APPS_FEEDBACK,
            'topic'    => PortalSettingsResolver::APPS_GUIDES,
        ];

        $limitTypesArray = array_filter($limitTypesArray,
            function ($value) use ($allowedSearchTypes, $appSettings, $brandSettingsResolver) {
                if (!isset($appSettings[$value])) {
                    return true;
                }

                return $brandSettingsResolver->getSetting($appSettings[$value]);
            });

        $contextFactory = new SearchContextFactory($this->getContainer());
        $context        = $contextFactory->createUserSearchContext($person);

        $omnisearchResults = [];
        foreach ($limitTypesArray as $type) {
            list($pageInfo, $results) = $this->doSearch(
                $request,
                $type,
                $q,
                $person,
                $curPage,
                $perPage,
                $context
            );
            $omnisearchResults[$type] = ['results' => $results, 'pageinfo' => $pageInfo];
        }

        return $omnisearchResults;
    }

    private function fetchSerializedSearchResults(Request $request, $types, $person, $q, $curPage, $perPage)
    {
        $results = $this->fetchSearchResults($request, $types, $person, $q, $curPage, $perPage);

        return $this->get('portal_search_serializer')->serializeArray($results);
    }
}
