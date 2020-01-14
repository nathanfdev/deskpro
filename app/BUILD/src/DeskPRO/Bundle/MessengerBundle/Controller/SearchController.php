<?php

namespace DeskPRO\Bundle\MessengerBundle\Controller;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\NewSearch\SearchEngine\SearchContextFactory;
use Application\DeskPRO\NewSearch\SearchEngine\SearchContextInterface;
use Application\DeskPRO\NewSearch\SearchEngine\SearchEngine;
use Application\DeskPRO\NewSearch\SearchEngine\UserSearchInterface;
use Application\DeskPRO\People\PersonGuest;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\MessengerBundle\Serializer\Model\SearchModel;
use DeskPRO\Bundle\MessengerBundle\Serializer\Model\SearchResults;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ServiceController.
 *
 * @ApiModes("all")
 * @ApiUserContext("open")
 *
 * @Rest\Route("/search")
 * @Feature("messenger")
 */
class SearchController extends AbstractMessengerController
{
    /**
     * @Rest\Get("/quick", name="messenger_quick_search")
     *
     * @param Request $request
     *
     * @return View
     */
    public function quickSearchAction(Request $request)
    {
        $q = $request->get('q');

        $person  = $this->getUser() ?: new PersonGuest();
        $curPage = $request->get('page', 1);
        $perPage = 10;

        $searchResults = new SearchResults($this->fetchSearchResults($person, $q, $curPage, $perPage));

        return View::create($this->wrap($searchResults), Response::HTTP_OK);
    }

    private function fetchSearchResults($person, $q, $curPage, $perPage, $detailledType = false)
    {
        $limitTypesArray = ['article'];
        $contextFactory  = new SearchContextFactory($this->getContainer());
        $context         = $contextFactory->createUserSearchContext($person);

        $results = [];
        foreach ($limitTypesArray as $type) {
            $searchResults = $this->doSearch(
                $type,
                $q,
                !$detailledType || $detailledType === $type ? $curPage : 1,
                $perPage,
                $context
            );
            $results = array_merge($results, $searchResults);
        }

        return $results;
    }
    /**
     * @param string        $type
     * @param mixed         $q
     * @param Person        $person
     * @param int           $curPage
     * @param int           $perPage
     * @param               $context
     *
     * @throws \Exception
     *
     * @return array
     */
    protected function doSearch(
        $type,
        $q,
        $curPage,
        $perPage,
        SearchContextInterface $context
    ) {
        if ($q && is_string($q)) {
            /** @var SearchEngine $se */
            $se = $this->get('search_engine');

            /** @var UserSearchInterface $userSearch */
            $userSearch = $se->getUserSearch();

            if (is_numeric($q)) {
                $context->setOption(SearchContextInterface::SEARCH_BY_ID, true);
            }

            /** @var \Application\DeskPRO\NewSearch\SearchEngine\Result\ResultSet $resultSet */
            $resultSet = $userSearch->search(
                $context,
                $q,
                ['page' => $curPage, 'per_page' => $perPage, 'limit_types' => [$type]]
            );

            return array_map(
                function ($r) {
                    return new SearchModel($r['object'], $this->get('object_router'));
                },
                $resultSet->getTypedResults()
            );
        }

        return [];
    }
}
