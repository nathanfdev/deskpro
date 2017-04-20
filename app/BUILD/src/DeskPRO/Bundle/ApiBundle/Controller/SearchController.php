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

namespace DeskPRO\Bundle\ApiBundle\Controller;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\QuickSearch\QuickSearchContext;
use DeskPRO\Bundle\AppBundle\QuickSearch\QuickSearchRequest;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SearchController.
 *
 * @ApiModes("all")
 * @Rest\Route("/search")
 */
class SearchController extends BaseController
{
    /**
     * Search through articles, downloads, feedback, news, tickets, chat_conversations, people and organizations.
     *
     * @ApiDoc(
     *     section="Search",
     *     resourceDescription="Operations about search",
     *     filters={
     *          {
     *              "name"="q",
     *              "requirement"=".*",
     *              "description"="search term",
     *              "dataType"="string"
     *          },
     *          {
     *              "name"="sort",
     *              "requirement"=".*",
     *              "description"="how to sort",
     *              "dataType"="string"
     *          },
     *          {
     *              "name"="types",
     *              "requirement"="(article|download|feedback|news|ticket|person|agent|organization|chat_conversation)+",
     *              "description"="comma separated list of types",
     *              "dataType"="string"
     *          }
     *      },
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     output="DeskPRO\Bundle\AppBundle\QuickSearch\QuickSearchResponse"
     * )
     *
     * @Rest\Get("")
     *
     * @param Request $request
     *
     * @return View
     */
    public function quickSearchAction(Request $request)
    {
        $searchRequest = $this->getSearchRequest($request);

        if ($request->query->get('types', '')) {
            $types        = explode(',', $request->query->get('types', ''));
            $unknownTypes = array_diff($types, array_keys(QuickSearchContext::getDoctrineMapping()));
            if ($unknownTypes) {
                throw $this->createBadRequestException('Unknown types: '.implode(',', $unknownTypes));
            }
        } else {
            $types = [];
        }

        $searchRequest->setTypes($types);

        return $this->getSearchResults($searchRequest);
    }

    /**
     * Search only organizations and people.
     *
     * @ApiDoc(
     *     section="Search",
     *     resourceDescription="Operations about search",
     *     filters={
     *          {
     *              "name"="q",
     *              "requirement"=".*",
     *              "description"="search term",
     *              "dataType"="string"
     *          },
     *          {
     *              "name"="sort",
     *              "requirement"=".*",
     *              "description"="how to sort",
     *              "dataType"="string"
     *          }
     *      },
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     output="DeskPRO\Bundle\AppBundle\QuickSearch\QuickSearchResponse"
     * )
     *
     * @Rest\Get("/people_and_orgs")
     *
     * @param Request $request
     *
     * @return View
     */
    public function quickSearchPeopleOrganizationsAction(Request $request)
    {
        $searchRequest = $this->getSearchRequest($request);
        $searchRequest->setTypes([QuickSearchContext::TYPE_PERSON, QuickSearchContext::TYPE_ORGANIZATION]);
        $searchRequest->disableSideloads();

        return $this->getSearchResults($searchRequest);
    }

    /**
     * Search by entity type.
     *
     * @ApiDoc(
     *     section="Search",
     *     resourceDescription="Operations about search",
     *     filters={
     *          {
     *              "name"="q",
     *              "requirement"=".*",
     *              "description"="search term",
     *              "dataType"="string"
     *          },
     *          {
     *              "name"="sort",
     *              "requirement"=".*",
     *              "description"="how to sort",
     *              "dataType"="string"
     *          }
     *      },
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     output="array"
     * )
     * @Rest\Get("/{type}", requirements={
     *     "type"="(article|download|feedback|news|ticket|person|agent|organization|chat_conversation|topic)"
     * })
     *
     * @param string  $type
     * @param Request $request
     *
     * @return View
     */
    public function quickSearchByTypeAction($type, Request $request)
    {
        $searchRequest = $this->getSearchRequest($request);
        $searchRequest->setTypes([$type]);
        $searchRequest->disableSideloads();

        $results = $this->get('quick_search')->search($searchRequest);

        return new View($this->wrap($results->getContext($type)->getEntities()));
    }

    /**
     * @param Request $request
     *
     * @return QuickSearchRequest
     */
    protected function getSearchRequest(Request $request)
    {
        $searchRequest = new QuickSearchRequest(
            $this->getUser(),
            (string) $request->query->get('q'),
            $request->query->get('params'),
            (string) $request->query->get('sort')
        );

        $limit = (int) $request->query->getInt('limit');
        if ($limit) {
            $searchRequest->setLimit(min($searchRequest->getLimit(), $limit));
        }

        return $searchRequest;
    }

    /**
     * @param QuickSearchRequest $searchRequest
     *
     * @return View
     */
    protected function getSearchResults(QuickSearchRequest $searchRequest)
    {
        return new View($this->wrap($this->get('quick_search')->search($searchRequest)));
    }
}
