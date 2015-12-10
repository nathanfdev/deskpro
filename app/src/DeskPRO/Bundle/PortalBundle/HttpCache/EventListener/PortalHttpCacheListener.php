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
namespace DeskPRO\Bundle\PortalBundle\HttpCache\EventListener;

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ContentAbstract;
use Application\DeskPRO\Entity\Download;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\HttpCache\PortalCacheHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class PortalHttpCacheListener implements EventSubscriberInterface
{
    /**
     * @var \SplObjectStorage
     */
    protected $lastModifiedDates;

    /**
     * @var \SplObjectStorage
     */
    protected $etags;

    /**
     * @var BrandStack
     */
    protected $brand_stack;

    /**
     * @var PortalCacheHelper
     */
    private $cache_helper;

    public function __construct(BrandStack $brand_stack, PortalCacheHelper $cache_helper)
    {
        $this->lastModifiedDates = new \SplObjectStorage();
        $this->etags             = new \SplObjectStorage();
        $this->brand_stack       = $brand_stack;
        $this->cache_helper      = $cache_helper;
    }

    /**
     * Handles HTTP validation headers.
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $request = $event->getRequest();

        $page_cache_config = $request->attributes->get('_portal_page_cache');
        $tag_cache_config  = $request->attributes->get('_portal_tag_cache');

        if (!$tag_cache_config && !$page_cache_config) {
            return;
        }

        /** @var \DeskPRO\Bundle\PortalBundle\HttpCache\Configuration\PortalHttpCache $config */
        $config = $page_cache_config ?: $tag_cache_config;

        $content = null;
        if ($content_name = $config->getContent()) {
            if (!$content = $request->attributes->get($content_name)) {
                throw new \InvalidArgumentException('could not find content for PortalHttpCacheListener (request attribute name = "'.$content_name.'" does not exist)');
            }
        }

        $response = new Response();

        $use_last_modified = $this->getBrandSetting('portal.http_cache_last_modified');
        $use_etags         = $this->getBrandSetting('portal.http_cache_etags');

        $lastModifiedDate = '';
        if ($use_last_modified && $content) {
            $lastModifiedDate = $this->generateLastModified($content);
            $response->setLastModified($lastModifiedDate);
        }

        $etag = '';
        if ($use_etags && $content) {
            $etag = $this->generateEtag($content);
            $response->setETag($etag);
        }

        if ($response->isNotModified($request)) {
            $event->setController(function () use ($response) {
                return $response;
            });
        } else {
            if ($use_etags && $etag) {
                $this->etags[$request] = $etag;
            }
            if ($use_last_modified && $lastModifiedDate) {
                $this->lastModifiedDates[$request] = $lastModifiedDate;
            }
        }
    }

    /**
     * Modifies the response to apply HTTP cache headers when needed.
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request  = $event->getRequest();
        $response = $event->getResponse();

        if (!$request->isMethodSafe()) {
            $response->setPrivate();

            return;
        }

        $page_cache_config = $request->attributes->get('_portal_page_cache');
        $tag_cache_config  = $request->attributes->get('_portal_tag_cache');

        if (!$tag_cache_config && !$page_cache_config) {
            return;
        }

        /** @var \DeskPRO\Bundle\PortalBundle\HttpCache\Configuration\PortalHttpCache $config */
        $config = $page_cache_config ?: $tag_cache_config;

        // http://tools.ietf.org/html/draft-ietf-httpbis-p4-conditional-12#section-3.1
        if (!in_array($response->getStatusCode(), array(200, 203, 300, 301, 302, 304, 404, 410))) {
            return;
        }

        // get smaxage from settings
        $is_guest = $this->cache_helper->isGuestRequest();
        if ($page_cache_config) {
            if ($is_guest) {
                $smaxage = (int) $this->getBrandSetting('portal.smaxage_guest_page');
            } else {
                $smaxage = (int) $this->getBrandSetting('portal.smaxage_user_page');
            }
        } else {
            if ($is_guest) {
                $smaxage = (int) $this->getBrandSetting('portal.smaxage_guest_tag');
            } else {
                $smaxage = (int) $this->getBrandSetting('portal.smaxage_user_tag');
            }
        }

        if ($smaxage > 0) {
            $response->setSharedMaxAge($smaxage);
            if ($this->cache_helper->isGuestRequest()) {
                // we never want to send cookies in this situation, because this page is about to be cached
                // for a user without a session
                foreach ($response->headers->getCookies() as $cookie) {
                    /* @var \Symfony\Component\HttpFoundation\Cookie $cookie */
                    $response->headers->removeCookie($cookie->getName());
                }
            }
        }

        if (isset($this->lastModifiedDates[$request])) {
            $response->setLastModified($this->lastModifiedDates[$request]);

            unset($this->lastModifiedDates[$request]);
        }

        if (isset($this->etags[$request])) {
            $response->setETag($this->etags[$request]);

            unset($this->etags[$request]);
        }

        // cache ajax requests differently (sometimes we return different response for the same url in these cases)
        $response->setVary('X-Requested-With', false);

        $event->setResponse($response);
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => 'onKernelController',
            KernelEvents::RESPONSE   => 'onKernelResponse',
        );
    }

    protected function getBrandSetting($setting, $default = null)
    {
        return $this->brand_stack->getActive()->getSetting($setting, $default);
    }

    /**
     * @param ContentAbstract $content
     *
     * @return \DateTime
     */
    protected function generateLastModified(ContentAbstract $content)
    {
        if ($content instanceof Article || $content instanceof Download) {
            return $content->getDateUpdated() ?: $content->getDateCreated();
        } else {
            return $content->getDatePublished() ?: $content->getDateCreated();
        }
    }

    /**
     * @param ContentAbstract $content
     *
     * @return string
     */
    protected function generateEtag(ContentAbstract $content)
    {
        $global_timestamp = $this->getBrandSetting('portal.global_cache_timestamp');
        $type             = $content->getContentType();
        $id               = $content->getId();
        $last_modified    = $this->generateLastModified($content);

        $etag = hash('sha256', $global_timestamp.$type.$id.$last_modified->getTimestamp());

        return $etag;
    }
}
