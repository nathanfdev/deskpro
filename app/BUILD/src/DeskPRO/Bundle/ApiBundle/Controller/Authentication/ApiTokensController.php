<?php

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
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\LoginAbuseCheck;
use DeskPRO\Bundle\AppBundle\Exception\UsersourceNoEmailException;
use DeskPRO\Bundle\AppBundle\Form\Error\ErrorsCodes;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\AbuseCaptchaFormException;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiTokenLoginType;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiTokens\MagicLinkEmailType;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Component\Util\ListUtils;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Orb\Auth\Adapter\CallbackInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class ApiTokensController.
 *
 * @ApiModes("all")
 * @Rest\Route("/api_tokens")
 * @ApiUserContext("open")
 * @ApiDoc(target="all", section="Auth")
 */
class ApiTokensController extends BaseController
{
    /**
     * @ApiDoc(
     *      description="creates a new api token based on the authenticated user's session",
     *      output="token",
     *      statusCodes={
     *          201="Created token",
     *          401="Invalid credentials",
     *      }
     * )
     * @Rest\Get("/session")
     * @Rest\View(serializerGroups={"token"})
     *
     *
     * @param Request $request
     * @return View
     */
    public function newSessionTokenAction(Request $request) {
        $person = $this->getUser();
        if (!$person) {
            $this->throwUnauthorized();
        }

        return View::create($this->wrap($this->createToken($person)), Response::HTTP_CREATED);
    }

    /**
     * @ApiDoc(
     *      description="create a new api token",
     *      output="token",
     *      parameters={
     *          { "name" = "email", "dataType" = "string", "format" = "string", "required" = true, "description" = "Email address" },
     *          { "name" = "password", "dataType" = "string", "format" = "string", "required" = true, "description" = "Password" },
     *      },
     *      statusCodes={
     *          201="Created token",
     *          401="Invalid credentials",
     *          400="Bad request"
     *      },
     *     input= {
     *         "class"="DeskPRO\Bundle\AppBundle\Form\Type\AuthenticationRequestType"
     *     }
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
        $form = $this->createForm(ApiTokenLoginType::class);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        $email    = $form->get('email')->getData();
        $password = $form->get('password')->getData();

        // abuse checks
        $abuseCheck  = new LoginAbuseCheck($email, $request->getClientIp());
        $abuseResult = $this->container->get('anti_abuse')->check($abuseCheck);

        if ($abuseResult->isLockoutRecommended()) {
            throw new TooManyRequestsHttpException();
        }
        if (!$form->has('captcha') && $abuseResult->isCaptchaRecommended()) {
            throw new AbuseCaptchaFormException($form);
        }

        // authenticate user
        $authResult = $this->get('dp_authentication_manager.agent')->authenticateFormLogin($email, $password);

        if (!$authResult->isValid()) {
            // failed on agent usersources, revert to user
            $authResult = $this->get('dp_authentication_manager.user')->authenticateFormLogin($email, $password);
            if (!$authResult->isValid()) {
                $this->container->get('anti_abuse')->saveRateLimit($abuseResult);
                $this->throwUnauthorized();
            }
        }

        $identity = $authResult->getIdentity();
        $personId = $identity->getIdentity();

        if (!$personId) {
            $this->throwUnauthorized();
        }

        $person = $this->getManager()->getRepository(Person::class)->find($personId);
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
     *     description="Get list of api token usersources.",
     *     statusCodes={
     *         404="Usersource not found"
     *     },
     *     output="array<DeskPRO\Bundle\AppBundle\Serializer\Model\Usersource>"
     * )
     *
     * @Rest\Get("/user_sources/{context}.{_format}", requirements={"context": "(agent|user)", "_format": "(json|html)"})
     *
     * @param string $context
     * @param string $_format
     *
     * @return View|Response
     */
    public function usersourcesListAction($context, $_format = 'json')
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

        $results = $qb->getQuery()->getResult();

        if ($_format === 'html') {
            $usersources = $this->get('serializer')->toArray($results, SideloadSerializationContext::createContext($this->container));
            $res         = $this->render('ApiBundle:ApiTokens:mobile-login-methods.html.twig', [
                'raw_usersources'      => $usersources,
                'external_usersources' => ListUtils::filter($usersources, function ($v) {
                    return $v['display_type'] === 'button';
                }),
                'social_usersources' => ListUtils::filter($usersources, function ($v) {
                    return $v['display_type'] === 'social';
                }),
            ]);
            $res->headers->set('Content-Type', 'text/html');

            return $res;
        }

        return new View($this->wrap($results));
    }

    /**
     * @ApiDoc(
     *     description="Login via usersource.",
     *     statusCodes={
     *         404="Usersource not found"
     *     },
     *     noOutput=true
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
     *     description="Returns api token on usersource callback",
     *     output="token",
     *     statusCodes={
     *         200="Created token"
     *     },
     *     noInput=true,
     *     output="string"
     * )
     *
     * @Rest\Get("/user_sources/{usersource}/callback/{format}", requirements={"format": "(ios|deskpro_scheme|default)"})
     * @Rest\Post("/user_sources/{usersource}/callback/{format}", requirements={"format": "(ios|deskpro_scheme|default)"})
     *
     * @param Request    $request
     * @param Usersource $usersource
     * @param string     $format
     *
     * @return Response
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

        $res = $this->render("ApiBundle::ApiTokens/$format.html.twig", [
            'token' => $this->createToken($person),
        ]);

        $res->headers->set('Content-Type', 'text/html');

        return $res;
    }

    /**
     * @ApiDoc(
     *     description="Sends magic link email",
     *     output="token",
     *     statusCodes={
     *         204="Created email"
     *     },
     *     input="DeskPRO\Bundle\AppBundle\Form\Type\ApiTokens\MagicLinkEmailType",
     *     output="string"
     * )
     *
     * @Rest\Post("/magic_link/email")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function magicLinkEmailAction(Request $request)
    {
        $form = $this->createForm(MagicLinkEmailType::class);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        $email  = $form->get('email')->getData();
        $target = $form->get('target')->getData();

        // generate tmp token
        $tmpData = new TmpData();
        $tmpData->setData('email', $email);
        $tmpData->setData('target', $target);
        $tmpData->setDateExpire(new \DateTime('+5 hours'));

        $this->getManager()->persist($tmpData);
        $this->getManager()->flush();

        // send email
        $message = $this->container->get('mailer')->createMessage();
        $message->setTemplate(
            'DeskPRO:emails_common:api-token-magic-email.html.twig',
            [
                'login_magic_link_url' => $this->get('router')->generate(
                    'portal_magic_link_login',
                    [
                        'authId' => $tmpData->getAuth(),
                    ],
                    UrlGeneratorInterface::ABSOLUTE_URL),
            ]
        );
        $message->setSubject($this->get('language_manager')->phrase('agent.emails.api_token_magic_link_subject', [
            'helpdesk_name' => $this->get('brand_stack')->getActive()->getSetting('core.deskpro_name'),
        ]));
        $message->setTo($email);
        $this->container->get('mailer')->send($message);

        return View::create(null, Response::HTTP_NO_CONTENT);
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
