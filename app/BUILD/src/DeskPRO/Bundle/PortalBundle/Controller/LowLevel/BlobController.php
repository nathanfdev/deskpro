<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
namespace DeskPRO\Bundle\PortalBundle\Controller\LowLevel;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class BlobController extends BaseController
{
    /**
     * @Route(
     *     "/favicon.ico",
     *     name="favicon"
     * )
     * @Method("GET")
     *
     * @return Response
     */
    public function faviconAction()
    {
        $settings = $this->container->get('settings_resolver');
        $em       = $this->container->get('doctrine')->getManager();
        $bs       = $this->container->get('blob.storage');

        $favicon_id = $settings->getGlobalSettings()->get('core.favicon_blob_id');
        $blob       = null;
        if ($favicon_id) {
            $blob = $em->getRepository('DeskPRO:Blob')->find($favicon_id);
        }

        if ($blob) {
            $response = new Response($bs->copyBlobRecordToString($blob));
        } else {
            $response = new BinaryFileResponse(DP_APP_DIR.'/src/Application/DeskPRO/Resources/assets/favicon.ico');
        }

        $response->headers->set('Content-Type', 'image/vnd.microsoft.icon; filename=favicon.ico');
        $response->headers->set('Content-Disposition', 'inline; filename=favicon.ico');
        $response->setExpires(date_create('+5 days'));
        $response->setMaxAge(432000);
        $response->setSharedMaxAge(432000);
        $response->setPublic();

        return $response;
    }

    /**
     * @Route(
     *     "/sitemap.xml",
     *     name="sitemap_xml"
     * )
     * @Method("GET")
     *
     * @return Response
     */
    public function sitemapAction()
    {
        $settings = $this->container->get('settings_resolver');
        $em       = $this->container->get('doctrine')->getManager();
        $bs       = $this->container->get('blob.storage');

        $blob_id = $settings->getGlobalSettings()->get('core.sitemap_blob_id');
        $blob    = null;
        if ($blob_id) {
            $blob = $em->getRepository('DeskPRO:Blob')->find($blob_id);
        }

        if ($blob) {
            $response = new Response($bs->copyBlobRecordToString($blob));
        } else {
            // means we JUST installed,
            // use an empty file until the sitemap can be generated on cron
            $response = new Response('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');
        }

        $response->headers->set('Content-Type', 'text/xml; filename=sitemap.xml');
        $response->headers->set('Content-Disposition', 'inline; filename=sitemap.xml');
        $response->setExpires(date_create('+5 days'));
        $response->setMaxAge(432000);
        $response->setSharedMaxAge(432000);
        $response->setPublic();

        return $response;
    }

    /**
     * @Route(
     *     "/robots.txt",
     *     name="robots_txt"
     * )
     * @Method("GET")
     *
     * @return Response
     */
    public function robotsAction()
    {
        $response = new BinaryFileResponse(DP_APP_DIR.'/src/DeskPRO/Bundle/PortalBundle/Resources/data/robots.txt');
        $response->headers->set('Content-Type', 'text/plain; filename=robots.txt');
        $response->headers->set('Content-Disposition', 'inline; filename=robots.txt');
        $response->setExpires(date_create('+7 days'));
        $response->setMaxAge(432000);
        $response->setSharedMaxAge(432000);
        $response->setPublic();

        return $response;
    }
}
