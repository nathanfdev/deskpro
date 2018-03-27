<?php

namespace DeskPRO\Bundle\ApiBundle\Request;

use DeskPRO\Component\Util\RegexUtils;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class ApiClientInfo.
 */
class ApiClientInfo
{
    /**
     * @var string
     */
    private $clientType;

    /**
     * @var string
     */
    private $clientVersion;

    /**
     * ApiClientInfo constructor.
     *
     * @param string $clientType
     * @param string $clientVersion
     */
    public function __construct($clientType, $clientVersion)
    {
        $this->clientType    = $clientType;
        $this->clientVersion = (string) $clientVersion;
    }

    /**
     * Creates ApiClientInfo from the current request.
     *
     * Expects format: clientType (vVERSION)
     * (Where VERSION should be a semver compatible version number)
     *
     * Examples:
     *  - ios (v1.0.3)
     *  - iOS (v1.0.92)
     *  - android (v0.5)
     *
     * Defaults to 'standard' if none specified.
     *
     * @param RequestStack $requestStack
     *
     * @return ApiClientInfo
     */
    public static function createFromRequestStack(RequestStack $requestStack)
    {
        if (!$request = $requestStack->getMasterRequest()) {
            return new self('standard', '0');
        }

        $header = $request->headers->get('X-DeskPRO-API-ClientType', 'standard');
        $header = strtolower($header);

        if ($m = RegexUtils::getMatches('/^(?P<clientType>\w+)(\s*\(v?(?P<clientVersion>.*?)\))?$/', $header)) {
            return new self($m['clientType'], !empty($m['clientVersion']) ? $m['clientVersion'] : '0');
        } else {
            return new self($header, '0');
        }
    }

    /**
     * @return bool
     */
    public function isIos()
    {
        return $this->clientType === 'ios';
    }

    /**
     * @return bool
     */
    public function isMobileClient()
    {
        return $this->isIos();
    }

    /**
     * Get the client type.
     *
     * @return string
     */
    public function getClientType()
    {
        return $this->clientType;
    }

    /**
     * Get the client version.
     *
     * @return string
     */
    public function getClientVersion()
    {
        return $this->clientVersion;
    }
}
