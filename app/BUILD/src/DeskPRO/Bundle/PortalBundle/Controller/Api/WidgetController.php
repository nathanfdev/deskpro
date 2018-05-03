<?php

namespace DeskPRO\Bundle\PortalBundle\Controller\Api;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;

/**
 * Class WidgetController.
 *
 * @Rest\Route("/portal/api/widget")
 */
class WidgetController extends AbstractApiController
{
    /**
     * @return View
     *
     * @Rest\Get("/brand_options")
     */
    public function getWidgetOptionsAction()
    {
        $brand = $this->get('brand_stack')->getActive()->getBrand();

        return new View($this->wrap($this->container->get('widget_settings_resolver')->getWidgetBrandOptions($brand)));
    }
}
