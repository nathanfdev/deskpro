<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Apps;

use Symfony\Component\HttpFoundation\Request;

class ProxyParams
{
    const DP_PARAMS_PREFIX = 'dp_';

    /**
     * @param string $name
     * @return string
     */
    public static function qualifyDpParamName($name)
    {
        return ProxyParams::DP_PARAMS_PREFIX . $name;
    }

    /**
     * @param array $name
     * @return array
     */
    public static function qualifyAllDpParams(array $name)
    {
        $qualified = [];
        foreach ($name as $key => $value) {
            $qualified[ProxyParams::qualifyDpParamName($key)] = $value;
        }
        return $qualified;
    }

    /**
     * @param $name
     * @param Request $request
     * @param null $default
     * @return string|null
     */
    public static function getDPQueryParam($name, Request $request, $default = null)
    {
        $key = ProxyParams::qualifyDpParamName($name);
        return $request->query->get($key, $default);
    }

    /**
     * @param $name
     * @param array $request
     * @return string|null
     */
    public static function getDPQueryParamFromArray($name, array $request)
    {
        $key = ProxyParams::qualifyDpParamName($name);
        return array_key_exists($key, $request) ? (string) $request[$key] : null;
    }

    /**
     * Returns a map of custom query params that might have been passed by apps
     *
     * @param Request $request
     * @return array
     */
    public static function getExtraQueryParams(Request $request)
    {
        return array_reduce($request->query->keys(), function ($acc,  $key) use ($request) {
            if(substr($key, 0, strlen(ProxyParams::DP_PARAMS_PREFIX)) !== ProxyParams::DP_PARAMS_PREFIX) {
                $acc[$key] = $request->query->get($key);
            }
            return $acc;
        }, []);
    }
}
