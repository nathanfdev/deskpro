<?php

namespace DeskPRO\Bundle\MessengerBundle\Admin\Controller;

use Application\DeskPRO\Entity\Brand;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\Settings\AbstractBrandAwareSettingsController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Settings\Model\AbstractBrandAwareSettings;
use DeskPRO\Bundle\MessengerBundle\Form\Type\Settings\MessengerType;
use DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerSettings;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;

/**
 * Class AdminController.
 *
 * @ApiModes("all")
 * @ApiUserContext("admin")
 * @Rest\Route("/messenger/settings/{brand}")
 * @Feature("messenger")
 */
class AdminController extends AbstractBrandAwareSettingsController
{
    /**
     * Gather widget setup information.
     *
     * @ApiDoc(
     *     section="Messenger setup",
     *     resourceDescription="Operations about messenger setup",
     *     description="get messenger setup",
     *     statusCodes={
     *         200="Returned if request was successful",
     *     },
     *     output="DeskPRO\Bundle\MessengerBundle\Settings\Model\ModelSettings"
     *)
     * @Rest\Get("/setup")
     *
     * @param Brand $brand
     *
     * @return View
     */
    public function getSettingsAction(Brand $brand)
    {
        return View::create($this->wrap($this->getModel($brand)));
    }

    /**
     * @param Brand $brand
     *
     * @return MessengerSettings
     */
    protected function getModel(Brand $brand)
    {
        return $this->get('messenger.service.settings_resolver')->getMessengerSettings($brand);
    }

    protected function getType()
    {
        return MessengerType::class;
    }

    protected function persistModel(AbstractBrandAwareSettings $model)
    {
        // TODO: Implement persistModel() method.
    }
}
