<?php

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
        if (preg_match('#^/api/v2/(\d{8})#', $request->getPathInfo(), $matches)) {
            $currentVersion = (int) $matches[1];
        } else {
            $currentVersion = (int) $this->versionInfo->getDefaultVersion();
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
        if ($matchedVersion !== null) {
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
        $lowerVersions  = $this->versionInfo->getLowerVersions($checkVersion);
        $higherVersions = $this->versionInfo->getNextVersions($checkVersion);
        rsort($lowerVersions);
        sort($higherVersions);

        $versions = [];
        if (isset($lowerVersions[0]) && (
            $checkVersion > $lowerVersions[0] ||
            (!count($higherVersions) && !in_array($checkVersion, $lowerVersions))
        )) {
            foreach ($higherVersions as $version) {
                $versions[] = $version;
            }

            $versions[] = '';
        }

        foreach ($lowerVersions as $version) {
            $versions[] = $version;
        }

        // if no default was added then add versionless as fallback
        if (!in_array('', $versions)) {
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
