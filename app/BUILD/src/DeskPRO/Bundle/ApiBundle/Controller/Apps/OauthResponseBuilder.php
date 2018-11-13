<?php namespace DeskPRO\Bundle\ApiBundle\Controller\Apps;

use DeskPRO\Bundle\AppStoreBundle\Infrastructure\Security\OauthErrorCodes;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure\Security\OauthException;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class OauthResponseBuilder
{
    /**
     * @param string $type
     * @param string $oauthVersion
     * @return OauthResponseBuilder
     */
    public static function forResponseType($type = 'token', $oauthVersion = '2.0') {
        return new OauthResponseBuilder($type === 'error', $oauthVersion);
    }

    /** @var string */
    private $buildErrorResponse ;

    /** @var array */
    private $messageProps = [];

    /** @var string */
    private $oauthVersion;

    /** @var string */
    private $appState = '';

    /** @var int  */
    private $httpStatus;

    private $messagePropNames = ['token', 'error', 'state'];

    /**
     * @param bool $buildErrorResponse
     * @param $oauthVersion
     */
    public function __construct($buildErrorResponse, $oauthVersion)
    {
        $this->buildErrorResponse = $buildErrorResponse;
        $this->oauthVersion = $oauthVersion;
        $this->httpStatus = $buildErrorResponse ? 400 : 200;
    }

    /**
     * @param string $state
     * @return OauthResponseBuilder
     */
    public function withApplicationState($state)
    {
        $this->appState = $state;
        return $this;
    }

    /**
     * @param AccessToken $token
     * @return OauthResponseBuilder
     */
    public function withOauth2Token( AccessToken $token)
    {
        $params = $token->jsonSerialize();
        return $this->withTokenParams($params);
    }

    /**
     * @param array $params
     * @return OauthResponseBuilder
     */
    public function withTokenParams(array $params)
    {
        $this->messageProps['token'] = $params;
        return $this;
    }

    /**
     * @param string $message
     * @param int $code
     * @return OauthResponseBuilder
     */
    public function withError($code, $message = null, array $props = [])
    {
        $httpCodes = [
            OauthErrorCodes::CODE_BAD_CREDENTIALS => 400,
            OauthErrorCodes::CODE_BAD_REQUEST => 400,
            OauthErrorCodes::CODE_CONNECTION_NOT_FOUND => 404,
            OauthErrorCodes::CODE_PROVIDER_NOT_FOUND => 404,
            OauthErrorCodes::CODE_GENERIC_FAILURE => 500,
        ];
        $this->httpStatus = array_key_exists($code, $httpCodes) ? $httpCodes[$code] : 500;

        $messages = [
            OauthErrorCodes::CODE_BAD_CREDENTIALS => "Bad Credentials",
            OauthErrorCodes::CODE_BAD_REQUEST => "Invalid Request",
            OauthErrorCodes::CODE_CONNECTION_NOT_FOUND => "Oauth connection not found",
            OauthErrorCodes::CODE_PROVIDER_NOT_FOUND => "Oauth provider not found",
            OauthErrorCodes::CODE_GENERIC_FAILURE => "An Oauth error occurred",
        ];

        $errorMessage = $message;
        if (empty($errorMessage)) {
            $errorMessage = array_key_exists($code, $messages) ? $messages[$code] : $messages[OauthErrorCodes::CODE_GENERIC_FAILURE];
        }
        $this->messageProps['error'] = $errorMessage;

        $otherProps = array_diff(array_keys($props), $this->messagePropNames);
        foreach ($otherProps as $name) {
            $this->messageProps[$name] = $props[$name];
        }

        return $this;
    }

    /**
     * @param OauthException $e
     * @return OauthResponseBuilder
     */
    public function withOauthException(OauthException $e)
    {
        return $this->withError($e->getCode(), $e->getMessage());
    }

    /**
     * @return JsonResponse
     */
    public function buildJSON()
    {
        return new JsonResponse($this->messageProps, $this->httpStatus);
    }

    /**
     * @return Response
     */
    public function buildPostMessage($callbackUrl)
    {
        $message = json_encode([
            'type' => 'oauth-proxy-callback',
            'status' => $this->buildErrorResponse ? 'error' : 'success',
            'http_status' => $this->httpStatus,
            'oauthVersion' => $this->oauthVersion,
            'state' => $this->appState,
            'body' => $this->messageProps
        ]);
        $content = <<<EOT
<html>
<body>
<script>
if (window.opener != null && !window.opener.closed) {
    window.opener.postMessage({$message}, '{$callbackUrl}')
}
window.close();
</script>
</body>
</html>

EOT;

        $headers = [ 'Content-Type' =>  'text/html; charset=UTF8' ];
        return new Response($content, $this->httpStatus, $headers);
    }
}
