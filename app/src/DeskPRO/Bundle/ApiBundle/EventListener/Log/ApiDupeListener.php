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

namespace DeskPRO\Bundle\ApiBundle\EventListener\Log;

use DeskPRO\Bundle\ApiBundle\Log\Helper\AbstractLogHelper as LogHelper;
use DeskPRO\Bundle\AppBundle\Entity\ApiLog;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiDupeListener extends AbstractLogListener
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::RESPONSE => ['onResponse', 31],
            // the priority doesn't make sense because we are using DP_START_TIME, that defined
            // at the very beginning of request handling
            // so you have to be sure, that it will run AFTER Auth
            KernelEvents::REQUEST => ['onRequest', 4],
        );
    }

    public function onRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $this->composer->getRequestId($request);

        $should_process = $this->composer->getLogHelper()->isClientRequestedLog()
            && $event->isMasterRequest()
            && $this->composer->getDupeHelper()->suitableMode($this->composer->getLogHelper()->getMode());

        if ($should_process) {
            $options = $this->composer->getDupeHelper()->getRequestOptions($request->headers);
            $this->composer->createApiLog($request);
            $this->processRequestDupe($event, $options);
            if ($event->hasResponse()) {
                return $event->getResponse();
            }
            $this->processEager($options);
        }

        return;
    }

    protected function processRequestDupe(GetResponseEvent $event, $options)
    {
        $response = new Response();

        $log_helper = $this->composer->getLogHelper();

        if ($log_helper->isClientRequestedLog()
            && $api_log = $log_helper->findRequest($log_helper->getRequestId())) {
            $this->composer->setRequestProcessed(true);
            /** @var ApiLog $api_log */
            if ($api_log->getId() > 0 && !$api_log->getStatus()) {
                throw new HttpException(Response::HTTP_LOCKED, 'Request is in process');
            }

            switch ($options['duplicate_mode']) {
                case LogHelper::DUPLICATE_MODE_FAIL:
                    throw new ConflictHttpException('This is duplicate request');
                    break;
                case LogHelper::DUPLICATE_MODE_RESEND:
                    $response->setStatusCode($api_log->getStatus());
                    $response->setContent($api_log->getResponseData()['body']);
                    $headers           = new ResponseHeaderBag($api_log->getResponseData()['headers']);
                    $response->headers = $headers;
                    $event->setResponse($response);
            }
        }
    }

    protected function processEager($options)
    {
        if ($options['eager']) {
            $this->composer->saveLog();
        }
    }
}
