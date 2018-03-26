<?php

namespace DeskPRO\Bundle\ApiBundle\EventListener\Log;

use DeskPRO\Bundle\ApiBundle\Log\Helper\AbstractLogHelper as LogHelper;
use DeskPRO\Bundle\ApiBundle\Log\LogSaveException;
use DeskPRO\Bundle\AppBundle\Entity\ApiLog;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class ApiDupeListener.
 */
class ApiDupeListener extends AbstractLogListener
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            // the priority doesn't make sense because we are using DP_START_TIME, that defined
            // at the very beginning of request handling
            // so you have to be sure, that it will run AFTER Auth
            KernelEvents::REQUEST => ['onRequest', 4],
        ];
    }

    /**
     * @param GetResponseEvent $event
     *
     * @return Response|void
     */
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
            try {
                $this->processRequestDupe($event, $options);
                $this->processEager($options);
            } catch (LogSaveException $logException) {
                // we can't log such error in db cause EntityManager is closed and no need to trick
                throw new ConflictHttpException('Request with same ID already processed');
                //just need this to determine for finally if HttpException was thrown
            } catch (HttpException $e) {
                $this->saveDupe($options);
                throw $e;
            }

            if ($event->hasResponse()) {
                $this->saveDupe($options);

                return $event->getResponse();
            }
        }

        return;
    }

    /**
     * @param array $options
     */
    private function saveDupe(array $options)
    {
        if ($options['log_dupe'] === LogHelper::LOG_DUPE_SAVE) {
            $log = $this->composer->getLog();
            $log
                ->setRequestId($log->getRequestId().uniqid('-dupe-', true))
                ->setEndTime(time())
                ->setIsDupe(true)
            ;
            $this->composer->saveLog();
        }
    }

    /**
     * @param GetResponseEvent $event
     * @param                  $options
     */
    protected function processRequestDupe(GetResponseEvent $event, $options)
    {
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
                    $response = new Response();
                    $response->setStatusCode($api_log->getStatus());
                    $response->setContent($api_log->getResponseData()['body']);
                    $headers           = new ResponseHeaderBag($api_log->getResponseData()['headers']);
                    $response->headers = $headers;
                    $event->setResponse($response);
            }
        }
    }

    /**
     * @param $options
     */
    protected function processEager($options)
    {
        if ($options['eager']) {
            $this->composer->saveLog();
        }
    }
}
