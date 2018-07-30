<?php

namespace DeskPRO\Bundle\ApiBundle\Controller;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\ApiBundle\EventListener\JsonHeadersResponseListener;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class BatchController.
 *
 * @ApiModes("all")
 * @Rest\Route("/batch")
 */
class BatchController extends BaseController
{
    /**
     * @Rest\Post("")
     *
     * @param Request $request
     *
     * @return View
     */
    public function executeBatchPostAction(Request $request)
    {
        $requests  = $request->request->get('requests', []);
        $responses = [];
        foreach ($requests as $identifier => $subRequestInfo) {
            $responses[$identifier] = $this->performSubRequest($subRequestInfo, $request);
        }

        return View::create([
            'responses' => $responses,
        ]);
    }

    /**
     * @Rest\Get("")
     *
     * @param Request $request
     *
     * @return View
     */
    public function executeBatchGetAction(Request $request)
    {
        $requests = $request->get('get', []);
        if (!is_array($requests)) {
            $requests = explode(',', $requests);
        }

        $responses = [];
        foreach ($requests as $identifier => $subRequestInfo) {
            $responses[$identifier] = $this->performSubRequest($subRequestInfo, $request);
        }

        return View::create([
            'responses' => $responses,
        ]);
    }

    /**
     * @param array   $subRequestInfo
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return string
     */
    protected function performSubRequest($subRequestInfo, Request $request)
    {
        $info = [
            'method'  => 'GET',
            'url'     => null,
            'data'    => null,
            'headers' => null,
            'params'  => [],
        ];

        if (is_array($subRequestInfo)) {
            $info = array_merge($info, $subRequestInfo);
        } else {
            $info = array_merge($info, ['url' => $subRequestInfo]);
        }

        // verify sub request url
        if (strpos($info['url'], '/api/v2') !== 0) {
            $info['url'] = '/api/v2/'.ltrim($info['url'], '/');
        }

        $json_serialized = null;
        if ($info['data']) {
            $json            = $info['data'];
            $json_serialized = @json_encode($json);
        }

        $subRequest = Request::create(
            $request->getUriForPath($info['url']),
            $info['method'],
            $info['params'],
            [],
            [],
            $request->server->all(),
            $json_serialized
        );

        if (!$this->get('request_matcher')->requestRouteExist($subRequest)) {
            throw $this->createBadRequestException("Route path for '{$info['url']}' not found");
        }

        $subRequest->headers->set('Content-Type', 'json');
        $subRequest->query->set(JsonHeadersResponseListener::INCLUDE_HEADERS_PARAM, 1);

        if ($info['headers']) {
            foreach ($info['headers'] as $k => $v) {
                $subRequest->headers->set($k, $v);
            }
        }

        // reset entity manager
        $this->getDoctrine()->getManager()->clear();
        if ($this->getUser() instanceof Person) {
            $token = $this->container->get('security.token_storage')->getToken();
            $token->setUser($this->getRepository(Person::class)->find($this->getUser()->getId()));
        }

        $response = $this->getKernel()->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        return $this->get('serializer')->deserialize($response->getContent(), 'array', 'json');
    }
}
