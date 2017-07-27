<?php namespace DeskPRO\Bundle\ApiBundle\Controller\Apps;

use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\HttpFoundation\Response;

class Oauth2ResponseBuilder
{
    /**
     * @param string $type
     * @return Oauth2ResponseBuilder
     */
    public static function forResponseType(string $type = 'token') {
        return new Oauth2ResponseBuilder($type === 'error');
    }

    /** @var string */
    private $buildErrorResponse ;

    /** @var array */
    private $messageProps = [];

    private $callbackUrl;

    public function __construct(bool $buildErrorResponse)
    {
        $this->buildErrorResponse = $buildErrorResponse;
    }

    /**
     * @param string $state
     * @return Oauth2ResponseBuilder
     */
    public function withApplicationState(string $state)
    {
        $this->messageProps['state'] = $state;
        return $this;
    }

    /**
     * @param AccessToken $token
     * @return Oauth2ResponseBuilder
     */
    public function withToken(AccessToken $token)
    {
        $params = $token->jsonSerialize();
        return $this->withTokenParams($params);
    }

    /**
     * @param array $params
     * @return Oauth2ResponseBuilder
     */
    public function withTokenParams(array $params)
    {
        $this->messageProps['token'] = $params;
        return $this;
    }

    /**
     * @param string $url
     * @return Oauth2ResponseBuilder
     */
    public function withCallbackUrl(string $url)
    {
        $this->callbackUrl = $url;
        return $this;
    }

    /**
     * @param string $callbackType
     * @return Response
     * @throws \DomainException
     */
    public function build(string $callbackType)
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
        return new Response($content, $status);
    }
}
