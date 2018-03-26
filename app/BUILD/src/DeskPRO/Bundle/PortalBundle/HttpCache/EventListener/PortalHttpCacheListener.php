<?php

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
    protected $brandStack;

    /**
     * @var PortalCacheHelper
     */
    private $cacheHelper;

    public function __construct(BrandStack $brandStack, PortalCacheHelper $cacheHelper)
    {
        $this->lastModifiedDates = new \SplObjectStorage();
        $this->etags             = new \SplObjectStorage();
        $this->brandStack        = $brandStack;
        $this->cacheHelper       = $cacheHelper;
    }

    /**
     * Handles HTTP validation headers.
     *
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $request = $event->getRequest();

        $pageCacheConfig = $request->attributes->get('_portal_page_cache');
        $tagCacheConfig  = $request->attributes->get('_portal_tag_cache');

        if (!$tagCacheConfig && !$pageCacheConfig) {
            return;
        }

        /** @var \DeskPRO\Bundle\PortalBundle\HttpCache\Configuration\PortalHttpCache $config */
        $config = $pageCacheConfig ?: $tagCacheConfig;

        $content = null;
        if ($content_name = $config->getContent()) {
            if (!$content = $request->attributes->get($content_name)) {
                throw new \InvalidArgumentException('could not find content for PortalHttpCacheListener (request attribute name = "'.$content_name.'" does not exist)');
            }
        }

        $response = new Response();

        $useLastModified = $this->getBrandSetting('portal.http_cache_last_modified');
        $useEtags        = $this->getBrandSetting('portal.http_cache_etags');

        $lastModifiedDate = '';
        if ($useLastModified && $content) {
            $lastModifiedDate = $this->generateLastModified($content);
            $response->setLastModified($lastModifiedDate);
        }

        $eTag = '';
        if ($useEtags && $content) {
            $eTag = $this->generateEtag($content);
            $response->setEtag($eTag);
        }

        if ($response->isNotModified($request)) {
            $event->setController(function () use ($response) {
                return $response;
            });
        } else {
            if ($useEtags && $eTag) {
                $this->etags[$request] = $eTag;
            }
            if ($useLastModified && $lastModifiedDate) {
                $this->lastModifiedDates[$request] = $lastModifiedDate;
            }
        }
    }

    /**
     * Modifies the response to apply HTTP cache headers when needed.
     *
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request  = $event->getRequest();
        $response = $event->getResponse();

        if (!$request->isMethodSafe()) {
            $response->setPrivate();

            return;
        }

        $pageCacheConfig = $request->attributes->get('_portal_page_cache');
        $tagCacheConfig  = $request->attributes->get('_portal_tag_cache');

        if (!$tagCacheConfig && !$pageCacheConfig) {
            return;
        }

        // http://tools.ietf.org/html/draft-ietf-httpbis-p4-conditional-12#section-3.1
        if (!in_array($response->getStatusCode(), [200, 203, 300, 301, 302, 304, 404, 410])) {
            return;
        }

        // get smaxage from settings
        $isGuest = $this->cacheHelper->isGuestRequest();
        if ($pageCacheConfig) {
            if ($isGuest) {
                $sMaxAge = (int) $this->getBrandSetting('portal.smaxage_guest_page');
            } else {
                $sMaxAge = (int) $this->getBrandSetting('portal.smaxage_user_page');
            }
        } else {
            if ($isGuest) {
                $sMaxAge = (int) $this->getBrandSetting('portal.smaxage_guest_tag');
            } else {
                $sMaxAge = (int) $this->getBrandSetting('portal.smaxage_user_tag');
            }
        }

        if ($sMaxAge > 0) {
            $response->setSharedMaxAge($sMaxAge);
            if ($this->cacheHelper->isGuestRequest()) {
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
            $response->setEtag($this->etags[$request]);

            unset($this->etags[$request]);
        }

        // cache ajax requests differently (sometimes we return different response for the same url in these cases)
        $response->setVary('X-Requested-With,X-User-Context-Hash', false);

        $event->setResponse($response);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
            KernelEvents::RESPONSE   => 'onKernelResponse',
        ];
    }

    protected function getBrandSetting($setting, $default = null)
    {
        return $this->brandStack->getActive()->getSetting($setting, $default);
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
        $globalTimestamp = $this->getBrandSetting('portal.global_cache_timestamp');
        $type            = $content->getContentType();
        $id              = $content->getId();
        $lastModified    = $this->generateLastModified($content);

        $eTag = hash('sha256', $globalTimestamp.$type.$id.$lastModified->getTimestamp());

        return $eTag;
    }
}
