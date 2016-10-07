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

namespace DeskPRO\Bundle\AppBundle\HitTrack\Recorder;

use DeskPRO\Bundle\AppBundle\Entity\HitRecord;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class HitRecordFactory
{
    /**
     * @param string      $page_type
     * @param string      $page_id
     * @param Request     $request
     * @param string|null $visitor_id
     *
     * @return HitRecord
     */
    public function fromRequest($page_type, $page_id, Request $request, $visitor_id = null)
    {
        switch ($request->getMethod()) {
            case 'POST':
                if ($request->getContentType() === 'application/json') {
                    $raw  = $request->getContent();
                    $data = @json_decode($raw) ?: [];
                    $bag  = new ParameterBag($data);
                } else {
                    $bag = $request->request;
                }
                break;

            case 'GET':
                $bag = $request->query;
                // json in 'dat' qs
                if ($bag->get('dat')) {
                    $raw  = $bag->get('dat');
                    $data = @json_decode($raw) ?: [];
                    if ($data) {
                        $bag = new ParameterBag($data);
                    }
                }
                break;

            default:
                $bag = null;
                break;
        }

        if (!$bag) {
            throw new \InvalidArgumentException('No params');
        }

        $ip_address = $request->getClientIp();
        $user_agent = $request->headers->get('User-Agent', '');

        $url      = $bag->get('url');
        $referrer = $bag->get('referrer', '');

        $meta = $bag->get('meta', null);
        if (!$meta || !is_array($meta)) {
            $meta = [];
        }

        $meta = array_filter($meta, function ($v) {
            return is_scalar($v);
        });

        // If we werent given a URL specifically,
        // maybe its a fallback image in which case we sholud use
        // the referrer which will be the 'real' page the user is actually on
        if (!$url) {
            $url = $request->headers->get('Referer', null);
        }

        if (!$url) {
            throw new \InvalidArgumentException('Missing URL');
        }

        $hit = new HitRecord($page_type, $page_id, $url, $meta);

        if ($visitor_id) {
            $hit->setVisitorId($visitor_id);
        }
        if ($ip_address) {
            $hit->setIpAddress($ip_address);
        }
        if ($user_agent) {
            $hit->setUserAgent($user_agent);
        }
        if ($referrer) {
            $hit->setReferrer($referrer);
        }

        return $hit;
    }
}
