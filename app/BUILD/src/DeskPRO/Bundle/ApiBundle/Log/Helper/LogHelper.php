<?php

namespace DeskPRO\Bundle\ApiBundle\Log\Helper;

use DeskPRO\Component\Util\RandUtils;
use Symfony\Component\HttpFoundation\HeaderBag;

/**
 * Class LogHelper.
 */
class LogHelper extends AbstractLogHelper
{
    /**
     * @var string
     */
    protected $requestId;

    /**
     * @var bool
     */
    protected $clientGeneratedRequestId = false;

    /**
     * @var string
     */
    protected $mode;

    /**
     * @param null|array|HeaderBag $headers
     *
     * @return string|null
     */
    public function getRequestId($headers = null)
    {
        if (!$this->requestId) {
            $headers         = $this->mutateHeaders($headers);
            $this->requestId =
                ($headers->has(self::REQUEST_ID_CLIENT_HEADER))
                    ? $this->generateRequestId(true, $headers->get(self::REQUEST_ID_CLIENT_HEADER))
                    : $this->generateRequestId();
        }

        return $this->requestId;
    }

    /**
     * @param bool|false $incoming
     * @param string     $incomingValue
     *
     * @return string
     */
    protected function generateRequestId($incoming = false, $incomingValue = '')
    {
        if ($incoming && !$incomingValue) {
            throw new \InvalidArgumentException(
                sprintf(
                    'In case you have incoming %s header, you should provide it\'s value when generating ID',
                    self::REQUEST_ID_CLIENT_HEADER
                ));
        }
        if ($incoming) {
            $value                          = $incomingValue;
            $suffix                         = self::CLIENT_SUFFIX;
            $prefix                         = '';
            $this->clientGeneratedRequestId = true;
        } else {
            $value  = RandUtils::randomStringFormat('%30cn');
            $suffix = self::DESKPRO_SUFFIX;
            $prefix = time().'-';
        }

        return sprintf('%s%s-%s', $prefix, $value, $suffix);
    }

    /**
     * @return bool
     */
    public function isLoggingEnabled()
    {
        return $this->resolver->getGlobalSettings()->get('api_log.enabled')
            && in_array($this->mode, $this->getModes());
    }

    /**
     * @return array
     */
    public function getModes()
    {
        return $this->resolver->getGlobalSettings()->getSerializedArray('api_log.modes', []);
    }

    /**
     * @return bool
     */
    public function isClientRequestedLog()
    {
        return $this->clientGeneratedRequestId;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param string $mode
     *
     * @return $this
     */
    public function setMode($mode)
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxRequestBodyLength()
    {
        $maxBodyLength = $this->resolver->getGlobalSettings()->get('api_log.max_request_body_length', 1024 * 1024);

        return is_numeric($maxBodyLength) ? $maxBodyLength : 1024 * 1024;
    }

    /**
     * @return int
     */
    public function getMaxResponseBodyLength()
    {
        $maxBodyLength = $this->resolver->getGlobalSettings()->get('api_log.max_response_body_length', 1024 * 1024);

        return is_numeric($maxBodyLength) ? $maxBodyLength : 1024 * 1024;
    }
}
