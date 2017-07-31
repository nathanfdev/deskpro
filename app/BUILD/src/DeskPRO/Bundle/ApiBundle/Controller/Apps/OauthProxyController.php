<?php namespace DeskPRO\Bundle\ApiBundle\Controller\Apps;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Entity\AppStore\AppInstance;
use DeskPRO\Bundle\AppBundle\Entity\AppStore\AppState;
use DeskPRO\Bundle\AppBundle\Entity\Repository\AppStateRepository;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure\Security\SerializedOauthConnection;
use FOS\RestBundle\Controller\Annotations as Rest;
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
class OauthProxyController extends BaseController
{
    /**
     * @param AppInstance|string $instance
     * @param $providerName
     * @return SerializedOauthConnection|null
     */
    private function loadOauthConnection($instance, $providerName)
    {
        $stateName = sprintf('oauth:%s', $providerName);
        $person = $this->getUser();

        /** @var AppStateRepository $appStateRepo */
        $appStateRepo = $this->getRepository(AppState::class);
        $appState = $appStateRepo->findOneReadableByName($instance, $person, $stateName);

        if ($appState instanceof AppState) {
            return SerializedOauthConnection::fromJSON($appState->getValue());
        }
        return null;
    }

    /**
     * @Rest\Get("/authorize")
     */
    public function authorizeAction(AppInstance $instance = null, Request $request)
    {
        $provider = $request->query->get('provider', null);
        $applicationId = $request->query->get('applicationId', null);
        if (is_null($provider) || is_null($applicationId)) {
            return new Response('Connection not found', 400);
        }

        $clientProfile = $request->query->get('client_profile', 'web-server'); //web-server, user-agent, [ native applications, autonomous clients ]
        if (!in_array($clientProfile, ['web-server', 'user-agent'])) {
            return new Response('Invalid client profile not found', 400);
        }

        $callbackMethod = $request->query->get('callbackMethod', 'postMessage');
        if ($callbackMethod !== 'postMessage') {
            return new Response('Invalid callback method', 400);
        }

        /** @var SerializedOauthConnection $connection */
        $connection = null;

        if ($clientProfile === 'web-server') {
            $connection = $this->loadOauthConnection($applicationId, $provider);
            if (empty($connection)) {
                return new Response('Connection not found', 400);
            }
        }

        $state = $request->query->get('state');
        $callbackUrl = $request->query->get('callbackUrl');

        if ($clientProfile === 'web-server') {
            $proxyState = [
                'appState' => $state,
                'callbackMethod' => $callbackMethod,
                'callbackUrl' => $callbackUrl,
            ];

            $options['state'] = base64_encode(json_encode($proxyState));
            $autorizationUrl = $connection->getAuthorizationUrl($options);
            return new RedirectResponse($autorizationUrl);
        }

        return new Response('Bad Request', 400);
    }

    /**
     * @ParamConverter("application", class="AppBundle:Entity\AppStore\AppInstance", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\AppInstanceParamConverter")
     *
     * @Rest\Get("/grant-access/{application}/{provider}")
     * @param AppInstance $application
     * @param string|null $provider
     * @param Request $request
     * @return Response
     */
    public function grantAccessAction(AppInstance $application = null, $provider = null, Request $request)
    {
        if (is_null($provider) || is_null($application)) {
            return new Response('Connection not found', 400);
        }

        $state = $request->query->get('state');
        $code = $request->query->get('code');
        $responseType = $request->query->get('response_type', 'code'); // === 'code'

        if (!in_array($responseType, ['code', 'error'])) {
            throw new \RuntimeException('unknown response type');
        }
        $proxyState = json_decode(base64_decode($state), $decodeArray = true);

        if ($responseType === 'code') {
            $connection = $this->loadOauthConnection($application, $provider);
            if (empty($connection)) {
                return new Response('Connection not found', 400);
            }

            $token = $connection->getAccessToken('authorization_code', ['code' => $code]);

            return OauthResponseBuilder::forResponseType('token')
                ->withApplicationState($proxyState['appState'])
                ->withToken($token)
                ->withCallbackUrl($proxyState['callbackUrl'])
                ->buildPostMessage();
        }

        // $responseType === 'error'
        return OauthResponseBuilder::forResponseType('error')
            ->withApplicationState($proxyState['appState'])
            ->withCallbackUrl($proxyState['callbackUrl'])
            ->buildPostMessage();

    }
}
