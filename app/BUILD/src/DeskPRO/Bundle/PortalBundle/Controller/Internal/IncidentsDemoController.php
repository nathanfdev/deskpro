<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Controller\Internal;

use DeskPRO\Bundle\SystemBundle\Controller\Internal\BaseIncidentsDemoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class IncidentsDemoController.
 *
 * @Route("_internal/incidents-demo")
 */
class IncidentsDemoController extends BaseIncidentsDemoController
{
    /**
     * {@inheritdoc}
     */
    protected function createResponse($data)
    {
        return new Response($data);
    }
}
