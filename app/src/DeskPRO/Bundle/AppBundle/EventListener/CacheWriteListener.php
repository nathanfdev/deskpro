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

namespace DeskPRO\Bundle\AppBundle\EventListener;

use DeskPRO\Bundle\AppBundle\Cache\Resolver\ResolverInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class CacheWriteListener.
 *
 * @todo should think about how it will interact with ApiLogListener with x-deskpro-client-request-id headers and resend mode
 */
class CacheWriteListener implements EventSubscriberInterface
{
    protected $resolver;

    public function __construct(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    const X_DP_CACHE_STORE_HEADER = 'X-DeskPRO-Cache-Store';
    const X_DP_CACHE_HEADER       = 'X-DeskPRO-Cache';

    /**
     *
     */
    public static function getSubscribedEvents()
    {
        return [KernelEvents::RESPONSE => ['onResponse', 1024]]; // should be called before request id was set
    }

    public function onResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();

        if ($response->headers->has(self::X_DP_CACHE_STORE_HEADER)) {
            $etag = $response->headers->get(self::X_DP_CACHE_STORE_HEADER);
            $response->headers->remove(self::X_DP_CACHE_STORE_HEADER);
            $response->setEtag($etag);
            $response->headers->set(self::X_DP_CACHE_HEADER, 'store');
            $this->resolver->write($event->getRequest(), $response);
        } elseif ($response->headers->has(self::X_DP_CACHE_HEADER)) {
            $response->headers->set(self::X_DP_CACHE_HEADER, 'hit');
        }
    }
}
