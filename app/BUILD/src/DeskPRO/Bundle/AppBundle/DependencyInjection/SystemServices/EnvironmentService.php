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
namespace DeskPRO\Bundle\AppBundle\DependencyInjection\SystemServices;

use Symfony\Component\HttpFoundation\RequestStack;
use GeoIp2\Database\Reader;

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
            $this->geo_reader = new Reader(DP_ROOT . '/vendor-src/geoip-db/GeoLite2-City.mmdb');
        }

        $result = array(
            'continent_code' => null,
            'country_code'   => null,
            'region'         => null,
            'city'           => null,
            'latitude'       => null,
            'longitude'      => null,
        );

        $ip = $this->getUserIp();
        try {
            $record = $this->geo_reader->city($ip);
        } catch (\Exception $e) {
            return $result;
        }

        if ($record->country && $record->continent) {
            $result['continent_code'] = $record->continent->code;
            $result['country_code']   = $record->country->isoCode;
        }

        if ($record->city && $record->city->name) {
            $result['city']       = $record->city;
            $result['latitude']   = $record->location->latitude;
            $result['longitude']  = $record->location->longitude;
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
