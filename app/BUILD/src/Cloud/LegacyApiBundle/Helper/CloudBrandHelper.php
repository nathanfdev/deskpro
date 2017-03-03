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

namespace Cloud\LegacyApiBundle\Helper;

use Application\DeskPRO\App;
use DeskPRO\Component\Util\ListUtils;
use DpSys\LowError\SystemErrorHandler;

class CloudBrandHelper
{
    public static function flushBrandDomains()
    {
        $em = App::$container->getEm();
        $db = App::$container->getDb();

        $brandUrls = $db->fetchAllCol("
            (SELECT value FROM settings WHERE name = 'core.deskpro_url')
            UNION
            (SELECT value FROM settings_brand WHERE name = 'core.deskpro_url')
        ");

        $domains = ListUtils::map($brandUrls, function ($url) {
            $host = @parse_url($url, \PHP_URL_HOST);

            return $host ?: null;
        });
        $domains = ListUtils::map($domains, function ($domain) {
            return $domain && strpos($domain, '.deskpro.com') === false;
        });

        $tmpdata = new TmpData();
        $tmpdata->setType('dpc_set_domain');
        $tmpdata->setData('domains', $domains);
        $tmpdata->date_expire = new \DateTime('+10 minutes');

        $em->persist($tmpdata);
        $em->flush();

        $url = DP_MA_SERVER_SECURE.'/cloud/call/'.DPC_SITE_ID.'/'.$tmpdata->getCode();

        \DpShutdown::add(function () use ($url) {
            try {
                $client = new \Zend\Http\Client(null, ['timeout' => 15, 'sslverifypeer' => false]);
                $client->setMethod(\Zend\Http\Request::METHOD_GET);
                $client->setUri($url);
                $client->send();
            } catch (\Exception $e) {
                SystemErrorHandler::logException($e);
            }
        });
    }
}
