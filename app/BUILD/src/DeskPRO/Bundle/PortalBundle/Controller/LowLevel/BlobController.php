<?php

namespace DeskPRO\Bundle\PortalBundle\Controller\LowLevel;

use DeskPRO\Bundle\PortalBundle\Designer\AssetsManager;
use DeskPRO\Bundle\PortalBundle\Helper\PortalModeTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class BlobController extends BaseController
{
    use PortalModeTrait;

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
        $assetManager = $this->container->get('dp.portal.designer.assets_manager');
        $asset        = $this->isPreviewMode($this->container)
            ? $this->getEditThemeSetBlobAsset($assetManager)
            : $this->getBlobAsset($assetManager);

        $blob = $asset ? $asset->getBlob() : null;
        $bs   = $this->container->get('blob.storage');

        if ($blob) {
            $response = new Response($bs->copyBlobRecordToString($blob));
        } else {
            $response = new BinaryFileResponse(DP_APP_DIR.'/src/Application/DeskPRO/Resources/assets/favicon.ico');
        }

        $response->headers->set('Content-Type', 'image/vnd.microsoft.icon; filename=favicon.ico');
        $response->headers->set('Content-Disposition', 'inline; filename=favicon.ico');
        $response->setExpires(date_create('+5 days'));
        $response->setPublic();

        return $response;
    }

    /**
     * @param AssetsManager $assetManager
     *
     * @return \DeskPRO\Bundle\AppBundle\Entity\ThemeSetAsset|null
     */
    private function getEditThemeSetBlobAsset(AssetsManager $assetManager)
    {
        return $assetManager->getEditThemeSetBlobAsset(AssetsManager::CUSTOM_FAVICON_FALLBACK)
            ?: $assetManager->getEditThemeSetBlobAsset(AssetsManager::CUSTOM_FAVICON_TAG);
    }

    /**
     * @param AssetsManager $assetManager
     *
     * @return \DeskPRO\Bundle\AppBundle\Entity\ThemeSetAsset|null
     */
    private function getBlobAsset(AssetsManager $assetManager)
    {
        return $assetManager->getBlobAsset(AssetsManager::CUSTOM_FAVICON_FALLBACK)
            ?: $assetManager->getBlobAsset(AssetsManager::CUSTOM_FAVICON_TAG);
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
        $response->setPublic();

        return $response;
    }
}
