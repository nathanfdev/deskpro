<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
