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

namespace DeskPRO\Bundle\ApiBundle\Log;

use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\ApiBundle\Log\Helper\LogComposer;
use DeskPRO\Bundle\AppBundle\Entity\ApiLog;
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
     * @param Kernel            $kernel
     * @param LogComposer       $composer
     * @param LogServiceFactory $factory
     * @param SettingsResolver  $resolver
     */
    public function __construct(Kernel $kernel, LogComposer $composer, LogServiceFactory $factory, SettingsResolver $resolver)
    {
        $this->kernel     = $kernel;
        $this->composer   = $composer;
        $this->serializer = $factory->createSerializer('human_readable');
        $this->resolver   = $resolver;
    }

    /**
     * @param $request_id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function replay($request_id)
    {
        $log = $this->composer->getLogHelper()->findRequest($request_id);
        if (!$log) {
            throw new \InvalidArgumentException(sprintf('Log with id %s not found'), $request_id);
        }

        $request  = $this->createSymfonyRequest($log);
        $response = $this->kernel->handle($request, HttpKernelInterface::SUB_REQUEST, true);

        return $this->compose($request, $response);
    }

    /**
     * @param $request_id
     *
     * @return string
     */
    public function replayWithCrawler($request_id)
    {
        $log = $this->composer->getLogHelper()->findRequest($request_id);
        if (!$log) {
            throw new \InvalidArgumentException(sprintf('Log with id %s not found'), $request_id);
        }
        $request  = $this->createSymfonyRequest($log);
        $response = $this->sendRequest($log);

        return $this->compose($request, $response);
    }

    /**
     * @param Request  $request
     * @param Response $response
     *
     * @return string
     */
    protected function compose(Request $request, Response $response)
    {
        $new_log = $this->composer->internalCreate($request);
        $this->composer->internalFinish($response, $new_log);

        return $this->serializer->serialize($new_log);
    }

    /**
     * @param ApiLog $log
     *
     * @return Response
     */
    protected function sendRequest(ApiLog $log)
    {
        // wont work with :8080 ie
        $client = new \GuzzleHttp\Client([
            'base_uri'        => 'http://localhost/', //$this->resolver->getGlobalSettings()->get('core.deskpro_url'),
            'allow_redirects' => true,
        ]);

        $server = $log->getRequestData('server');

        $request = new \GuzzleHttp\Psr7\Request(
            $server['REQUEST_METHOD'],
            $log->getRequestedUri().'?'.http_build_query($log->getRequestData('query')),
            $log->getRequestData('headers'),
            $log->getRequestData('body')
        );

        $response         = $client->send($request);
        $response_content = $response->getBody()->read($response->getBody()->getSize());

        return new Response($response_content, $response->getStatusCode(), $response->getHeaders());
    }

    /**
     * @param ApiLog $log
     *
     * @return Request
     */
    protected function createSymfonyRequest(ApiLog $log)
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
