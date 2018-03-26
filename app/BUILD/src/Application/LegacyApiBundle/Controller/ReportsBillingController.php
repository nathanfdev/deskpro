<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;

/**
 * @ApiModes("all")
 */
class ReportsBillingController extends AbstractController
{
    //###################################################################################################################
    // get report
    //###################################################################################################################

    public function getAction($id)
    {
        /*
         * @var \Application\DeskPRO\Reports\Billing
         */
        $reports_billing = $this->container->getSystemService('reports_billing');
        $report          = $reports_billing->getById($id);

        if (!$report) {
            throw $this->createNotFoundException();
        }

        $rendered_result = $reports_billing->getRenderedResult($id);

        return $this->createApiResponse(
            [
                 'rendered_result' => $rendered_result,
            ]
        );
    }
}
