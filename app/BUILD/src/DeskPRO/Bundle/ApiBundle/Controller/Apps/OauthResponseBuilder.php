<?php namespace DeskPRO\Bundle\ApiBundle\Controller\Apps;

use League\OAuth2\Client\Token\AccessToken;
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

    private $callbackUrl;

    /** @var string */
    private $oauthVersion;

    /**
     * @param bool $buildErrorResponse
     * @param $oauthVersion
     */
    public function __construct($buildErrorResponse, $oauthVersion)
    {
        $this->buildErrorResponse = $buildErrorResponse;
        $this->oauthVersion = $oauthVersion;
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
        return $this;
    }

    /**
     * @param string $url
     * @return OauthResponseBuilder
     */
    public function withRedirectUrl($url)
    {
        $this->callbackUrl = $url;
        return $this;
    }

    /**
     * @param string $callbackType
     * @return Response
     * @throws \DomainException
     */
    public function build($callbackType)
    {
        switch ($callbackType) {
            case 'postMessage' :
                return $this->buildPostMessage();
        }

        throw new \DomainException(sprintf('unknown callback type: %s', $callbackType));
    }

    /**
     * @return Response
     */
    public function buildPostMessage()
    {
        $templateVars = [
            'message' => json_encode([
                'type' => 'oauth-proxy-callback',
                'status' => $this->buildErrorResponse ? 'error' : 'success',
                'oauthVersion' => $this->oauthVersion,
                'body' => $this->messageProps
            ]),
            'windowUrl' => $this->callbackUrl
        ];


        $content = <<<EOT
<html>
<body>
<script>
if (window.opener != null && !window.opener.closed) {
    window.opener.postMessage({$templateVars['message']}, '{$templateVars['windowUrl']}')
}
window.close();
</script>
</body>
</html>

EOT;

        $status = $this->buildErrorResponse ? 400 : 200;
        $headers = [ 'Content-Type' =>  'text/html; charset=UTF8' ];
        return new Response($content, $status, $headers);
    }
}
