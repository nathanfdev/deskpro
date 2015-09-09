<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Controller;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\SearchLog;
use Application\DeskPRO\NewSearch\SearchEngine\Result\ResultSet;
use Application\DeskPRO\NewSearch\SearchEngine\SearchContext;
use Application\DeskPRO\NewSearch\SearchEngine\SearchContextFactory;
use Application\DeskPRO\People\PersonGuest;
use Application\DeskPRO\Search\StickyWordSearch;
use DeskPRO\Bundle\AppBundle\Pagerfanta\Adapter\DeskproSearchAdapter;
use Doctrine\ORM\EntityManager;
use Orb\Util\Numbers;
use Pagerfanta\Adapter\NullAdapter;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;

class SearchController extends AbstractController
{
    /**
     * @Route("/search", name="portal_search")
     * @Route("/search", name="user_search")
     */
    public function indexAction(Request $request)
    {
        $q = $request->get('q');

        $is_search = false;
        $person = $this->getUser() ?: new PersonGuest();
        $sticky_results = array();
        $results = array();
        $total = 0;
        $cur_page = $request->get('page', 1);
        $per_page = 10;

        ////////////////////////////////////////////////////////////////////////
        // search types
        $allowed_search_types = array('article', 'news', 'download', 'feedback');
        if (!$limit_types_array = $request->get('types', null)) {
            $limit_types_array = $allowed_search_types;
        }
        if (!is_array($limit_types_array)) {
            $limit_types_array = explode(',', $limit_types_array);
        }
        $limit_types_array = array_filter($limit_types_array, function($value) use ($allowed_search_types) {
            return in_array($value, $allowed_search_types);
        });
        $limit_types = implode(',', $limit_types_array);

        if ($q) {
            $is_search = true;

            $se = $this->get('search_engine');
            $contextFactory = new SearchContextFactory($this->getContainer());
            $context = $contextFactory->createUserSearchContext($person);

            /** @var \Application\DeskPRO\NewSearch\SearchEngine\Result\ResultSet $result_set */
            $result_set = $se->getUserSearch()->search($context, $q, array('page' => $cur_page, 'per_page' => $per_page, 'limit_types' => $limit_types));

            $total = $result_set->getTotal();
            $results = $result_set->getTypedResults();

            $sticky_search = new StickyWordSearch($this->getEm());
            $sticky_search->setPersonContext($person);
            $sticky_results = $sticky_search->getResults($q, 5);

            if ($sticky_results) {
                $got_sticky = array();
                foreach ($sticky_results as $sitem) {
                    $total++;
                    $got_sticky[get_class($sitem['object']) . $sitem['object']->getId()] = true;
                }
                $results = array_filter(
                    $results,
                    function ($r) use ($got_sticky) {
                        return !isset($got_sticky[get_class($r['object']) . $r['object']->getId()]);
                    }
                );
            }

            $searchlog = SearchLog::create($q, count($results) + count($sticky_results));
            $searchlog->person = $this->getUser();
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

        if ($request->isXmlHttpRequest()) {
            $serialized_results = $this->get('portal_search_serializer')->serializeArray($results);
            return $this->makeJsonResponse(
                array(
                    'results' => $serialized_results,
                    'pageinfo'   => $pageinfo,
                )
            );
        }

        $pagination = new Pagerfanta(new DeskproSearchAdapter($pageinfo));
        $pagination->setMaxPerPage((int)$pageinfo['per_page']);
        $pagination->setCurrentPage((int)$pageinfo['curpage']);

        $breadcrumbs = $this->getBreadcrumbGenerator()->buildSearch($q);

        return $this->renderThemeView(
            'Theme:Search:search_results.html.twig',
            array(
                'is_search' => $is_search,
                'results' => $results,
                'sticky_results' => $sticky_results,
                'query' => $q,
                'pageinfo' => $pageinfo,
                'num_results' => $total,
                'pager' => $pagination,
                'breadcrumbs' => $breadcrumbs,
                'page_title' => $this->createPageTitle()->search(),
                'limit_types' => $limit_types_array
            )
        );
    }

    /**
     * @Route("/search/similar/{content_type}", name="portal_search_similar", defaults={"content_type":null})
     * @Route("/search/similar/{content_type}", name="user_search_similarto")
     */
    public function similarToAction(Request $request, $content_type = null)
    {
        $content = $request->get('content', '');

        if (!$content) {
            return $this->makeJsonResponse(
                array(
                    'results' => array(),
                    'words' => array()
                )
            );
        }

        $allowed_types = array('article', 'news', 'download', 'feedback');

        if (null === $content_type) {
            $content_type = $allowed_types;
        } else {
            $content_type = array($content_type);
        }

        $person = $this->getUser() ?: new PersonGuest();
        $se = $this->get('search_engine');
        $contextFactory = new SearchContextFactory($this->getContainer());
        $context = $contextFactory->createUserSearchContext($person);
        $sticky_search = new StickyWordSearch($this->getEm());
        /** @var ResultSet $results */
        $results = $se->getUserSearch()->similarTo(
            $context,
            $content,
            array('limit_types' => $content_type)
        );


        $search_results = $results->getTypedResults();

        // filter out the unwanted types from response and get the "words" for allowed objects
        $property_accessor = PropertyAccess::createPropertyAccessor();
        $typed_results = array();
        $words = array();
        foreach ($search_results as $result) {
            if (isset($result['type']) && in_array($result['type'], $allowed_types)) {
                $typed_results[] = $result;

                $object = $result['object'];
                if (is_object($object)) {
                    $class = get_class($object);
                    $type = 'DeskPRO:' . substr($class, strrpos($class, '\\') + 1);
                    $id = $property_accessor->getValue($object, 'id');
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
                'words' => $words,
            )
        );
    }
}
