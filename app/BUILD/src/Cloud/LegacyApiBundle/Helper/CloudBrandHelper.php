<?php

namespace Cloud\LegacyApiBundle\Helper;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\TmpData;
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
            UNION
            (SELECT CONCAT('https://', url, '/') AS value FROM brands)
        ");

        // just makes sure the setting via a dynamic setting is included
        array_unshift($brandUrls, App::getContainer()->getSetting('core.deskpro_url'));

        $domains = ListUtils::map($brandUrls, function ($url) {
            if (!$url) {
                return null;
            }

            $host = @parse_url($url, \PHP_URL_HOST);

            return $host ?: null;
        });

        $domains = ListUtils::filterOutFalsey($domains);
        $domains = array_unique($domains);

        $tmpdata = new TmpData();
        $tmpdata->setType('dpc_set_domain');
        $tmpdata->setData('domains', $domains);
        $tmpdata->date_expire = new \DateTime('+10 minutes');

        $em->persist($tmpdata);
        $em->flush();

        $url = DP_MA_SERVER_SECURE.'/cloud/call/'.DPC_SITE_ID.'/'.$tmpdata->getCode();

        try {
            $client = new \Zend\Http\Client(null, ['timeout' => 15, 'sslverifypeer' => false]);
            $client->setMethod(\Zend\Http\Request::METHOD_GET);
            $client->setUri($url);
            $client->send();
        } catch (\Exception $e) {
            SystemErrorHandler::logException($e);
        }
    }

    /**
     * This requests a custom domain to get set up.
     *
     * @param $domain
     *
     * @return array
     */
    public static function provisionCustomDomain($domain)
    {
        $em = App::$container->getEm();

        $tmpdata = new TmpData();
        $tmpdata->setType('dpc_provision_custom_domain');
        $tmpdata->setData('domain', $domain);
        $tmpdata->date_expire = new \DateTime('+10 minutes');

        $em->persist($tmpdata);
        $em->flush();

        $url = DP_MA_SERVER_SECURE.'/cloud/call/'.DPC_SITE_ID.'/'.$tmpdata->getCode();

        try {
            $client = new \Zend\Http\Client(null, ['timeout' => 15, 'sslverifypeer' => false]);
            $client->setMethod(\Zend\Http\Request::METHOD_GET);
            $client->setUri($url);
            $response = $client->send();

            $data = json_decode($response->getBody(), true);

            return $data;
        } catch (\Exception $e) {
            SystemErrorHandler::logException($e);

            return [
                'error'            => true,
                'type'             => 'server_error',
                'code'             => 'server_error',
                'message'          => 'An error occurred while setting up your custom domain. Please try again later.',
                'internal_message' => $e->getMessage(),
            ];
        }
    }
}
