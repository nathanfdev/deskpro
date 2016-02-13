<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

use DeskPRO\Bundle\ApiBundle\EventListener\JsonHeadersResponseListener;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class BatchController.
 */
class BatchController extends BaseController
{
    /**
     * @Post("/batch", name="api_batch_post")
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
        foreach ($requests as $identifier => $sub_request_info) {
            $responses[$identifier] = $this->performSubRequest($sub_request_info);
        }

        return View::create(
            $this->get('api_view_representation_factory')->createBatchRepresentation($responses),
            Response::HTTP_OK
        );
    }

    /**
     * @Get("/batch", name="api_batch_get")
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
        foreach ($requests as $identifier => $sub_request_info) {
            $responses[$identifier] = $this->performSubRequest($sub_request_info);
        }

        return View::create(
            $this->get('api_view_representation_factory')->createBatchRepresentation($responses),
            Response::HTTP_OK
        );
    }

    /**
     * @param array $sub_request_info
     *
     * @return string
     */
    protected function performSubRequest($sub_request_info)
    {
        $info = [
            'method'  => 'GET',
            'url'     => null,
            'data'    => null,
            'headers' => null,
            'params'  => [],
        ];

        if (is_array($sub_request_info)) {
            $info = array_merge($info, $sub_request_info);
        } else {
            $info = array_merge($info, ['url' => $sub_request_info]);
        }

        $json_serialized = null;
        if ($info['data']) {
            $json            = $info['data'];
            $json_serialized = $this->get('serializer')->serialize($json, 'json');
        }

        $sub_request = Request::create(
            $info['url'],
            $info['method'],
            $info['params'],
            [],
            [],
            [],
            $json_serialized
        );

        $sub_request->headers->set('Content-Type', 'json');
        $sub_request->query->set(JsonHeadersResponseListener::INCLUDE_HEADERS_PARAM, 1);

        if ($info['headers']) {
            foreach ($info['headers'] as $k => $v) {
                $sub_request->headers->set($k, $v);
            }
        }

        $response = $this->get('http_kernel')->handle($sub_request, HttpKernelInterface::SUB_REQUEST);

        return $this->get('serializer')->deserialize($response->getContent(), 'array', 'json');
    }
}
