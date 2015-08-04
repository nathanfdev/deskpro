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
use Doctrine\ORM\EntityManager;
use Orb\Util\Numbers;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;

class SearchController extends AbstractController
{
    /**
     * @Route("/search", name="portal_search")
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
        $per_page = 5;

        if ($q) {
            $is_search = true;

            $se = $this->get('search_engine');
            $contextFactory = new SearchContextFactory($this->getContainer());
            $context = $contextFactory->createUserSearchContext($person);

            /** @var \Application\DeskPRO\NewSearch\SearchEngine\Result\ResultSet $result_set */
            $result_set = $se->getUserSearch()->search($context, $q, array('page' => $cur_page, 'per_page' => $per_page));

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

        return $this->renderThemeView(
            'Theme:Search:search_results.html.twig',
            array(
                'is_search' => $is_search,
                'results' => $results,
                'sticky_results' => $sticky_results,
                'query' => $q,
                'pageinfo' => $pageinfo,
                'num_results' => $total,
            )
        );
    }

    /**
     * @Route("/search/similar/{content_type}", name="portal_search_similar")
     */
    public function similarToAction(Request $request, $content_type)
    {
        $content = $request->get('content', '');

        if (!$content) {
            return $this->simpleJson(
                array(
                    'results' => array(),
                    'words' => array()
                )
            );
        }

        $person = $this->getUser() ?: new PersonGuest();
        $se = $this->get('search_engine');
        $contextFactory = new SearchContextFactory($this->getContainer());
        $context = $contextFactory->createUserSearchContext($person);
        $sticky_search = new StickyWordSearch($this->getEm());
        /** @var ResultSet $results */
        $results = $se->getUserSearch()->similarTo($context, $content, array('limit_types' => array($content_type)));
        $words = array();

        $property_accessor = PropertyAccess::createPropertyAccessor();
        foreach ($results->getResults() as $result) {
            if (is_object($result)) {
                $class = get_class($result);
                $type = 'DeskPRO:' . substr($class, strrpos($class, '\\') + 1);
                foreach ($sticky_search->getStickyWords($type, $property_accessor->getValue($result, 'id')) as $word) {
                    if (count($words) < 100) {
                        $words[] = $word;
                    }
                }
            }
        }


        return $this->simpleJson(
            array(
                'results' => $results->getTypedResults(),
                'words' => $words,
            )
        );
    }

    private function simpleJson(array $array)
    {
        return new JsonResponse(array('data' => $array));
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface|DeskproContainer
     */
    private function getContainer()
    {
        return $this->container;
    }
}
