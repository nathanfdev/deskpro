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

namespace DeskPRO\Bundle\ApiBundle\Controller\Settings;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\BrandSetting;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Settings\Model\AbstractBrandAwareSettings;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractBrandAwareSettingsController extends BaseController
{
    /**
     * @var Brand
     */
    protected $brand;

    /**
     * @var AbstractBrandAwareSettings
     */
    protected $model;

    /**
     * @return AbstractBrandAwareSettings
     */
    abstract protected function getModel();

    /**
     * @return string
     */
    abstract protected function getType();

    /**
     * @param mixed $model
     * @param int   $brandId
     *
     * @return
     */
    abstract protected function persistModel($model, $brandId);

    /**
     * Set the brand as the active Brand in the BrandStack.
     *
     * @param int $brandId
     */
    protected function setBrandStack($brandId)
    {
        $brand = $this->getBrand($brandId);

        /** @var BrandStack $brandStack */
        $brandStack = $this->get('brand_stack');
        $brandStack->push($brand);
    }

    /**
     * @param $brandId
     *
     * @return Brand
     */
    protected function getBrand($brandId)
    {
        if ($this->brand) {
            return $this->brand;
        }
        $this->brand = $this->getRepository(Brand::class)->find($brandId);
        if (!$this->brand) {
            throw $this->createNotFoundException('Brand not found');
        }

        return $this->brand;
    }

    /**
     * @param Request $request
     * @param         $brandId
     *
     * @return View
     */
    protected function handleForm(Request $request, $brandId)
    {
        $this->getBrand($brandId);

        $model = $this->getModel();

        $form = $this->createForm($this->getType(), $model);
        $form->submit($request->request->all());

        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        $this->persistModel($model, $brandId);

        return new View(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\BrandSetting
     */
    protected function getSettingRepository()
    {
        return $this->getRepository(BrandSetting::class);
    }
}
