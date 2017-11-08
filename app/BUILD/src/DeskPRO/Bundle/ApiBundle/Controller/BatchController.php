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

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\ApiBundle\EventListener\JsonHeadersResponseListener;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
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
        if (!is_array($requests = $request->request->get('requests'))) {
            throw new BadRequestHttpException('the "requests" field is required');
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
     * @Rest\Get("")
     *
     * @param Request $request
     *
     * @return View
     */
    public function executeBatchGetAction(Request $request)
    {
        if (!$requests = $request->get('get')) {
            throw new BadRequestHttpException('You must specify requests in the "get" parameter');
        }
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
