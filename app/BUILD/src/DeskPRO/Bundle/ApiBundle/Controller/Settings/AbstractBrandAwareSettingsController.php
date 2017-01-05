<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
