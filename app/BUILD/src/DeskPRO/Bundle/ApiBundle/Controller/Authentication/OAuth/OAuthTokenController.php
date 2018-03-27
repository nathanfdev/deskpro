<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Authentication\OAuth;

use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use FOS\RestBundle\Controller\Annotations as Rest;
use OAuth2\OAuth2;
use OAuth2\OAuth2ServerException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class OAuthTokenController.
 *
 * @ApiModes("all")
 * @Rest\Route("/oauth/token")
 * @ApiUserContext("open")
 */
class OAuthTokenController extends BaseController
{
    /**
     * {@inheritdoc}
     *
     * @Rest\Get("", name="api_oauth_get_token")
     * @Rest\Post("")
     */
    public function tokenAction(Request $request)
    {
        try {
            if ($request->getMethod() === 'POST') {
                $input = $request->request->all();
            } else {
                $input = $request->query->all();
            }

            // force disable 'client credentials' auth type as no person is linked here
            if (isset($input['grant_type']) && $input['grant_type'] === OAuth2::GRANT_TYPE_CLIENT_CREDENTIALS) {
                throw new OAuth2ServerException(OAuth2::HTTP_BAD_REQUEST, OAuth2::ERROR_UNAUTHORIZED_CLIENT, 'This grant type is not supported');
            }

            return $this->container->get('fos_oauth_server.server')->grantAccessToken($request);
        } catch (OAuth2ServerException $e) {
            return $e->getHttpResponse();
        }
    }
}
