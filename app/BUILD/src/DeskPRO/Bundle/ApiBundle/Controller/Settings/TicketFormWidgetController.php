<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Settings;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Component\Util\MapUtils;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class TicketFormWidgetController.
 *
 * @ApiModes("all")
 * @Rest\Route("/ticket-form-widget/code")
 */
class TicketFormWidgetController extends BaseController
{
    /**
     * @ApiDoc(
     *     section="Ticket Form Widget",
     *     description="Get ticket form widget JS code",
     *     statusCodes={
     *         200="OK"
     *     },
     *     noOutput=true
     * )
     * @Rest\Get("")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function getJsAction(Request $request)
    {
        $language       = $request->get('language') ?: 'en';
        $department     = (int) $request->get('department') ?: 0;
        $hideDepartment = (int) $request->get('hide_department') ?: 0;
        $width          = $request->get('width', '500');

        $options = [
            'language'        => $language,
            'department'      => $department,
            'hide_department' => $hideDepartment,
            'width'           => $width,
        ];

        return new Response($this->getCode($options));
    }

    /**
     * @param array $options
     *
     * @return string
     */
    private function getCode(array $options)
    {
        $helpdeskUrl = rtrim($this->generateUrl('portal_home', [], UrlGeneratorInterface::ABSOLUTE_URL), '/');

        $loaderSrc = $this->get('assets.packages')->getUrl('embed_loader.js', 'app_assets');
        $loaderSrc = preg_replace('#/assets/.*?/pub/#', '/dyn-assets/pub/', $loaderSrc);
        $loaderSrc = preg_replace('#\?.*?$#', '', $loaderSrc);

        if (!preg_match('#^https?://#i', $loaderSrc)) {
            $loaderSrc = $helpdeskUrl.$loaderSrc;
        }

        $options = MapUtils::prependItem($options, 'type', 'form');
        $options = MapUtils::prependItem($options, 'containerId', 'deskpro_embed_form_container');
        $options = MapUtils::prependItem($options, 'helpdeskUrl', $helpdeskUrl);

        $options = json_encode($options, \JSON_PRETTY_PRINT);

        $html = '<div id="deskpro_embed_form_container"></div>';

        return "<!--DESKPRO_EMBED_LOADER::BEGIN-->\n$html\n<script type=\"text/javascript\">\nwindow.DESKPRO_EMBED_OPTIONS = $options;\n</script>\n<script type=\"text/javascript\" src=\"$loaderSrc\"></script>\n<!--DESKPRO_EMBED_LOADER::END-->";
    }
}
