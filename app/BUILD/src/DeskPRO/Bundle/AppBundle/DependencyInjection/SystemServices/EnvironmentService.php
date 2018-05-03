<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DependencyInjection\SystemServices;

use GeoIp2\Database\Reader;
use Symfony\Component\HttpFoundation\RequestStack;

class EnvironmentService
{
    /**
     * @param RequestStack $token_storage
     */
    private $request;

    protected $geo_reader;

    public function __construct(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
    }

    public function getHostname()
    {
        return $this->getRemoteHost() ?: gethostbyaddr($this->getUserIp());
    }

    public function getGeoIp()
    {
        if (!$this->geo_reader) {
            $this->geo_reader = new Reader(DP_ROOT.'/vendor-src/geoip-db/GeoLite2-Country.mmdb');
        }

        $result = [
            'continent_code' => null,
            'country_code'   => null,
            'region'         => null,
            'city'           => null,
            'latitude'       => null,
            'longitude'      => null,
        ];

        $ip = $this->getUserIp();
        try {
            $record = $this->geo_reader->country($ip);
        } catch (\Exception $e) {
            return $result;
        }

        if ($record->country && $record->continent) {
            $result['continent_code'] = $record->continent->code;
            $result['country_code']   = $record->country->isoCode;
        }

        if (isset($record->city) && !empty($record->city->name)) {
            $result['city']      = $record->city;
            $result['latitude']  = $record->location->latitude;
            $result['longitude'] = $record->location->longitude;
        }

        return $result;
    }

    private function getRemoteHost()
    {
        return $this->request->server->get('REMOTE_HOST') ?: null;
    }

    private function getUserIp()
    {
        return $this->request->getClientIp();
    }
}
