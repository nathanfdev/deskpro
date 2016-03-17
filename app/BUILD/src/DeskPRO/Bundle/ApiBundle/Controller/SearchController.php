<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
namespace DeskPRO\Bundle\ApiBundle\Controller;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\QuickSearch\QuickSearchContext;
use DeskPRO\Bundle\AppBundle\QuickSearch\QuickSearchRequest;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SearchController.
 *
 * @ApiModes("all")
 */
class SearchController extends BaseController
{
    /**
     * Search through articles, donwloads, feedback, news, tickets, chat_conversations, people and organizations.
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
     *     }
     * )
     *
     * @Annotations\Get("/search", name="api_quick_search")
     *
     * @param Request $request
     *
     * @return View
     */
    public function quickSearchAction(Request $request)
    {
        $search_request = $this->getSearchRequest($request);

        $types = explode(',', $request->query->get('types', ''));
        $search_request->setTypes($types);

        return $this->getSearchResults($search_request);
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
     *              "description"="how to sord",
     *              "dataType"="string"
     *          }
     *      },
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     }
     * )
     *
     * @Annotations\Get("/search/people_and_orgs", name="api_quick_search_people_and_orgs")
     *
     * @param Request $request
     *
     * @return View
     */
    public function quickSearchPeopleOrganizationsAction(Request $request)
    {
        $search_request = $this->getSearchRequest($request);

        $search_request->setTypes([QuickSearchContext::TYPE_PERSON, QuickSearchContext::TYPE_ORGANIZATION]);
        $search_request->disableSideloads();

        return $this->getSearchResults($search_request);
    }

    /**
     * @param Request $request
     *
     * @return QuickSearchRequest
     */
    protected function getSearchRequest(Request $request)
    {
        $search_request = new QuickSearchRequest(
            $this->getUser(),
            (string) $request->query->get('q'),
            (string) $request->query->get('sort')
        );

        return $search_request;
    }

    /**
     * @param QuickSearchRequest $search_request
     *
     * @return View
     */
    protected function getSearchResults(QuickSearchRequest $search_request)
    {
        $results = $this->get('quick_search')->search($search_request);

        $response = ['grouped_results' => []];
        foreach ($results->getContexts() as $context) {
            $response['grouped_results'][] = [
                'type'    => $context->getType(),
                'results' => $this->dataSerialize($context->getEntities())['data'],
            ];
        }

        return new View($response);
    }
}
