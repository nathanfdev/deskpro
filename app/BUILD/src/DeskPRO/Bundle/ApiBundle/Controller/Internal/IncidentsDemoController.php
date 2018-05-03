<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Internal;

use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\SystemBundle\Controller\Internal\BaseIncidentsDemoController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;

/**
 * Class IncidentsDemoController.
 *
 * @ApiModes("all")
 * @Rest\Route("/_internal/incidents-demo")
 */
class IncidentsDemoController extends BaseIncidentsDemoController
{
    /**
     * {@inheritdoc}
     */
    protected function createResponse($data)
    {
        return View::create($this->wrap($data));
    }
}
