<?php

namespace DeskPRO\Bundle\PortalBundle\EventListener;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Translate\Translate;
use Application\EmailBundle\SwiftMailer\Mailer;
use Application\EmailBundle\SwiftMailer\MailerUtils;
use Application\LegacyApiBundle\HttpFoundation\JsonResponse;
use DeskPRO\Bundle\AppBundle\DataService\PersonDataService;
use DeskPRO\Bundle\AppBundle\EventListener\RedirectProtectionListener;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\EmailSender\PortalEmailSender;
use DeskPRO\Bundle\PortalBundle\Routing\PasswordResetException;
use DeskPRO\Bundle\SendmailBundle\Factory\AgentViewModelFactory;
use DeskPRO\Bundle\SendmailBundle\Factory\UserViewModelFactory;
use DeskPRO\Bundle\SendmailBundle\Sender\EmailSender;
use DpSys\Features;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class PasswordResetExceptionListener
 */
class PasswordResetExceptionListener implements EventSubscriberInterface
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var PersonDataService
     */
    private $personDataService;

    /**
     * @var PortalEmailSender
     */
    private $portalEmailSender;

    /**
     * @var BrandStack
     */
    private $brandStack;

    /**
     * @var Translate
     */
    private $translator;

    /**
     * @var Features
     */
    private $features;

    /**
     * @var UserViewModelFactory
     */
    private $userViewModel;

    /**
     * @var AgentViewModelFactory
     */
    private $agentViewModel;

    /**
     * @var EmailSender
     */
    private $emailSender;

    /**
     * @var Mailer
     */
    private $mailer;

    /**
     * @var MailerUtils
     */
    private $mailerUtils;

    /**
     * PasswordResetExceptionListener constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->router            = $container->get('router');
        $this->personDataService = $container->get('data.person');
        $this->portalEmailSender = $container->get('portal_email_sender');
        $this->brandStack        = $container->get('brand_stack');
        $this->translator        = $container->get('deskpro.core.translate');
        $this->features          = $container->get('deskpro.feature_flags');
        $this->userViewModel     = $container->get('email.user_viewmodel_factory');
        $this->agentViewModel    = $container->get('email.agent_viewmodel_factory');
        $this->emailSender       = $container->get('email.email_sender');
        $this->mailer            = $container->getMailer();
        $this->mailerUtils       = $container->get('mailer.utils');
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 129],
        ];
    }

    /**
     * @param GetResponseForExceptionEvent $event
     *
     * @throws \Exception
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if (!$exception instanceof PasswordResetException) {
            return;
        }

        /** @var Person $person */
        $person = $exception->getPerson();
        $email  = $event->getRequest()->get('email', $person->getPrimaryEmailAddress());

        $resetPasswordPrefix = $person->isAgent()
            ? 'reset-password:'.DP_INTERFACE.':'.$person->getId()
            : 'reset-password-';

        if ($this->personDataService->isPasswordResetReSendExpired($person, $resetPasswordPrefix)) {
            $expire = $person->isAgent()
                ? null
                : $this->brandStack->getActive()->getSetting('user.password_reset_code_time_limit', 18000);
            $reset = $this->personDataService->createPasswordReset($person, $expire, $resetPasswordPrefix);

            if ($person->isAgent() || $person->isAdmin()) {
                $this->translator->setDefaultPersonContext($person);

                if (!defined('DPC_IS_CLOUD') && $person->can_admin && $person->is_agent && !$person->is_deleted) {
                    $vars = [
                        'person' => $person,
                        'email'  => $email,
                    ];

                    if ($this->features->hasBeta('email_templates')) {
                        $viewModel = $this->agentViewModel->createAdminNoResetPasswordModel();

                        $this->emailSender->send($viewModel, ['to' => $email]);
                    } else {
                        $message = $this->mailer->createMessage();
                        $message->setTemplate('DeskPRO:emails_agent:admin-noreset-password.html.twig', $vars);
                        $message->setTo($email, $person->getDisplayName());

                        $this->mailer->send($message);
                    }
                } else {
                    if ($this->features->hasBeta('email_templates')) {
                        $resetUrl = $this->router->generate(
                            'agent_login',
                            ['reset_code' => $reset['code'], 'brand' => $person->getBrands()->first()],
                            UrlGeneratorInterface::ABSOLUTE_URL
                        );
                        $viewModel = $this->userViewModel->createResetPasswordModel($resetUrl);
                        $this->mailerUtils->sendModelWithPersonContext(
                            $person,
                            $viewModel,
                            ['to' => $person]
                        );
                    } else {
                        $vars = [
                            'code'   => $reset['code'],
                            'person' => $person,
                        ];
                        $message = $this->mailer->createMessage();
                        $message->setTemplate('DeskPRO:emails_user:reset-password.html.twig', $vars);
                        $message->setTo($email, $person->getDisplayName());

                        $this->translator->setDefaultPersonContext($person);
                        $this->mailerUtils->sendWithPersonContext($message, $person);
                    }
                }

                $response = $this->getResponseForAgentInterface($email);
            } else {
                $this->portalEmailSender->sendPasswordSetLink($person, $reset);

                $response = $this->getResponseForUserInterface($event->getRequest(), $person->getPrimaryEmail()->getId());
            }
        } else {
            // Reset email is already sent, just redirect.
            $response = $this->getResponse($event->getRequest(), $email, $person->getPrimaryEmail()->getId());
        }

        $response->headers->set('X-DeskPRO-RedirectReason', $exception->getMessage());
        $response->headers->set('X-Status-Code', $response->getStatusCode());
        $response->headers->set(RedirectProtectionListener::ALLOW_REDIRECT_OFFSITE_HEADER, true);

        if ($event->getRequest()->isXmlHttpRequest()) {
            $response->headers->set('Content-Type', 'application/json');
        }

        $event->setResponse($response);
    }

    /**
     * @param string $email
     *
     * @return RedirectResponse
     */
    private function getResponseForAgentInterface($email)
    {
        return new RedirectResponse(
            $this->router->generate(
                'agent_login',
                ['reset_send_to' => $email],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
            Response::HTTP_FOUND
        );
    }

    /**
     * @param Request $request
     * @param string  $emailId
     *
     * @return JsonResponse|RedirectResponse
     */
    private function getResponseForUserInterface(Request $request, $emailId)
    {
        $redirectUrl = $this->router->generate(
            'portal_set_password_sent',
            ['emailId' => $emailId],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $request->isXmlHttpRequest()
            ? new JsonResponse([
                'success'  => true,
                'redirect' => $redirectUrl,
            ], Response::HTTP_OK)
            : new RedirectResponse($redirectUrl, Response::HTTP_FOUND);
    }

    private function getResponse(Request $request, $email, $emailId = null)
    {
        return DP_INTERFACE === 'user'
            ? $this->getResponseForUserInterface($request, $emailId)
            : $this->getResponseForAgentInterface($email);
    }
}
