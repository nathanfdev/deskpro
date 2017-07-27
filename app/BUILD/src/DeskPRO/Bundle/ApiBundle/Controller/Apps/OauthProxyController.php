<?php namespace DeskPRO\Bundle\ApiBundle\Controller\Apps;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Entity\AppStore\AppInstance;
use DeskPRO\Bundle\AppBundle\Util\HttpClient;
use FOS\RestBundle\Controller\Annotations as Rest;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use League\OAuth2\Client\Provider\GenericProvider;
use Psr\Http\Message\ResponseInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class HttpProxyController.
 *
 * @ApiModes("session")
 * @ApiUserContext("agent")
 * @Rest\Route("/apps/oauth-proxy")
 */
class OauthProxyController
{
    /**
     * @ParamConverter("instance", class="AppBundle:Entity\AppStore\AppInstance", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\AppInstanceParamConverter")
     *
     * @Rest\Get("/authorize/{instance}/{provider}")
     */
    public function authorize(AppInstance $instance, $provider, Request $request)
    {
        if (is_null($provider)) {
            return new Response('Provider not found', 400);
        }

        $clientProfile = $request->query->get('client_profile', 'web-server'); //web-server, user-agent, [ native applications, autonomous clients ]
        if (! in_array($clientProfile, ['web-server', 'user-agent'])) {
            return new Response('Invalid client profile not found', 400);
        }

        $callbackMethod = $request->query->get('callbackMethod', 'postMessage');
        if ($callbackMethod !== 'postMessage') {
            return new Response('Invalid callback method', 400);
        }

        $state = $request->query->get('state');
        $callbackUrl = $request->query->get('callbackUrl');

        if ($clientProfile === 'web-server') {
            $clientDetails = reset($provider['clients']);

            $proxyState = [
                'appState' => $state,
                'callbackMethod' => $callbackMethod,
                'callbackUrl' => $callbackUrl,
            ];

            $clientDetails['state'] = base64_encode(json_encode($proxyState));
            $provider = new GenericProvider($clientDetails);

            $authUrl = $provider->getAuthorizationUrl($clientDetails);
            return new RedirectResponse($authUrl);
        }

        return new Response('Bad Request', 400);
    }

    /**
     * @Rest\Get("/grant-access/{instance}/{provider}")
     */
    public function grantAccess(AppInstance $instance, $provider, Request $request)
    {
        try {
            $state = $request->query->get('state');
            $code = $request->query->get('code');
            $responseType = $request->query->get('response_type', 'code'); // === 'code'

            if (! in_array($responseType, ['code', 'error'])) {
                throw new \RuntimeException('unknown response type');
            }
            $proxyState = json_decode(base64_decode($state), $decodeArray = true);

            if ($responseType === 'code') {

                $clientDetails = reset($provider['clients']);
                $clientDetails = array_merge($clientDetails, [
                    'urlAuthorize',
                    'urlAccessToken',
                    'urlResourceOwnerDetails',
                ]);

                $provider = new GenericProvider($clientDetails);
                $token = $provider->getAccessToken('authorization_code', [ 'code' => $code ]);

                return Oauth2ResponseBuilder::forResponseType('token')
                    ->withApplicationState($proxyState['appState'])
                    ->withToken($token)
                    ->withCallbackUrl($proxyState['callbackUrl'])
                    ->buildPostMessage()
                    ;
            } else { // $responseType === 'error'
                return Oauth2ResponseBuilder::forResponseType('error')
                    ->withApplicationState($proxyState['appState'])
                    ->withCallbackUrl($proxyState['callbackUrl'])
                    ->buildPostMessage()
                    ;
            }
        } catch (\Exception $e) {
            return new Response('Unexpected server error', 500);
        }
    }
}
