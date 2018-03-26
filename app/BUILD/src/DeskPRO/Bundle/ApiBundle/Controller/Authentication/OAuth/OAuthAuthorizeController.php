<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Authentication\OAuth;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Entity\OAuthClient;
use FOS\OAuthServerBundle\Controller\AuthorizeController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class OAuthAuthorizeController.
 *
 * @ApiModes("all")
 * @Rest\Route("/oauth/auth")
 * @ApiUserContext("open")
 */
class OAuthAuthorizeController extends AuthorizeController
{
    /**
     * {@inheritdoc}
     *
     * @Rest\Get("", name="fos_oauth_server_authorize")
     * @Rest\Post("")
     */
    public function authorizeAction(Request $request)
    {
        try {
            if ($request->isMethod('GET')) {
                $clientId = $request->query->get('client_id');
            } else {
                $formName  = $this->container->get('fos_oauth_server.authorize.form')->getName();
                $submitted = $request->request->get($formName);
                $clientId  = isset($submitted['client_id']) ? $submitted['client_id'] : '';
            }

            if (!is_string($clientId) || strpos($clientId, '_') === false) {
                return $this->container->get('templating')->renderResponse('ApiBundle:OAuth:login_error.html.twig', [
                    'message' => 'api.oauth.error_not_found',
                ]);
            }

            list($id, $randomId) = explode('_', $clientId);

            $em         = $this->container->get('doctrine.orm.default_entity_manager');
            $person     = $this->container->get('security.token_storage')->getToken()->getUser();
            $authClient = $em->getRepository(OAuthClient::class)->findOneBy([
                'id'       => $id,
                'randomId' => $randomId,
            ]);

            if (!$person instanceof Person || !$authClient) {
                return $this->container->get('templating')->renderResponse('ApiBundle:OAuth:login_error.html.twig', [
                    'message' => 'api.oauth.error_access_denied',
                ]);
            }
            if ($authClient->getContext() === OAuthClient::CONTEXT_AGENT && !$person->isActiveAgent()) {
                return $this->container->get('templating')->renderResponse('ApiBundle:OAuth:login_error.html.twig', [
                    'message' => 'api.oauth.error_access_denied',
                ]);
            }

            return parent::authorizeAction($request);
        } catch (\Exception $e) {
            return $this->container->get('templating')->renderResponse('ApiBundle:OAuth:authorize_error.html.twig', [
                'exception' => $e,
            ]);
        }
    }
}
