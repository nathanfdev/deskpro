<?php namespace DeskPRO\Bundle\ApiBundle\Controller\Apps;

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

    /** @var int  */
    private $httpStatus;

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
        $this->messageProps['state'] = $state;
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
     * @return OauthResponseBuilder
     */
    public function withErrorType($message)
    {
        $this->messageProps['error'] = $message;

        if ($message === 'Connection not found') {
            $this->httpStatus = 404;
        }

        return $this;
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
