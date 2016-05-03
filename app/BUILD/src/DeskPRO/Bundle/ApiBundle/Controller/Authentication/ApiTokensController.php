<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\ApiBundle\Security\Authentication\ApiAuthenticator;
use DeskPRO\Bundle\ApiBundle\Security\Token\AbstractApiSecurityToken;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Error\ErrorsCodes;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\BadCredentialsFormException;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\AuthenticationRequestType;
use DeskPRO\Bundle\AppBundle\Form\Type\AuthenticationType;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * Class ApiTokensController.
 *
 * @ApiModes("all")
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
     * @Rest\Post("/api_tokens")
     * @Rest\View(serializerGroups={"token"})
     *
     * @param Request $request
     *
     * @return View
     */
    public function newTokenAction(Request $request)
    {
        $request_data = $request->request->all();

        $form = $this->createForm(new AuthenticationRequestType());
        $form->submit($request_data);
        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        $form = $this->createForm(new AuthenticationType());
        $form->submit($request_data);
        if (!$form->isValid()) {
            throw new BadCredentialsFormException($form);
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

        $person = $this->getManager()->getRepository(Person::class)->find($person_id);
        if (!$person) {
            $this->throwUnauthorized();
        }

        $token         = new ApiToken();
        $token->person = $person;
        $token->scope  = ApiToken::SCOPE_CLIENT;

        $this->getManager()->persist($token);
        $this->getManager()->flush($token);

        return View::create($this->wrap($token), Response::HTTP_CREATED);
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
     * @Rest\Get("/api_tokens/device-setup/{auth}")
     * @Rest\View(serializerGroups={"token", "discover"})
     *
     * @param string $auth
     *
     * @return View
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

        return View::create($this->wrap($token), Response::HTTP_CREATED);
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
        throw new UnauthorizedHttpException(ApiAuthenticator::HTTP_REALM, ErrorsCodes::BAD_CREDENTIALS);
    }
}
