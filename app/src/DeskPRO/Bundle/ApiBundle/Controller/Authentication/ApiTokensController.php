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
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TmpData;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\ApiBundle\Error\ApiErrors;
use DeskPRO\Bundle\ApiBundle\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\ApiBundle\Security\Authentication\ApiAuthenticator;
use DeskPRO\Bundle\ApiBundle\Security\Token\AbstractApiSecurityToken;
use DeskPRO\Bundle\AppBundle\Form\Type\AuthenticationType;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * Class ApiTokensController.
 */
class ApiTokensController extends BaseController
{
    /**
     * @ApiDoc(
     *      description="create a new api token",
     *      output="token",
     *      statusCodes={
     *          201="Created token",
     *          401="Invalid credentials",
     *          400="Bad request"
     *      }
     * )
     *
     * @Post("/api_tokens", name="api_post_api_tokens")
     *
     * @param Request $request
     *
     * @return View
     */
    public function newTokenAction(Request $request)
    {
        $form = $this->createForm(new AuthenticationType());
        $form->submit($request->request->all());

        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        $data     = $form->getData();
        $email    = $data['email'];
        $password = $data['password'];

        $auth_result = $this->get('dp_authentication_manager.agent')->authenticateFormLogin($email, $password);

        if (!$auth_result->isValid()) {
            // failed on agent usersources, revert to user
            $auth_result = $this->get('dp_authentication_manager.user')->authenticateFormLogin($email, $password);
            if (!$auth_result->isValid()) {
                $this->throwUnauthorized();
            }
        }

        $identity  = $auth_result->getIdentity();
        $person_id = $identity->getIdentity();

        if (!$person_id) {
            $this->throwUnauthorized();
        }

        $em     = $this->get('doctrine.orm.default_entity_manager');
        $person = $em->getRepository('DeskPRO:Person')->find($person_id);

        if (!$person) {
            $this->throwUnauthorized();
        }

        $api_token         = new ApiToken();
        $api_token->person = $person;
        $api_token->scope  = ApiToken::SCOPE_CLIENT;

        $em = $this->get('doctrine.orm.default_entity_manager');
        $em->persist($api_token);
        $em->flush($api_token);

        return View::create(
            $this->createRepresentation(
                [
                    'person_id' => $person->id,
                    'token'     => $api_token->id.':'.$api_token->token,
                ]
            ),
            Response::HTTP_CREATED
        );
    }

    /**
     * @ApiDoc(
     *      description="Authenticate device by authorization code and return auth tokens",
     *      output="token",
     *      statusCodes={
     *          201="Created token",
     *          404="Auth code not found"
     *      }
     * )
     * @Get("/api_tokens/device-setup/{auth}", name="api_authenticate_device")
     */
    public function authenticateDeviceAction($auth)
    {
        /** @var TmpData $tmpData */
        if (!$tmpData = $this->getRepository(TmpData::class)->findOneBy(['auth' => $auth])) {
            throw $this->createNotFoundException();
        }
        if (!$person = $this->getManager()->find(Person::class, $tmpData->getData('agent_id'))) {
            throw $this->createNotFoundException();
        }

        $token         = new ApiToken();
        $token->person = $person;
        $token->scope  = ApiToken::SCOPE_CLIENT;
        $this->getManager()->remove($tmpData);
        $this->getManager()->persist($token);
        $this->getManager()->flush();

        return View::create(
            $this->createRepresentation([
                'token' => $token->id.':'.$token->token,
            ]),
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

    protected function throwUnauthorized()
    {
        throw new UnauthorizedHttpException(ApiAuthenticator::HTTP_REALM, ApiErrors::BAD_CREDENTIALS);
    }
}
