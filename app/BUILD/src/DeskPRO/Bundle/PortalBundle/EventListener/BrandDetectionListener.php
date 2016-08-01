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

namespace DeskPRO\Bundle\PortalBundle\EventListener;

use Application\DeskPRO\Entity\Brand as BrandEntity;
use Application\DeskPRO\EntityRepository\Brand;
use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\Helper\IsProxyRequestHelper;
use DeskPRO\Bundle\AppBundle\Helper\UrlHostChecker;
use DeskPRO\Bundle\AppBundle\HttpKernel\SkipLowRequestInterface;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\Mode\PortalMode;
use DeskPRO\Bundle\PortalBundle\Mode\PortalModeStorage;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Early in a request, this listener will determine the active brand for this request and push it onto the brand_stack
 * service.
 */
class BrandDetectionListener implements EventSubscriberInterface, SkipLowRequestInterface
{
    /**
     * @var BrandStack
     */
    private $brandStack;

    /**
     * @var SettingsResolver
     */
    private $settingsResolver;

    /**
     * @var \Application\DeskPRO\EntityRepository\Brand
     */
    private $brandRepository;

    /**
     * @var \Application\DeskPRO\Entity\Brand
     */
    private $defaultBrand;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var UrlHostChecker
     */
    private $urlHostChecker;

    /**
     * @var PortalModeStorage
     */
    private $modeStorage;

    public function __construct(
        BrandStack $brandStack,
        SettingsResolver $settingsResolver,
        Brand $brandRepository,
        BrandEntity $defaultBrand,
        PortalModeStorage $modeStorage,
        LoggerInterface $logger,
        UrlHostChecker $urlHostChecker
    ) {
        $this->brandStack       = $brandStack;
        $this->settingsResolver = $settingsResolver;
        $this->brandRepository  = $brandRepository;
        $this->defaultBrand     = $defaultBrand;
        $this->modeStorage      = $modeStorage;
        $this->logger           = $logger;
        $this->urlHostChecker   = $urlHostChecker;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $brand = null;
        if ($event->isMasterRequest()) {
            if ($mode = $this->modeStorage->getMode()) {
                $brand = $this->detectBrandMode($mode);
            } else {
                $brand = $this->detectFromEsiQuery($event->getRequest());
            }
        }
        if ($brand === null) {
            $brand = $this->detectBrandByHost($event);
        }
        if ($brand !== null) {
            if (!$brand) {
                $this->logger->info('Brand Detector: can\'t determine brand from request. falling back on default brand');
                $brand = $this->getDefaultBrand();
            }
            $this->brandStack->push($brand);

            $this->logger->info('Brand Detector: initialized brand stack with brand id='.$brand->getId());
        }
    }

    /**
     * @param Request $request
     *
     * @return null|Brand|void
     */
    protected function detectFromEsiQuery(Request $request)
    {
        if (IsProxyRequestHelper::check($request)) {
            if ($brand_id = $request->query->getInt('brand_id')) {
                $this->logger->info(sprintf('found "%s" in esi brand_id query', $brand_id));

                return $this->brandRepository->find($brand_id);
            }
        }

        return;
    }

    /**
     * @param PortalMode $mode
     *
     * @return null|Brand|void
     */
    protected function detectBrandMode(PortalMode $mode)
    {
        if ($mode->isBrand() || $mode->isAdminPreview()) {
            try {
                return $this->brandRepository->find($mode->getData());
            } catch (\Exception $e) {
                return;
            }
        }
    }

    /**
     * @param GetResponseEvent $event
     *
     * @return null|Brand
     */
    protected function detectBrandByHost(GetResponseEvent $event)
    {
        $host  = $event->getRequest()->getHttpHost();
        $brand = $this->brandRepository->findOneBy(['url' => $this->urlHostChecker->simplifyUrl($host)]);

        return $brand;
    }

    protected function getDefaultBrand()
    {
        return $this->defaultBrand;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            // high priority, must be called BEFORE RouterListener
            KernelEvents::REQUEST => ['onKernelRequest', 34],
        ];
    }
}
