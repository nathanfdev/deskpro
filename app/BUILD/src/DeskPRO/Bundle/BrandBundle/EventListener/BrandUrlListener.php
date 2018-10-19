<?php

namespace DeskPRO\Bundle\BrandBundle\EventListener;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Helper\IsProxyRequestHelper;
use DeskPRO\Bundle\AppBundle\HttpKernel\SkipLowRequestInterface;
use DeskPRO\Bundle\AppBundle\Request\OriginalUrlGenerator;
use DeskPRO\Bundle\AppBundle\Request\RequestUtils;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
use DeskPRO\Bundle\BrandBundle\Request\OriginalRequestStorage;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class BrandUrlListener.
 */
class BrandUrlListener implements EventSubscriberInterface, SkipLowRequestInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var BrandStack
     */
    private $brandStack;

    /**
     * @var OriginalUrlGenerator
     */
    private $urlGenerator;

    /**
     * @var OriginalRequestStorage
     */
    private $originalRequestStorage;

    /**
     * Constructor.
     *
     * @param EntityManager          $em
     * @param TokenStorageInterface  $tokenStorage
     * @param BrandStack             $brandStack
     * @param OriginalUrlGenerator   $urlGenerator
     * @param OriginalRequestStorage $originalRequestStorage
     */
    public function __construct(
        EntityManager          $em,
        TokenStorageInterface  $tokenStorage,
        BrandStack             $brandStack,
        OriginalUrlGenerator   $urlGenerator,
        OriginalRequestStorage $originalRequestStorage
    ) {
        $this->em                     = $em;
        $this->tokenStorage           = $tokenStorage;
        $this->brandStack             = $brandStack;
        $this->urlGenerator           = $urlGenerator;
        $this->originalRequestStorage = $originalRequestStorage;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest'],
        ];
    }

    /**
     * @internal
     *
     * @param GetResponseEvent $event
     *
     * @throws NotFoundHttpException
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()
            || $event->getRequest()->isXmlHttpRequest()
            || IsProxyRequestHelper::check($event->getRequest())
            || RequestUtils::isLowRequest($event->getRequest())) {
            return;
        }

        $request = $event->getRequest();
        if (!$request->attributes->has('_dp_brand_slug')) {
            return;
        }

        $brand = $this->em->getRepository(Brand::class)->findOneBy([
            'slug' => $request->attributes->get('_dp_brand_slug'),
        ]);

        if (!$brand) {
            return;
        }

        if (!$this->originalRequestStorage->getOriginalRequest()) {
            return;
        }

        // we detected a brand in slug mode but it has a url
        if ($brand->getUrl()) {
            $token = $this->tokenStorage->getToken();
            $user  = $token ? $token->getUser() : null;

            // just allow to see brand portal by slug for admin
            if (!$user instanceof Person || !$user->isAdmin()) {
                $url = $this->urlGenerator->generate($this->originalRequestStorage->getOriginalRequest(), 'portal_home', [
                    'brand' => $this->brandStack->getDefaultBrand(),
                ]);

                $event->setResponse(new RedirectResponse($url));
            }
        }
    }
}
