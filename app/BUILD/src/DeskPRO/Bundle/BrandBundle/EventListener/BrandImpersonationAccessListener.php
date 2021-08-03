<?php

namespace DeskPRO\Bundle\BrandBundle\EventListener;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class BrandImpersonationAccessListener.
 */
class BrandImpersonationAccessListener implements EventSubscriberInterface
{
    /**
     * @var \DeskPRO\Bundle\BrandBundle\Brand\BrandStack
     */
    private $brandStack;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * Constructor.
     *
     * @param \DeskPRO\Bundle\BrandBundle\Brand\BrandStack $brandStack
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(BrandStack $brandStack, TokenStorageInterface $tokenStorage)
    {
        $this->brandStack     = $brandStack;
        $this->tokenStorage   = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', -5],
        ];
    }

    /**
     * @param GetResponseEvent $event
     *
     * @internal
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $impersonationMode = $event->getRequest()->getSession() && $event->getRequest()->getSession()->get('is_impersonating', false);

        if (!$impersonationMode) {
            return;
        }

        $brand = $this->brandStack->getActive()->getBrand();
        $token = $this->tokenStorage->getToken();

        if (!$token || !$brand) {
            return;
        }

        $person = $token->getUser();
        if (!$person instanceof Person || !$person->getId()) {
            return;
        }

        if (!$person->isAdmin() && !$person->hasBrand($brand)) {

            //if user is being impersonated, check if user has access to other brand and redirect user to the brand's portal home
            if (!$person->getBrands()->isEmpty()) {
                $brand = $person->getBrands()->first();
                $this->brandStack->push($brand);

                //TODO: Better way to generate url for custom brand
                $event->setResponse(new RedirectResponse('/b/'.$brand->getSlug()));
            } else {
                // user doesn't have access to brand unset authorised token
                $this->tokenStorage->setToken(new AnonymousToken('anon.', 'anon.'));
            }
        }
    }
}
