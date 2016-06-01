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

namespace DeskPRO\Bundle\AppBundle\EventListener;

use DeskPRO\Component\Util\ListUtils;
use DeskPRO\Component\Util\MapUtils;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SecurityHeadersResponseListener implements EventSubscriberInterface
{
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
        $response = $event->getResponse();
        $response->headers->add(['X-Content-Type-Options' => 'nosniff']);

        $request = $event->getRequest();
        $path    = $request->getPathInfo();

        $csp = [
            'default-src' => 'self',
            'script-src'  => ['*', 'unsafe-inline', 'unsafe-eval'],
            'style-src'   => ['*', 'unsafe-inline'],
            'img-src'     => ['*', 'data:'],
            'font-src'    => ['*', 'data:'],
            'connect-src' => '*',
            'media-src'   => '*',
            'object-src'  => '*',
            'child-src'   => '*',
            'form-action' => '*',
            'referrer'    => 'no-referrer-when-downgrade',
        ];

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
            $csp['child-src']   = 'self';
            $csp['referrer']    = 'no-referrer';
        }

        if ($request->isSecure()) {
            $csp['upgrade-insecure-requests'] = true;
            $csp['block-all-mixed-content']   = true;
        }

        $response->headers->add(['Content-Security-Policy' => $this->buildCspString($csp)]);
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
