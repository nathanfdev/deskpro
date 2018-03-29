<?php

namespace DeskPRO\Bundle\AppBundle\EventListener;

use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\Cache\Resolver\ResolverInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class CacheWriteListener.
 *
 * @todo should think about how it will interact with ApiLogListener with x-deskpro-client-request-id headers and resend mode
 */
class CacheWriteListener implements EventSubscriberInterface
{
    /**
     * @var ResolverInterface
     */
    protected $resolver;

    /**
     * @var SettingsResolver
     */
    protected $settings_resolver;

    /**
     * @param ResolverInterface $resolver
     * @param SettingsResolver  $settings_resolver
     */
    public function __construct(ResolverInterface $resolver, SettingsResolver $settings_resolver)
    {
        $this->resolver          = $resolver;
        $this->settings_resolver = $settings_resolver;
    }

    const X_DP_CACHE_STORE_HEADER = 'X-DeskPRO-Cache-Store';
    const X_DP_CACHE_HEADER       = 'X-DeskPRO-Cache';

    public static function getSubscribedEvents()
    {
        return [KernelEvents::RESPONSE => ['onResponse', 1024]]; // should be called before request id was set
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onResponse(FilterResponseEvent $event)
    {
        if ($this->isEnabled()) {
            $this->processResponse($event->getRequest(), $event->getResponse());
        } else {
            $this->cleanHeaders($event->getResponse());
        }
    }

    /**
     * @param Request  $request
     * @param Response $response
     */
    protected function processResponse(Request $request, Response $response)
    {
        if ($response->headers->has(self::X_DP_CACHE_STORE_HEADER)) {
            $etag = $response->headers->get(self::X_DP_CACHE_STORE_HEADER);
            $response->setEtag($etag);
            $response->headers->set(self::X_DP_CACHE_HEADER, 'store');
            $this->cleanHeaders($response); // headers MUST be cleaned before response is stored
            $this->resolver->write($request, $response);
        } elseif ($response->headers->has(self::X_DP_CACHE_HEADER)) {
            $response->headers->set(self::X_DP_CACHE_HEADER, 'hit');
            $this->cleanHeaders($response);
        }
    }

    /**
     * @param Response $response
     */
    protected function cleanHeaders(Response $response)
    {
        $response->headers->remove(self::X_DP_CACHE_STORE_HEADER);
        $response->headers->remove('x-content-digest');
    }

    protected function isEnabled()
    {
        return $this->settings_resolver->getGlobalSettings()->get('response.cache.enabled', false);
    }
}
