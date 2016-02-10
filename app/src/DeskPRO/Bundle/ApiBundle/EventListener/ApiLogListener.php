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

namespace DeskPRO\Bundle\ApiBundle\EventListener;

use DeskPRO\Bundle\ApiBundle\Log\LogHelper;
use DeskPRO\Bundle\ApiBundle\Log\Writer\WriterInterface;
use DeskPRO\Bundle\AppBundle\Entity\ApiLog;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class ApiLogListener.
 */
class ApiLogListener implements EventSubscriberInterface
{
    /**
     * @var WriterInterface
     */
    protected $writer;

    /**
     * @var TokenStorageInterface
     */
    protected $token_storage;

    /**
     * @var bool
     */
    protected $enabled;

    /**
     * @var ApiLog
     */
    protected $log;

    protected $persisted;

    /**
     * @param WriterInterface       $writer
     * @param TokenStorageInterface $token_storage
     * @param EntityManager         $em
     * @param LogHelper             $helper
     */
    public function __construct(
        WriterInterface $writer,
        TokenStorageInterface $token_storage,
        EntityManager $em,
        LogHelper $helper
    ) {
        $this->writer        = $writer;
        $this->token_storage = $token_storage;
        $this->em            = $em;
        $this->helper        = $helper;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::RESPONSE => array('onResponse', 32),
            // the priority doesn't make sense because we are using DP_START_TIME, that defined
            // at the very beginning of request handling
            // so you have to be sure, that it will run AFTER Auth
            KernelEvents::REQUEST => array('onRequest', -32),
        );
    }

    public function onRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $options = $this->helper->getRequestOptions($request->headers);
        $this->helper->getRequestId($request->headers);

        if ($this->helper->shouldLog() && $event->isMasterRequest()) {
            $this->log = $this->createApiLog($request);
            if ($this->helper->isClientRequestdLog()) {
                $this->processRequestDupe($event, $options);
                if ($event->hasResponse()) {
                    $this->helper->setRequestIsProcessed(true);

                    return $event->getResponse();
                }
                $this->processEager($options);
            }
        }

        return;
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onResponse(FilterResponseEvent $event)
    {
        if ($this->helper->shouldLog() && $event->isMasterRequest()) {
            $response = $event->getResponse();

            $this->setApiLogAuthData($this->log);

            $response_data = [
                'headers' => $response->headers->all(),
                'body'    => $response->getContent(),
            ];
            $this->log
                ->setEndTime(time())
                ->setResponseData($response_data)
                ->setStatus($response->getStatusCode());
            $this->writer->write($this->log);
        }
    }

    protected function createApiLog(Request $request)
    {
        $log = new ApiLog();

        $request_data = [
            'headers' => $request->headers->all(),
            'body'    => $request->getContent(),
            'query'   => $request->query->all(),
            'post'    => $request->request->all(),
            'files'   => $request->files->all(),
            'server'  => $request->server->all(),
        ];

        $log
            ->setStartTime(defined('DP_START_TIME') ? DP_START_TIME : time())
            ->setRequestedUri($request->getUri())
            ->setRequestData($request_data)
            ->setRequestId($this->getRequestId($request));
        $this->setApiLogAuthData($log);

        return $log;
    }

    protected function getRequestId(Request $request)
    {
        return $this->helper->getRequestId($request->headers);
    }

    protected function setApiLogAuthData(ApiLog $log)
    {
        /** @var \Application\DeskPRO\EntityRepository\ApiKey $key_repo */
        $key_repo = $this->em->getRepository('DeskPRO:ApiKey');
        if (
            $this->token_storage->getToken()
            && $this->token_storage->getToken()->getName() === 'api_key'
            && $key = $key_repo->findByKeyString($this->token_storage->getToken()->getCredentials())
        ) {
            /* @var \Application\DeskPRO\Entity\ApiKey $key */
            $log->setKey($key);
        }
    }

    protected function processRequestDupe(GetResponseEvent $event, $options)
    {
        $response = new Response();
        $request  = $event->getRequest();

        if ($this->helper->isClientRequestdLog() && $api_log = $this->helper->findRequest($this->helper->getRequestId($request->headers))) {

            /** @var ApiLog $api_log */
            if (!$this->persisted && !$api_log->getStatus()) {
                throw new HttpException(Response::HTTP_LOCKED, 'Request is in process');
            }

            switch ($options['duplicate_mode']) {
                case LogHelper::DUPLICATE_MODE_FAIL:
                    throw new ConflictHttpException('This is duplicate request');
                    break;
                case LogHelper::DUPLICATE_MODE_RESEND:
                    $response->setStatusCode(Response::HTTP_OK);
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
            $this->persisted = true;
            $this->writer->write($this->log);
        }
    }
}
