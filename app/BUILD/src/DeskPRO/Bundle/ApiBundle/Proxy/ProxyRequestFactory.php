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

namespace DeskPRO\Bundle\ApiBundle\Proxy;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\AppStore\AppInstance;
use DeskPRO\Bundle\AppBundle\Entity\AppStore\AppState;
use DeskPRO\Bundle\AppBundle\Entity\Repository\AppStateRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ProxyRequestFactory.
 */
class ProxyRequestFactory
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var string[]
     */
    private $settings = [];

    /**
     * @var string[]
     */
    private $privateStateVars = [];

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param Request   $request
     *
     * @return SimpleProxyRequest
     */
    public function createFromRequest(Request $request)
    {
        $proxyMethod  = $this->getProxyMethod($request);
        $proxyUrl     = $this->getOriginalProxyUrl($request);
        $proxyHeaders = $this->getOriginalProxyHeaders($request);

        return new SimpleProxyRequest($proxyMethod, $proxyUrl, $proxyHeaders);
    }

    /**
     * @param AppInstance $instance
     * @param Request     $request
     * @param Person      $person
     *
     * @return ApplicationProxyRequest
     */
    public function createFromAppRequest(AppInstance $instance, Request $request, Person $person)
    {
        $proxyMethod  = $this->getProxyMethod($request);
        $proxyUrl     = $this->getOriginalProxyUrl($request);
        $proxyHeaders = $this->getOriginalProxyHeaders($request);
        $whiteList    = $this->getOriginalWhiteList($instance);

        $shouldReplaceVars = $this->shouldReplaceVars($request);
        $signWithHeader = $this->getSignWithHeader($request);

        if ($shouldReplaceVars || !is_null($signWithHeader)) {
            $nameProviders = [];
            if ($signWithHeader) {
                $nameProviders[] = $signWithHeader;
            }

            if ($shouldReplaceVars) {
                $nameProviders = array_merge($nameProviders, [$proxyUrl, $proxyHeaders, $whiteList ]);
            }

            $this->collectPrivateStateVars($instance, $person, $nameProviders);
        }

        if ($shouldReplaceVars) {
            // prepare placeholder values
            $this->collectSettings($instance);
            // replace placeholders
            $proxyUrl = $this->replaceVars($proxyUrl);

            foreach ($proxyHeaders as &$value) {
                $value = $this->replaceVars($value);
            }
            foreach ($whiteList as &$value) {
                $value = $this->replaceVars($value);
            }
        }

        $requestSigningStrategy = $signWithHeader ? $this->getRequestSigningStrategy($signWithHeader) : null;
        return new ApplicationProxyRequest($proxyMethod, $proxyUrl, $proxyHeaders, $whiteList, $requestSigningStrategy);
    }

    private function getRequestSigningStrategy(ProxySignWithHeader $header)
    {
        $credentialName = $header->getCredentialName();
        if (array_key_exists($credentialName, $this->privateStateVars)) {
            return new RequestSigningStrategy($header->getAlgorithm(), $this->privateStateVars[$credentialName]);
        }

        return null;
    }

    /**
     * @param Request $request
     *
     * @return ProxySignWithHeader
     */
    private function getSignWithHeader( Request $request)
    {
        $value = $request->headers->get(ProxySignWithHeader::NAME, null);
        if (is_null($value)) {
            return null;
        }

        $parts = explode(' ', $value);
        if (
            count($parts) === 2
            && is_string($parts[0]) && !empty($parts[0])
            && is_string($parts[1]) && !empty($parts[1])
        ) {
            return new ProxySignWithHeader($parts[0], $parts[1]);
        }
        return null;
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    private function getProxyMethod(Request $request)
    {
        return $request->headers->get('X-Proxy-Method') ?: $request->getMethod();
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    private function shouldReplaceVars(Request $request)
    {
        return (bool) $request->headers->get('X-Proxy-ReplaceVars', false);
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    private function getOriginalProxyUrl(Request $request)
    {
        $url = $request->headers->get('X-Proxy-Url');
        if (!is_string($url)) {
            $url = '';
        }

        return $url;
    }

    /**
     * @param AppInstance $instance
     *
     * @return string[]
     */
    private function getOriginalWhiteList(AppInstance $instance)
    {
        $app = $instance->getApp();
        if (!$app) {
            return [];
        }

        $manifest = $app->getManifest();
        if (!$manifest) {
            return [];
        }

        return $manifest->getExternalApis();
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    private function getOriginalProxyHeaders(Request $request)
    {
        $autoHeadersEnabled = $request->headers->get('X-Proxy-AutoHeaders', 'true') === 'true';
        $proxyHeaders       = [];

        foreach ($request->headers->all() as $name => $value) {
            if (preg_match('#x-proxy-header-(.*)#', $name, $m)) {
                // collect x-proxy headers only
                $proxyHeaders[$m[1]] = $value;
            } elseif ($autoHeadersEnabled && strpos($name, 'x-') !== 0) {
                // collect all other HTTP_ headers except special ones
                $proxyHeaders[$name] = $value;
            }
        }

        return $proxyHeaders;
    }

    /**
     * @param string $string
     *
     * @return string
     */
    private function replaceVars($string)
    {
        if (is_string($string)) {
            // replace settings
            if (is_array($this->settings)) {
                foreach ($this->settings as $name => $value) {
                    $string = str_replace("{{settings.$name}}", $value, $string);
                }
            }

            // replace state vars
            if (is_array($this->privateStateVars)) {
                foreach ($this->privateStateVars as $name => $value) {
                    $string = str_replace("{{privateState.$name}}", $value, $string);
                }
            }

            // replace all undefined vars
            $string = preg_replace('#{{.*}}#', '(undefined)', $string);
        } elseif (is_array($string)) {
            foreach ($string as &$item) {
                $item = $this->replaceVars($item);
            }
        }

        return $string;
    }

    /**
     * @param AppInstance $instance
     */
    private function collectSettings(AppInstance $instance)
    {
        $this->settings = $instance->getSettings() ?: [];
    }

    /**
     * @param AppInstance $instance
     * @param Person $person
     * @param array $nameProviders
     */
    private function collectPrivateStateVars(AppInstance $instance, Person $person, $nameProviders)
    {
        $this->privateStateVars = [];

        $names = $this->getAppStateNamesFromValue($nameProviders);

        /** @var AppStateRepository $appStateRepo */
        $appStateRepo = $this->em->getRepository(AppState::class);
        $appStates    = $appStateRepo->findReadableByName($instance, $person, $names);

        if ($appStates) {
            foreach ($appStates as $appState) {
                $this->privateStateVars[$appState->getName()] = $appState->getValue();
            }
        }
    }

    /**
     * @param string $value
     *
     * @return array
     */
    private function getAppStateNamesFromValue($value)
    {
        $names = [];
        if ($value instanceof ProxySignWithHeader) {
            $names[] = $value->getCredentialName();
        } if (is_string($value)) {
            if (preg_match_all('#{{privateState\.(.*?)}}#', $value, $m)) {
                $names = $m[1];
            }
        } elseif (is_array($value)) {
            foreach ($value as $item) {
                foreach ($this->getAppStateNamesFromValue($item) as $subName) {
                    $names[] = $subName;
                }
            }
        }

        return array_values($names);
    }
}
