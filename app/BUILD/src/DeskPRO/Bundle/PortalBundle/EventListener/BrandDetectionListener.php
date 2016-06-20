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
     * @var \DeskPRO\Bundle\PortalBundle\Brand\BrandStack
     */
    private $brand_stack;

    /**
     * @var \Application\DeskPRO\NewSettings\SettingsResolver
     */
    private $settings_resolver;

    /**
     * @var \Application\DeskPRO\EntityRepository\Brand
     */
    private $brand_repository;

    /**
     * @var \Application\DeskPRO\Entity\Brand
     */
    private $default_brand;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var PortalModeStorage
     */
    private $mode_storage;

    public function __construct(BrandStack $brand_stack, SettingsResolver $settings_resolver, Brand $brand_repository, BrandEntity $default_brand, PortalModeStorage $mode_storage, LoggerInterface $logger)
    {
        $this->brand_stack       = $brand_stack;
        $this->settings_resolver = $settings_resolver;
        $this->brand_repository  = $brand_repository;
        $this->default_brand     = $default_brand;
        $this->mode_storage      = $mode_storage;
        $this->logger            = $logger;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            // only run this on the master request - we only detect once per request.
            return;
        }

        $brand = null;

        if ($mode = $this->mode_storage->getMode()) {
            $brand = $this->detectBrandMode($mode);
        } else {
            $brand = $this->detectFromEsiQuery($event->getRequest());
        }

        if (!$brand) {
            $this->logger->info('Brand Detector: can\'t determine brand from request. falling back on default brand');
            $brand = $this->getDefaultBrand();
        }

        $this->brand_stack->push($brand);

        $this->logger->info('Brand Detector: initialized brand stack with brand id='.$brand->getId());
    }

    protected function detectFromEsiQuery(Request $request)
    {
        if (IsProxyRequestHelper::check($request)) {
            if ($brand_id = $request->query->getInt('brand_id')) {
                $this->logger->info(sprintf('found "%s" in esi brand_id query', $brand_id));

                return $this->brand_repository->find($brand_id);
            }
        }

        return;
    }

    protected function detectBrandMode(PortalMode $mode)
    {
        if ($mode->isBrand()) {
            try {
                return $this->brand_repository->find($mode->getData());
            } catch (\Exception $e) {
                return;
            }
        }
    }

    protected function getDefaultBrand()
    {
        return $this->default_brand;
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
