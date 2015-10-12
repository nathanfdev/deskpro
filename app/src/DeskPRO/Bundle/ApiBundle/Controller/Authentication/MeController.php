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

use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\ApiBundle\Model\Me;
use DeskPRO\Bundle\ApiBundle\Security\Token\AbstractApiSecurityToken;
use DeskPRO\Bundle\ApiBundle\Security\Token\AgentSessionSecurityToken;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class MeController extends BaseController
{
    /**
     * @ApiDoc(
     *      description="get information about the authenticated user",
     *      output="DeskPRO\Bundle\ApiBundle\Model\Me",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     *
     * @Get("/me", name="me")
     */
    public function meAction()
    {
        /** @var \DeskPRO\Bundle\ApiBundle\Security\Token\AbstractApiSecurityToken $token */
        $token = $this->get('security.token_storage')->getToken();

        $person = $token->getUser();

        $me              = new Me();
        $me->auth_method = $this->makeAuthMethodString($token);
        $me->id          = $token->getUser()->getId();
        $me->person_id   = $token->getUser()->getId();
        $me->person      = $person->toApiData(); //TODO

        if ($token instanceof AgentSessionSecurityToken) {
            $me->app_id = $token->getAppId();
        }

        return View::create(
            $this->createRepresentation(
               $me
            ),
            200
        );
    }

    protected function makeAuthMethodString(AbstractApiSecurityToken $token)
    {
        return $token->getName();
    }
}
