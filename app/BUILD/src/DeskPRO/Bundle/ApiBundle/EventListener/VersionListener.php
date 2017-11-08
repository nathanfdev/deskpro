<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ApiBundle\EventListener;

use DeskPRO\Bundle\ApiBundle\Request\ApiVersionInfo;
use DeskPRO\Bundle\AppBundle\Routing\RequestMatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class VersionListener.
 */
class VersionListener implements EventSubscriberInterface
{
    /**
     * @var ApiVersionInfo
     */
    private $versionInfo;

    /**
     * @var RequestMatcher
     */
    private $requestMatcher;

    /**
     * Constructor.
     *
     * @param ApiVersionInfo $versionInfo
     * @param RequestMatcher $requestMatcher
     */
    public function __construct(ApiVersionInfo $versionInfo, RequestMatcher $requestMatcher)
    {
        $this->versionInfo    = $versionInfo;
        $this->requestMatcher = $requestMatcher;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            // call before RouterListener
            KernelEvents::REQUEST  => ['onRequest', 40],
            KernelEvents::RESPONSE => ['onResponse', 40],
        ];
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        // get version number from the request
        if (preg_match('#^/api/v2/(\d+)#', $request->getPathInfo(), $matches)) {
            $currentVersion = $this->versionInfo->getClosestVersion($matches[1]);
        } else {
            $currentVersion = $this->versionInfo->getDefaultVersion();
        }

        // register version num from the request
        $request->attributes->set('version', $currentVersion);

        // iterate versions to get proper controller
        $availableVersions = $this->getCheckVersions($currentVersion);

        $pathInfo = $request->getPathInfo();
        $pathInfo = preg_replace('#^/api/v2#', '', $pathInfo);
        $pathInfo = preg_replace('#^/\d+#', '', $pathInfo);
        $pathInfo = ltrim($pathInfo, '/');

        $matchedVersion = null;
        $versionUrl     = null;
        foreach ($availableVersions as $version) {
            $versionUrl = '/api/v2/'.($version ? ($version.'/') : '').$pathInfo;
            if ($this->requestMatcher->routeExists($versionUrl, $request->getMethod())) {
                // stop on first match
                // get latest action
                $matchedVersion = $version;
                break;
            }
        }

        // if matched controller version is differ than requested one
        // e.g. we have controller action just for the previous version
        // then update request url as well
        if ($matchedVersion !== null && $currentVersion !== $matchedVersion) {
            $reflection = new \ReflectionClass(Request::class);
            $property   = $reflection->getProperty('pathInfo');
            $property->setAccessible(true);
            $property->setValue($request, $versionUrl);
            $property->setAccessible(false);
        }
    }

    /**
     * @param $checkVersion
     *
     * @return array
     */
    private function getCheckVersions($checkVersion)
    {
        $filtered = $this->versionInfo->getLowerVersions($checkVersion);
        rsort($filtered);

        $versions = [];
        $default  = $this->versionInfo->getDefaultVersion();
        foreach ($filtered as $version) {
            // add versionless for default
            if ($version === $default) {
                $versions[] = '';
            }

            $versions[] = $version;
        }

        // if no default was added then add versionless as fallback
        if (!in_array($default, $versions)) {
            $versions[] = '';
        }

        return $versions;
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onResponse(FilterResponseEvent $event)
    {
        $request  = $event->getRequest();
        $response = $event->getResponse();
        $headers  = [
            'X-DeskPRO-Version'    => defined('DP_ACTIVE_BUILD') ? DP_ACTIVE_BUILD : 'NA',
            'X-DeskPRO-ApiVersion' => $request->get('version'),
        ];
        $response->headers->add($headers);
    }
}
