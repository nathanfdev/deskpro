<?php

namespace DeskPRO\Bundle\AppBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class ReferrerPolicyListener.
 */
class ReferrerPolicyListener implements EventSubscriberInterface
{
    /**
     * @var string[]
     */
    private $whitelistedRoutes = [
        'portal_home',
        'portal_kb',
        'portal_kb_browse',
        'portal_kb_view',
        'portal_community',
        'portal_community_browse',
        'portal_community_topic_view',
        'portal_downloads',
        'portal_downloads_browse',
        'portal_downloads_view',
        'portal_guides',
        'user_guides',
        'portal_guides_topic_view_short',
        'portal_guides_topic_view',
        'portal_guides_topic_permalink',
        'portal_news',
        'portal_news_browse',
        'portal_news_view',
    ];

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => ['onResponse'],
        ];
    }

    /**
     * @internal
     *
     * @param FilterResponseEvent $event
     */
    public function onResponse(FilterResponseEvent $event)
    {
        $request  = $event->getRequest();
        $response = $event->getResponse();

        $route = $request->attributes->get('_route');
        if (in_array($route, $this->whitelistedRoutes)) {
            $referrerPolicy = 'no-referrer-when-downgrade';
        } else {
            $referrerPolicy = 'no-referrer';
        }

        $response->headers->add(['Referrer-Policy' => $referrerPolicy]);
    }
}
