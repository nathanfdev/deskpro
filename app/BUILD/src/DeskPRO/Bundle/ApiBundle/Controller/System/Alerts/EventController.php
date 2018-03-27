<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\System\Alerts;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\AbstractEvent;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class IncidentController.
 *
 * @ApiModes("all")
 * @Rest\Route("/system/events")
 * @ApiDoc(target="all", section="System", output="DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\AbstractEvent")
 */
class EventController extends CrudController
{
    public static $entity       = AbstractEvent::class;
    public static $exposeOnly   = ['get'];
    public static $listPaginate = false;

    /**
     * {@inheritdoc}
     */
    protected function getManager()
    {
        return $this->getDoctrine()->getManager('system');
    }
}
