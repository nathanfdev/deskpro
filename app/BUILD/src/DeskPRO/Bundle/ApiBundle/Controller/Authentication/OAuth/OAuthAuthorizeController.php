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
