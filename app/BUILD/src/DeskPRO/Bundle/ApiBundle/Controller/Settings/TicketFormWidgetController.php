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

namespace DeskPRO\Bundle\ApiBundle\Controller\Settings;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\AppEnv\AppEnvInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class TicketFormWidgetController.
 *
 * @ApiModes("all")
 */
class TicketFormWidgetController extends BaseController
{
    /**
     * @ApiDoc(
     *     section="Ticket Form Widget",
     *     description="Get ticket form widget JS code",
     *     statusCodes={
     *         200="OK"
     *     }
     * )
     * @Rest\Get("/ticket-form-widget/code")
     */
    public function getJsAction(Request $request)
    {
        /** @var AppEnvInterface $env */
        $env    = $this->get('deskpro.app_env');
        $assets = $env->getAppWwwAssetDir();
        $file   = "$assets/pub/build/embed_loader.js";
        $js     = file_get_contents($file);
        $js     = strtr($js, [
            '__DP_URL__'     => $this->getDpUrl(),
            '__DP_OPTIONS__' => $this->getDpOptions($request),
        ]);
        $js     = str_replace("\n", "\n  ", $js); // JS 2 space padding
        $script = <<<CODE
<!--DESKPRO_TICKET_FORM_WIDGET::BEGIN-->
<script type="text/javascript">
  $js
</script>
<!--DESKPRO_TICKET_FORM_WIDGET::END-->
CODE;

        return new Response($script);
    }

    /**
     * Get __DP_URL__ JS placeholder value.
     *
     * @return string
     */
    private function getDpUrl()
    {
        return '"'.$this->generateUrl('portal_home', [], UrlGeneratorInterface::ABSOLUTE_URL).'"';
    }

    /**
     * Get __DP_OPTIONS__ JS placeholder value.
     *
     * @param Request $request
     *
     * @return string
     */
    private function getDpOptions(Request $request)
    {
        $language       = $request->get('language') ?: 'en';
        $department     = (int) $request->get('department') ?: 0;
        $hideDepartment = (int) $request->get('hide_department') ?: 0;
        $width          = $request->get('width', '500');

        return "{language: '$language', department: $department, hide_department: $hideDepartment, width: '$width'}";
    }
}
