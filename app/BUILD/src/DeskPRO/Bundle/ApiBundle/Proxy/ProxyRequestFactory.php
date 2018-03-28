<?php

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
     * @var AppStateRepository
     */
    private $appStateRepository;

    /**
     * @var string[]
     */
    private $requestVariables = [];

    /**
     * @param EntityManager $em
     * @return ProxyRequestFactory
     */
    public static function create(EntityManager $em)
    {
        /** @var AppStateRepository $appStateRepo */
        $appStateRepo = $em->getRepository(AppState::class);
        return new ProxyRequestFactory($appStateRepo);
    }

    /**
     * @param AppStateRepository $appStateRepository
     */
    public function __construct(AppStateRepository $appStateRepository)
    {
        $this->appStateRepository = $appStateRepository;
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

            $this->collectRequestVariables($instance, $person, $nameProviders);
        }

        if ($shouldReplaceVars) {
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
        $strategyName = $header->getSignWithStrategy();
        if ($strategyName === RequestSigningStrategy::STRATEGY_OAUTH1) {

            $credentialNames = $header->getCredentialNames();
            $requestVariables = array_intersect_key($this->requestVariables, array_flip($credentialNames));
            if (count($credentialNames) !== count($requestVariables)) {
                throw new \RuntimeException('unknown credentials');
            }

            return RequestSigningStrategyOauth1::fromRequestVariables($requestVariables);
        }

        throw new \RuntimeException('unknown sign with strategy');
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

        $parts = explode(' ', trim($value));
        $nonEmptyParts = array_filter($parts, function($part) { return !empty($part); });

        if ($parts[0] !== $nonEmptyParts[0] || count($nonEmptyParts) < 2) {
            return null;
        }

        $strategy  = array_shift($nonEmptyParts);
        return new ProxySignWithHeader($strategy, $nonEmptyParts);
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

        return $manifest->getDomainWhitelist();
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
            // replace request variables
            foreach ($this->requestVariables as $name => $value) {
                if (is_string($value)) {
                    $string = str_replace("{{" .$name. "}}", $value, $string);
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
     * @param Person $person
     * @param array $nameProviders
     */
    private function collectRequestVariables( AppInstance $instance, Person $person, $nameProviders)
    {
        $names = $this->getAppStateNamesFromValue($nameProviders);

        $this->requestVariables = [];
        $appStates = $this->appStateRepository->findReadableByName($instance, $person, $names);
        if ($appStates) {
            foreach ($appStates as $appState) {
                // TODO we need a better way to tell if a value should be json_decoded
                $value = $appState->getValue();
                $jsonDecodedValue = \json_decode($value);

                $variableValue = JSON_ERROR_NONE === json_last_error() ? $jsonDecodedValue : $value;
                $this->requestVariables[$appState->getName()] = $variableValue;
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
            $names = $value->getCredentialNames();
        } if (is_string($value)) {
            if (preg_match_all('#{{(.*?)}}#', $value, $m)) {
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
