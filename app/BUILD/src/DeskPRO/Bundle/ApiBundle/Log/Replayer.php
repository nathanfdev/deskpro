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

namespace DeskPRO\Bundle\ApiBundle\Log;

use DeskPRO\Bundle\ApiBundle\Log\Helper\LogComposer;
use DeskPRO\Bundle\AppBundle\Entity\ApiLog;
use DeskPRO\Bundle\AppBundle\Util\HttpClient;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use GuzzleHttp\RequestOptions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Class Replayer.
 */
class Replayer
{
    /**
     * @var Kernel
     */
    private $kernel;

    /**
     * @var LogComposer
     */
    private $composer;

    /**
     * @var LogServiceFactory
     */
    private $factory;

    /**
     * @var BrandStack
     */
    private $brandStack;

    /**
     * @param Kernel            $kernel
     * @param LogComposer       $composer
     * @param LogServiceFactory $factory
     * @param BrandStack        $brandStack
     */
    public function __construct(
        Kernel $kernel,
        LogComposer $composer,
        LogServiceFactory $factory,
        BrandStack $brandStack)
    {
        $this->kernel     = $kernel;
        $this->composer   = $composer;
        $this->factory    = $factory;
        $this->brandStack = $brandStack;
    }

    /**
     * @param string $request_id
     * @param string $serializer
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function replay($request_id, $serializer = 'human_readable')
    {
        $log = $this->composer->getLogHelper()->findRequest($request_id);
        if (!$log) {
            throw new \InvalidArgumentException(sprintf('Log with id %s not found'), $request_id);
        }

        $request  = $this->createSymfonyRequest($log);
        $response = $this->kernel->handle($request, HttpKernelInterface::SUB_REQUEST, true);

        return $this->compose($request, $response, $serializer);
    }

    /**
     * @param string $request_id
     * @param string $serializer
     *
     * @return string
     */
    public function replayWithCrawler($request_id, $serializer = 'human_readable')
    {
        $log = $this->composer->getLogHelper()->findRequest($request_id);
        if (!$log) {
            throw new \InvalidArgumentException(sprintf('Log with id %s not found'), $request_id);
        }
        $request  = $this->createSymfonyRequest($log);
        $response = $this->sendRequest($log);

        return $this->compose($request, $response, $serializer);
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @param string   $serializer
     *
     * @return string
     */
    private function compose(Request $request, Response $response, $serializer)
    {
        $new_log = $this->composer->internalCreate($request);
        $this->composer->internalFinish($response, $new_log);

        $serializer = $this->factory->createSerializer($serializer);

        return $serializer->serialize($new_log);
    }

    /**
     * @param ApiLog $log
     *
     * @return Response
     */
    private function sendRequest(ApiLog $log)
    {
        // wont work with :8080 ie
        $client = new HttpClient([
            'base_uri'        => $this->brandStack->getActive()->getSetting('core.deskpro_url'),
            'allow_redirects' => true,
        ]);

        $response = $client->request(
            $log->getMethod(),
            $log->getRequestedUri().'?'.http_build_query($log->getRequestData('query')),
            [
                RequestOptions::HEADERS => $log->getRequestData('headers'),
                RequestOptions::BODY    => $log->getRequestData('body'),
            ]
        );
        $response_content = $response->getBody()->read($response->getBody()->getSize());

        return new Response($response_content, $response->getStatusCode(), $response->getHeaders());
    }

    /**
     * @param ApiLog $log
     *
     * @return Request
     */
    private function createSymfonyRequest(ApiLog $log)
    {
        $request = new Request(
            $log->getRequestData('query'),
            $log->getRequestData('post'),
            [],
            [],
            $log->getRequestData('files'),
            $log->getRequestData('server'),
            $log->getRequestData('body')
        );
        $request->headers->add($log->getRequestData('headers'));

        return $request;
    }
}
