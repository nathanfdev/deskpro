<?php

namespace DeskPRO\Bundle\ApiBundle\Controller;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Timezone;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;

/**
 * Class TimezonesController.
 *
 * @ApiModes("all")
 * @Rest\Route("/timezones")
 * @ApiDoc(target="all", section="Timezones")
 */
class TimezonesController extends BaseController
{
    /**
     * Retrieve the list of PHP timezones.
     *
     * @ApiDoc(
     *     resourceDescription="Operations about timezones",
     *     description="get timezones",
     *     statusCodes={
     *         200="Returned if everything is OK",
     *     },
     *     output="array<DeskPRO\Bundle\AppBundle\Serializer\Model\Timezone>"
     * )
     *
     * @Rest\Get("")
     */
    public function listAction()
    {
        $timezones = [];
        foreach (\DateTimeZone::listIdentifiers() as $num => $timezone) {
            $timezones[] = new Timezone($num, $timezone);
        }

        return View::create($this->wrap($timezones));
    }
}
