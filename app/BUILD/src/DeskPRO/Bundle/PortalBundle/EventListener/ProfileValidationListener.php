<?php

namespace DeskPRO\Bundle\PortalBundle\EventListener;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Form\Error\FormValidatorChecker;
use DeskPRO\Bundle\AppBundle\Request\RequestUtils;
use DeskPRO\Bundle\AppBundle\Security\Http\HttpUtils;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\Form\Form\Type\PersonEditProfileType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class ProfileValidationListener.
 */
class ProfileValidationListener implements EventSubscriberInterface
{
    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var BrandStack
     */
    private $brandStack;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var HttpUtils
     */
    private $httpUtils;

    /**
     * Constructor.
     *
     * @param FormFactory           $formFactory
     * @param BrandStack            $brandStack
     * @param TokenStorageInterface $tokenStorage
     * @param HttpUtils             $httpUtils
     */
    public function __construct(FormFactory $formFactory, BrandStack $brandStack, TokenStorageInterface $tokenStorage, HttpUtils $httpUtils)
    {
        $this->formFactory  = $formFactory;
        $this->brandStack   = $brandStack;
        $this->tokenStorage = $tokenStorage;
        $this->httpUtils    = $httpUtils;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 0],
        ];
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if (
            !$event->isMasterRequest()
            || $request->isXmlHttpRequest()
            || RequestUtils::isPortalApi($request)
            || RequestUtils::isLowRequest($request)
            || RequestUtils::isProxyRequest($request)
        ) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return;
        }

        $user = $token->getUser();
        if (!$user instanceof Person) {
            return;
        }

        // check that all user fields are set correctly
        // otherwise we should redirect user to the profile page
        $profileForm = $this->formFactory->create(PersonEditProfileType::class, $user, [
            'settings'                      => $this->brandStack->getActive()->getSettings(),
            'csrf_protection'               => false,
            'csrf_double_submit_protection' => false,
        ]);

        FormValidatorChecker::submitForm($profileForm);
        if (!$profileForm->isValid() && $request->attributes->get('_route') !== 'portal_user_profile') {
            $event->setResponse($this->httpUtils->createRedirectResponse($request, 'portal_user_profile'));
        }
    }
}
