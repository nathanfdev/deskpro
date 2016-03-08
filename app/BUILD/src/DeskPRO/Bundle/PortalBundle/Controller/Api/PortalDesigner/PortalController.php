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
namespace DeskPRO\Bundle\PortalBundle\Controller\Api\PortalDesigner;

use Application\DeskPRO\Entity\Blob;
use DeskPRO\Bundle\PortalBundle\Controller\Api\AbstractApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class PortalController.
 */
class PortalController extends AbstractApiController
{
    use HelperMethods;

    /**
     * @Route("/portal/api/style/portal.css", name="dp_portal_designer_custom_css", defaults={"direction": "LTR"})
     * @Route("/portal/api/style/portal-rtl.css", name="dp_portal_designer_custom_css_rtl", defaults={"direction": "RTL"})
     * @Method({"GET"})
     *
     * @param string  $direction
     * @param Request $request
     *
     * @return Response
     */
    public function getCssFileAction($direction = 'LTR', Request $request)
    {
        $blob_storage = $request->get('preview')
                      ? $this->getStylesManager()->getEditThemeSetCssBlobStorage($direction)
                      : $this->getStylesManager()->getCssBlobStorage($direction);

        if (!$blob_storage) {
            throw $this->createNotFoundException('Custom styles not found');
        }

        return new Response($blob_storage->getData(), 200, ['Content-Type' => 'text/css']);
    }

    /**
     * @Route("/portal/api/style/assets/{name}", name="dp_portal_custom_asset")
     * @Method({"GET"})
     */
    public function serveAssetAction($name)
    {
        if (!$blob_storage = $this->getAssetsManager()->getAssetBlobStorage($name)) {
            throw $this->createNotFoundException('Asset file not found');
        }
        if (!$blob = $this->getManager()->find(Blob::class, $blob_storage->getBlobId())) {
            throw $this->createNotFoundException('Asset file info not found');
        }

        return new Response($blob_storage->getData(), 200, ['Content-Type' => $blob->content_type]);
    }
}
