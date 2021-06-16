<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Apps;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Util\HttpClient;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class UnsplashController.
 *
 * @ApiModes("all")
 * @Rest\Route("/apps/unsplash")
 */
class UnsplashController extends BaseController
{
    /**
     * Get a random unsplash image.
     *
     * @ApiDoc(
     *     section="Apps",
     *     description="Get random images from unsplash",
     *     filters={
     *          {"name"="count", "pattern"="\d", "description"="Resource per page count", "dataType"="integer"},
     *     },
     *      statusCodes={
     *          200="Returned if your request was successful",
     *          400="An error will occur if you provide wrong filters set",
     *      },
     *     output="string"
     * )
     *
     * @Rest\Get("/random")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function randomAction(Request $request)
    {
        $params['count']       = (int) $request->get('count');
        $params['orientation'] = 'landscape';

        return $this->requestApi('photos/random', $params);
    }

    /**
     * Search for specific unsplash image.
     *
     * @ApiDoc(
     *     section="Apps",
     *     description="Search for specific unsplash image",
     *     filters={
     *          {"name"="query", "pattern"="\s", "description"="Search query", "dataType"="string"},
     *          {"name"="page", "pattern"="\d", "description"="Page number", "dataType"="integer"},
     *     },
     *      statusCodes={
     *          200="Returned if your request was successful",
     *          400="An error will occur if you provide wrong filters set",
     *      },
     *     output="array"
     * )
     *
     * @Rest\Get("/search")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function searchAction(Request $request)
    {
        $params['query']       = $request->get('query');
        $params['page']        = (int) $request->get('page', 1);
        $params['per_page']    = 12;
        $params['orientation'] = 'landscape';
        $response              = $this->requestApi('search/photos', $params);
        $results               = \GuzzleHttp\json_decode($response->getContent());

        return new JsonResponse($results->results);
    }

    /**
     * @param $path
     * @param array $params
     *
     * @return Response
     */
    private function requestApi($path, $params = [])
    {
        $accessKey = $this->get('settings_resolver')->getGlobalSettings()->get('services.unsplash_access_key', null);
        $client    = new HttpClient();
        $res       = $client->request('GET', 'https://api.unsplash.com/'.$path, [
            'headers' => [
                'Authorization' => 'Client-ID '.$accessKey,
            ],
            'query' => $params,
        ]);
        $body = $res->getBody();

        return new Response((string) $body);
    }
}
