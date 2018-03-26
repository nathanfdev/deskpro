<?php

namespace DeskPRO\Bundle\AppBundle\EventListener;

use DeskPRO\Component\Util\ListUtils;
use DeskPRO\Component\Util\MapUtils;
use Symfony\Component\DependencyInjection\IntrospectableContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class SecurityHeadersResponseListener.
 */
class SecurityHeadersResponseListener implements EventSubscriberInterface
{
    /**
     * @var IntrospectableContainerInterface
     */
    protected $container;

    public function __construct(IntrospectableContainerInterface $container)
    {
        $this->container = $container;
    }
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => ['onResponse', 64],
        ];
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onResponse(FilterResponseEvent $event)
    {
        if ($this->container->initialized('deskpro.core.settings')) {
            if ($this->container->get('deskpro.core.settings')->get('core.disable_csp_headers')) {
                return;
            }
        }

        $response = $event->getResponse();
        $response->headers->add(['X-Content-Type-Options' => 'nosniff']);

        $request = $event->getRequest();
        $path    = $request->getPathInfo();

        $csp = [
            'default-src' => ['self', 'blob:'],
            'script-src'  => ['*', 'data:', 'unsafe-inline', 'unsafe-eval'],
            'style-src'   => ['*', 'data:', 'unsafe-inline'],
            'img-src'     => ['*', 'data:', 'blob:'],
            'font-src'    => ['*', 'data:'],
            'connect-src' => '*',
            'media-src'   => ['*', 'data:', 'blob:'],
            'object-src'  => '*',
            'child-src'   => ['*', 'blob:'],
            'form-action' => '*',
            'frame-src'   => ['*'],
        ];

        $referrerPolicy = 'no-referrer-when-downgrade';

        if (strpos($path, '/frame-embed') === 0 || strpos($path, '/focus-win') === 0) {
            // these portal modes can be framed,
            // so no X-Frame-Options header and use wildcard frame frame-ancestors
            $csp['frame-ancestors'] = '*';
        } else {
            $response->headers->add(['X-Frame-Options' => 'sameorigin']);
            $csp['frame-ancestors'] = 'self';
        }

        // Lock down agent/admin a bit
        if (strpos($path, '/agent') || strpos($path, '/admin')) {
            $csp['form-action'] = 'self';
            $referrerPolicy     = 'no-referrer';
        }

        $response->headers->add(['Content-Security-Policy' => $this->buildCspString($csp)]);
        $response->headers->add(['Referrer-Policy' => $referrerPolicy]);
    }

    /**
     * @see https://en.wikipedia.org/wiki/Content_Security_Policy
     *
     * @param array $options
     *
     * @return string
     */
    private function buildCspString(array $options)
    {
        $parts = MapUtils::mapToList($options, function ($k, $v) {
            if ($v === true) {
                return $k;
            }
            if (!is_array($v)) {
                $v = [$v];
            }
            $val = ListUtils::filterOutFalsey(array_map(function ($v) {
                switch ($v) {
                    case 'self':
                    case 'unsafe-inline':
                    case 'unsafe-eval':
                    case 'none':
                        return "'$v'";
                        break;
                    default:
                        return $v;
                }
            }, $v));

            if ($val) {
                return $k.' '.implode(' ', $val);
            } else {
                return $k;
            }
        });

        return implode('; ', $parts);
    }
}
