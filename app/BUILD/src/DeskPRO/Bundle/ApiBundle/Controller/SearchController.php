<?php

namespace DeskPRO\Bundle\ApiBundle\Controller;

use Application\LegacyApiBundle\HttpFoundation\JsonResponse;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
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

    /**
     * @ApiDoc(
     *     section="Search",
     *     resourceDescription="",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     output="array"
     * )
     *
     * @Rest\Post("/new_search")
     * @Feature("new_search")
     */
    public function newSearchAction()
    {
        $result = [
            'organizations' => [
                [
                    'id'      => 1,
                    'name'    => 'Deskpro',
                    'members' => 123,
                    'img'     => 'http://localhost/file.php/avatar/25/default.jpg?size-fit=1',
                ],
                [
                    'id'      => 2,
                    'name'    => 'Deskpro',
                    'members' => 123,
                    'img'     => 'http://localhost/file.php/avatar/25/default.jpg?size-fit=1',
                ],
            ],
            'people' => [
                [
                    'id'      => 1,
                    'name'    => 'Kenneth James',
                    'email'   => 'raterequests@alllogistics.com',
                    'tickets' => 132,
                    'img'     => 'http://localhost/file.php/avatar/25/default.jpg?size-fit=1',
                ],
                [
                    'id'      => 2,
                    'name'    => 'Richard Blaine',
                    'email'   => 'hello@email.co.uk',
                    'tickets' => 76,
                    'img'     => 'http://localhost/file.php/avatar/25/default.jpg?size-fit=1',
                ],
            ],
            'tickets' => [
                [
                    'id'           => 83995,
                    'subject'      => 'Pricing and Purchasing Questions',
                    'person_name'  => 'Kenneth James',
                    'person_email' => 'kenneth@windfarm.co.uk',
                    'agent'        => 1,
                    'urgency'      => 11,
                    'messages'     => [
                        [
                            'id'   => 123,
                            'text' => 'Many software vendors have government rate pricing that differs from the',
                        ],
                        [
                            'id'   => 124,
                            'text' => '..discount policy. The On-Premise pricing is very competitive so it\'s ...',
                        ],
                    ],
                ],
                [
                    'id'           => 74562,
                    'subject'      => 'Pricing and translations',
                    'person_name'  => 'Toby Falkirk',
                    'person_email' => 't.falkirk@rambling.com',
                    'agent'        => 2,
                    'urgency'      => 4,
                    'messages'     => [
                        [
                            'id'   => 123,
                            'text' => "...discount policy. The On-Premise pricing is very competitive so it's ...",
                        ],
                    ],
                ],
                [
                    'id'           => 63331,
                    'subject'      => 'ASP Licensing',
                    'person_name'  => 'Mary Jarvis',
                    'person_email' => 'mjarvis@econorob.nl',
                    'agent'        => 3,
                    'messages'     => [
                        [
                            'id'   => 123,
                            'text' => 'if I could make the pricing attractive enough. Several of them are small, and don\'t ha...',
                        ],
                    ],
                ],
            ],
            'feedback' => [
                [
                    'id'    => 83995,
                    'title' => 'Feedback result',
                ],
            ],
            'articles' => [
                [
                    'id'      => 83995,
                    'title'   => 'Article result',
                    'status'  => 'Published',
                    'content' => 'Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.',
                ],
                [
                    'id'      => 83996,
                    'title'   => 'Article result',
                    'status'  => 'Published',
                    'content' => 'Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.',
                ],
                [
                    'id'      => 83997,
                    'title'   => 'Article result',
                    'status'  => 'Published',
                    'content' => 'Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.',
                ],
            ],
            'news' => [
                [
                    'id'    => 83995,
                    'title' => 'News result',
                ],
                [
                    'id'    => 83996,
                    'title' => 'News result',
                ],
                [
                    'id'    => 83997,
                    'title' => 'News result',
                ],
            ],
            'downloads' => [
                [
                    'id'    => 83995,
                    'title' => 'Download result',
                ],
            ],
            'admin' => [
                ['title' => 'Admin result'],
                ['title' => 'Admin result'],
            ],
        ];

        return new JsonResponse($result);
    }
}
