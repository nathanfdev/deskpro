<?php

namespace DeskPRO\Bundle\ApiBundle\Log\Helper;

use Application\DeskPRO\Entity\ApiKey;
use DeskPRO\Bundle\ApiBundle\Log\LogSaveException;
use DeskPRO\Bundle\ApiBundle\Log\Writer\WriterInterface;
use DeskPRO\Bundle\ApiBundle\Security\Token\AbstractApiSecurityToken;
use DeskPRO\Bundle\ApiBundle\Util\ApiUtil;
use DeskPRO\Bundle\AppBundle\Entity\ApiLog;
use Doctrine\ORM\EntityManager;
use DpSys\LowError\SystemErrorHandler;
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
    protected $logHelper;

    /**
     * @var DupeHelper
     */
    protected $dupeHelper;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var bool
     */
    protected $requestProcessed;

    /**
     * @param WriterInterface       $writer
     * @param LogHelper             $logHelper
     * @param DupeHelper            $dupeHelper
     * @param TokenStorageInterface $tokenStorage
     * @param EntityManager         $em
     */
    public function __construct(
        WriterInterface $writer,
        LogHelper $logHelper,
        DupeHelper $dupeHelper,
        TokenStorageInterface $tokenStorage,
        EntityManager $em
    ) {
        $this->writer       = $writer;
        $this->logHelper    = $logHelper;
        $this->dupeHelper   = $dupeHelper;
        $this->tokenStorage = $tokenStorage;
        $this->em           = $em;
    }

    /**
     * @return ApiLog
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * @param bool $requestProcessed
     *
     * @return $this
     */
    public function setRequestProcessed($requestProcessed)
    {
        $this->requestProcessed = (bool) $requestProcessed;

        return $this;
    }

    /**
     * @return LogHelper
     */
    public function getLogHelper()
    {
        return $this->logHelper;
    }

    /**
     * @return DupeHelper
     */
    public function getDupeHelper()
    {
        return $this->dupeHelper;
    }

    /**
     * @param Request $request
     *
     * @return mixed
     */
    public function getRequestId(Request $request)
    {
        return $this->logHelper->getRequestId($request->headers);
    }

    /**
     * @param Request $request
     *
     * @return ApiLog
     */
    public function createApiLog(Request $request)
    {
        if (!$this->log) {
            $this->log = $this->doCreate($request);
        }

        return $this->log;
    }

    /**
     * @param Request $request
     *
     * @return ApiLog
     */
    private function doCreate(Request $request)
    {
        $log         = new ApiLog();
        $requestData = [
            'headers' => $request->headers->all(),
            'body'    => $request->getContent(),
            'query'   => $request->query->all(),
            'post'    => $request->request->all(),
            'files'   => $request->files->all(),
            'server'  => $request->server->all(),
        ];
        $this->setRequestData($log, $requestData);

        $log
            ->setStartTime(defined('DP_START_TIME') ? DP_START_TIME : time())
            ->setRequestedUri($request->getPathInfo())
            ->setMethod($request->getMethod())
            ->setRequestId($this->getRequestId($request));
        $this->setApiLogAuthData($log);

        return $log;
    }

    /**
     * @param Response $response
     */
    public function finishApiLog(Response $response)
    {
        $responseData = [
            'headers' => $response->headers->all(),
            'body'    => $response->getContent(),
        ];

        $this->setResponseData($this->log, $responseData);

        $this->log->setEndTime(time())
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
        $options = $this->dupeHelper->getRequestOptions($request->headers);

        $skipFailedClientRequest = $this->getLogHelper()->isClientRequestedLog()
            && $options['failure_mode'] === LogHelper::FAILURE_MODE_SKIP && $options['eager'] !== LogHelper::EAGER_ON
            && !($response->isSuccessful() || $response->isRedirection());

        $shouldSave =
            (
                $this->logHelper->isLoggingEnabled() ||
                (
                    $this->getLogHelper()->isClientRequestedLog()
                    && $this->getDupeHelper()->suitableMode($this->getLogHelper()->getMode())
                )
            )
            && !$this->requestProcessed;

        if ($skipFailedClientRequest) {
            return false;
        } elseif ($shouldSave) {
            $this->saveLog();
        }

        return true;
    }

    public function saveLog()
    {
        try {
            $this->writer->write($this->log);
        } catch (\Exception $e) {
            SystemErrorHandler::logException($e);
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
        /** @var \Application\DeskPRO\EntityRepository\ApiKey $keyRepo */
        $keyRepo = $this->em->getRepository(ApiKey::class);
        if ($this->getToken()->getName() === 'api_key'
            && $key = $keyRepo->findByKeyString($this->getToken()->getCredentials())
        ) {
            /* @var \Application\DeskPRO\Entity\ApiKey $key */
            $key->addApiLog($log);
        }
    }

    /**
     * @return null|\Symfony\Component\Security\Core\Authentication\Token\TokenInterface
     */
    protected function getToken()
    {
        return $this->tokenStorage->getToken();
    }

    /**
     * @param ApiLog $log
     * @param array  $requestData
     */
    protected function setRequestData(ApiLog $log, array $requestData)
    {
        /* we're going to reduce request_data */
        $maxRequestBodyLength = $this->getLogHelper()->getMaxRequestBodyLength();
        if (mb_strlen($requestData['body'], '8bit') > $maxRequestBodyLength) {
            $requestData['body'] = substr($requestData['body'], 0, ceil(0.99 * $maxRequestBodyLength));
            $log->setIsRequestTruncated(true);
        }
        $log->setRequestData($requestData);
    }

    /**
     * @param ApiLog $log
     * @param array  $responseData
     */
    protected function setResponseData(ApiLog $log, array $responseData)
    {
        /* we're going to reduce request_data */
        $maxResponseBodyLength = $this->getLogHelper()->getMaxResponseBodyLength();
        if (mb_strlen($responseData['body'], '8bit') > $maxResponseBodyLength) {
            $responseData['body'] = substr($responseData['body'], 0, ceil(0.99 * $maxResponseBodyLength));
            $log->setIsResponseTruncated(true);
        }
        $log->setResponseData($responseData);
    }
}
