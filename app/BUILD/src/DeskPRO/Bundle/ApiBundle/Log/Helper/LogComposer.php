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

namespace DeskPRO\Bundle\ApiBundle\Log\Helper;

use DeskPRO\Bundle\ApiBundle\Log\LogSaveException;
use DeskPRO\Bundle\ApiBundle\Log\Writer\WriterInterface;
use DeskPRO\Bundle\ApiBundle\Security\Token\AbstractApiSecurityToken;
use DeskPRO\Bundle\ApiBundle\Util\ApiUtil;
use DeskPRO\Bundle\AppBundle\Entity\ApiLog;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class LogComposer.
 */
class LogComposer
{
    /**
     * @var WriterInterface
     */
    protected $writer;

    /**
     * @var ApiLog
     */
    protected $log;

    /**
     * @var LogHelper
     */
    protected $log_helper;

    /**
     * @var DupeHelper
     */
    protected $dupe_helper;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var bool
     */
    protected $request_processed;

    /**
     * @param WriterInterface       $writer
     * @param LogHelper             $log_helper
     * @param DupeHelper            $dupe_helper
     * @param TokenStorageInterface $token_storage
     * @param EntityManager         $em
     */
    public function __construct(
        WriterInterface $writer,
        LogHelper $log_helper,
        DupeHelper $dupe_helper,
        TokenStorageInterface $token_storage,
        EntityManager $em
    ) {
        $this->writer        = $writer;
        $this->log_helper    = $log_helper;
        $this->dupe_helper   = $dupe_helper;
        $this->token_storage = $token_storage;
        $this->em            = $em;
    }

    /**
     * @return ApiLog
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * @param bool $request_processed
     *
     * @return $this
     */
    public function setRequestProcessed($request_processed)
    {
        $this->request_processed = (bool) $request_processed;

        return $this;
    }

    /**
     * @return LogHelper
     */
    public function getLogHelper()
    {
        return $this->log_helper;
    }

    /**
     * @return DupeHelper
     */
    public function getDupeHelper()
    {
        return $this->dupe_helper;
    }

    /**
     * @param Request $request
     *
     * @return mixed
     */
    public function getRequestId(Request $request)
    {
        return $this->log_helper->getRequestId($request->headers);
    }

    /**
     * @param Request $request
     *
     * @return ApiLog
     */
    public function createApiLog(Request $request)
    {
        if (!$this->log) {
            $this->log = $this->internalCreate($request);
        }

        return $this->log;
    }

    /**
     * @param Request $request
     *
     * @return ApiLog
     */
    public function internalCreate(Request $request)
    {
        $log          = new ApiLog();
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
            ->setRequestedUri($request->getPathInfo())
            ->setMethod($request->getMethod())
            ->setRequestData($request_data)
            ->setRequestId($this->getRequestId($request));
        $this->setApiLogAuthData($log);

        return $log;
    }

    /**
     * @param Response $response
     */
    public function finishApiLog(Response $response)
    {
        $this->internalFinish($response, $this->log);
    }

    /**
     * @param Response $response
     * @param ApiLog   $log
     */
    public function internalFinish(Response $response, ApiLog $log)
    {
        $response_data = [
            'headers' => $response->headers->all(),
            'body'    => $response->getContent(),
        ];
        $log->setEndTime(time())
            ->setResponseData($response_data)
            ->setStatus($response->getStatusCode());
    }

    /**
     * @param Request  $request
     * @param Response $response
     *
     * @return bool
     */
    public function write(Request $request, Response $response)
    {
        $options = $this->dupe_helper->getRequestOptions($request->headers);

        $skip_failed_client_request = $this->getLogHelper()->isClientRequestedLog()
            && $options['failure_mode'] === LogHelper::FAILURE_MODE_SKIP && $options['eager'] !== LogHelper::EAGER_ON
            && !($response->isSuccessful() || $response->isRedirection());

        $should_save =
            (
                $this->log_helper->isLoggingEnabled() ||
                (
                    $this->getLogHelper()->isClientRequestedLog()
                    && $this->getDupeHelper()->suitableMode($this->getLogHelper()->getMode())
                )
            )
            && !$this->request_processed;

        if ($skip_failed_client_request) {
            return false;
        } elseif ($should_save) {
            $this->saveLog();
        }

        return true;
    }

    public function saveLog()
    {
        try {
            $this->writer->write($this->log);
        } catch (\Exception $e) {
            throw new LogSaveException();
        }
    }

    /**
     * @param ApiLog $log
     */
    protected function setApiLogAuthData(ApiLog $log)
    {
        if ($this->getToken() && $this->getToken() instanceof AbstractApiSecurityToken) {
            $this->addKey($log);
            $log->setCredentials($this->getToken()->getCredentials());
            $log->setMode(ApiUtil::getMode($this->getToken()->getName()));
        }
    }

    /**
     * @param ApiLog $log
     */
    protected function addKey(ApiLog $log)
    {
        /** @var \Application\DeskPRO\EntityRepository\ApiKey $key_repo */
        $key_repo = $this->em->getRepository('DeskPRO:ApiKey');
        if ($this->getToken()->getName() === 'api_key'
            && $key = $key_repo->findByKeyString($this->getToken()->getCredentials())
        ) {
            /* @var \Application\DeskPRO\Entity\ApiKey $key */
            $log->setKey($key);
        }
    }

    /**
     * @return null|\Symfony\Component\Security\Core\Authentication\Token\TokenInterface
     */
    protected function getToken()
    {
        return $this->token_storage->getToken();
    }
}
