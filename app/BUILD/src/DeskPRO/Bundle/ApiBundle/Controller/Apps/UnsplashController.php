<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Apps;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations as Rest;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class UnsplashController.
 *
 * @ApiModes("all")
 * @Rest\Route("/apps/unsplash")
 */
class UnsplashController
{
    // TODO Move id to settings
    public static $unsplashAccessKey = '3d9c27e1cb7a6e77f038d7c8beb0759d37f7a825be8a0c79d03f0f1554407bc1';

    /**
     * @ApiDoc(
     *     section="Apps",
     *     description="Get random images from unsplash",
     *     filters={
     *          {"name"="count", "pattern"="\d", "description"="Resource per page count", "dataType"="integer"},
     *     },
     *      statusCodes={
     *          200="Returned if your request was successful",
     *          400="An error will occur if you provide wrong filters set",
     *      }
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
     * @ApiDoc(
     *     section="Apps",
     *     description="Get random images from unsplash",
     *     filters={
     *          {"name"="query", "pattern"="\s", "description"="Search query", "dataType"="string"},
     *          {"name"="page", "pattern"="\d", "description"="Page number", "dataType"="integer"},
     *     },
     *      statusCodes={
     *          200="Returned if your request was successful",
     *          400="An error will occur if you provide wrong filters set",
     *      }
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
        $client = new Client();
        $res    = $client->request('GET', 'https://api.unsplash.com/'.$path, [
            'headers' => [
                'Authorization' => 'Client-ID '.self::$unsplashAccessKey,
            ],
            'query' => $params,
        ]);
        $body = $res->getBody();

        return new Response((string) $body);
    }
}
