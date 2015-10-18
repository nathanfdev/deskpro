<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\ApiBundle\Controller\Authentication;

use Application\DeskPRO\Entity\ApiToken;
use DeskPRO\Bundle\ApiBundle\Security\Token\AbstractApiSecurityToken;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ApiTokensController.
 */
class ApiTokensController extends AbstractAuthController
{
    /**
     * @ApiDoc(
     *      description="create a new api token",
     *      input="email,password",
     *      output="token",
     *      statusCodes={
     *          201="Created token",
     *          401="Invalid credentials",
     *          400="Bad request"
     *      }
     * )
     *
     * @Post("/api_tokens", name="post_api_tokens")
     *
     * @param Request $request
     *
     * @return View
     */
    public function newTokenAction(Request $request)
    {
        $person = $this->getPerson($request);

        $api_token         = new ApiToken();
        $api_token->person = $person;
        $api_token->scope  = ApiToken::SCOPE_CLIENT;

        $em = $this->get('doctrine.orm.default_entity_manager');
        $em->persist($api_token);
        $em->flush($api_token);

        return View::create(
            $this->createRepresentation(
                [
                    'token' => $api_token->id.':'.$api_token->token,
                ]
            ),
            Response::HTTP_CREATED
        );
    }

    /**
     * @param AbstractApiSecurityToken $token
     *
     * @return string
     */
    protected function makeAuthMethodString(AbstractApiSecurityToken $token)
    {
        return $token->getName();
    }
}
