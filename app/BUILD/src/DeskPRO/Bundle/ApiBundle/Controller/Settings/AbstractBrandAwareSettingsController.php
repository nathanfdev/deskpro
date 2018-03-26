<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Settings;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\BrandSetting;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Settings\Model\AbstractBrandAwareSettings;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AbstractBrandAwareSettingsController.
 */
abstract class AbstractBrandAwareSettingsController extends BaseController
{
    /**
     * @param Brand $brand
     *
     * @return AbstractBrandAwareSettings
     */
    abstract protected function getModel(Brand $brand);

    /**
     * @return string
     */
    abstract protected function getType();

    /**
     * @param mixed $model
     *
     * @return
     */
    abstract protected function persistModel(AbstractBrandAwareSettings $model);

    /**
     * @param Request $request
     * @param mixed   $model
     *
     * @return View
     */
    protected function handleForm(Request $request, AbstractBrandAwareSettings $model)
    {
        $form = $this->createForm($this->getType(), $model);
        $form->submit($request->request->all());

        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        $this->persistModel($model);
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\BrandSetting
     */
    protected function getSettingRepository()
    {
        return $this->getRepository(BrandSetting::class);
    }
}
