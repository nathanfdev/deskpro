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

namespace DeskPRO\Bundle\ApiBundle\Controller\Authentication;

use Application\DeskPRO\Auth\LoginProcessor;
use Application\DeskPRO\Entity\ApiToken;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TmpData;
use Application\DeskPRO\Entity\Usersource;
use Application\DeskPRO\Usersource\UsersourceAuthAdapterFactory;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\ApiBundle\Security\Authentication\ApiAuthenticator;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Exception\UsersourceNoEmailException;
use DeskPRO\Bundle\AppBundle\Form\Error\ErrorsCodes;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\BadCredentialsFormException;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\AuthenticationRequestType;
use DeskPRO\Bundle\AppBundle\Form\Type\AuthenticationType;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Orb\Auth\Adapter\CallbackInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class ApiTokensController.
 *
 * @ApiModes("all")
 * @Rest\Route("/api_tokens")
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
     * @Rest\Post("")
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

        $form = $this->createForm(AuthenticationType::class);
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

        return View::create($this->wrap($this->createToken($person)), Response::HTTP_CREATED);
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
     * @Rest\Get("/device_setup/{auth}", name="api_authenticate_device")
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

        $this->getManager()->remove($tmpData);
        $this->getManager()->flush();

        return View::create($this->wrap($this->createToken($person)), Response::HTTP_CREATED);
    }

    /**
     * @ApiDoc(
     *      description="Get list of api token usersources.",
     *      statusCodes={
     *          404="Usersource not found"
     *      }
     * )
     *
     * @Rest\Get("/user_sources/{context}", requirements={"context": "(agent|user)"})
     *
     * @param string $context
     *
     * @return View
     */
    public function usersourcesListAction($context)
    {
        $qb = $this->getManager()->createQueryBuilder();
        $qb
            ->select('e')
            ->from(Usersource::class, 'e')
            ->andWhere('e.type = :context')
            ->andWhere('e.source_type IN (:callback_sources)')
            ->setParameter('context', $context)
            ->setParameter('callback_sources', Usersource::$callbackAdapters)
        ;

        return new View($this->wrap($qb->getQuery()->getResult()));
    }

    /**
     * @ApiDoc(
     *      description="Login via usersource.",
     *      statusCodes={
     *          404="Usersource not found"
     *      }
     * )
     *
     * @Rest\Get("/user_sources/{usersource}/login")
     *
     * @param Request    $request
     * @param Usersource $usersource
     *
     * @return RedirectResponse
     */
    public function usersourceLoginAction(Request $request, Usersource $usersource)
    {
        $adapter = $this->getUsersourceAdapterFactory()->getAuthAdapter($usersource);

        if ($adapter instanceof CallbackInterface) {
            $adapter->setCallbackUrl($this->generateUrl('deskpro_api_authentication_apitokens_usersourcecallback', [
                'usersource' => $usersource->getId(),
                'format'     => $request->get('format', 'default'),
            ], UrlGeneratorInterface::ABSOLUTE_URL));

            $result = $adapter->authenticate();
            if ($result->isRedirectRequired()) {
                return $this->redirect($result->getRedirectUrl());
            } else {
                throw $this->createBadRequestException('Unable to redirect');
            }
        }

        throw $this->createBadRequestException('Not supported usersource');
    }

    /**
     * @ApiDoc(
     *      description="Returns api token on usersource callback",
     *      output="token",
     *      statusCodes={
     *          200="Created token"
     *      }
     * )
     *
     * @Rest\Get("/user_sources/{usersource}/callback/{format}", requirements={"format": "(ios|default)"})
     * @Rest\Post("/user_sources/{usersource}/callback/{format}", requirements={"format": "(ios|default)"})
     *
     * @param Request    $request
     * @param Usersource $usersource
     * @param string     $format
     *
     * @return View
     */
    public function usersourceCallbackAction(Request $request, Usersource $usersource, $format)
    {
        $adapter = $this->getUsersourceAdapterFactory()->getAuthAdapter($usersource);
        if (!$adapter instanceof CallbackInterface) {
            throw $this->createBadRequestException('Not callback usersource.');
        }

        $adapter->setCallbackUrl($request->getUriForPath($request->getPathInfo()));
        $adapter->setCallbackContext($_REQUEST);

        $result = $adapter->authenticate();
        if (!$result->isValid()) {
            $this->throwUnauthorized();
        }

        $loginProcessor = new LoginProcessor($usersource, $result->getIdentity());

        try {
            $person = $loginProcessor->getPerson();
            $person->setLastLoginAt();

            $this->getManager()->persist($person);
            $this->getManager()->flush();
        } catch (UsersourceNoEmailException $e) {
            $person = null;
            $this->throwUnauthorized();
        }

        return $this->render("ApiBundle::ApiTokens/$format.html.twig", [
            'token' => $this->createToken($person),
        ]);
    }

    /**
     * @param Person $person
     *
     * @return ApiToken
     */
    private function createToken(Person $person)
    {
        $token = new ApiToken();
        $token->setPerson($person);
        $token->setScope(ApiToken::SCOPE_CLIENT);

        $this->getManager()->persist($token);
        $this->getManager()->flush($token);

        return $token;
    }

    /**
     * @throws UnauthorizedHttpException
     */
    private function throwUnauthorized()
    {
        throw new UnauthorizedHttpException(ApiAuthenticator::HTTP_REALM, ErrorsCodes::BAD_CREDENTIALS);
    }

    /**
     * @return UsersourceAuthAdapterFactory
     */
    protected function getUsersourceAdapterFactory()
    {
        return $this->getContainer()->getSystemService('usersource_auth_adapter_factory');
    }
}
