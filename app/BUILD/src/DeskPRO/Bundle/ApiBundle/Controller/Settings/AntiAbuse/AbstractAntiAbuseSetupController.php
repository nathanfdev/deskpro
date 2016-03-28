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

namespace DeskPRO\Bundle\ApiBundle\Controller\Settings\AntiAbuse;

use Application\DeskPRO\Entity\Setting;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AbstractAntiAbuseSetupController.
 */
abstract class AbstractAntiAbuseSetupController extends BaseController
{
    protected static $model;
    protected static $resolver;

    /**
     * @ApiDoc(
     *     section="Anti-abuse settings",
     *     resourceDescription="Admin anti-abuse settings ui",
     *     description="Get anti-abuse settings",
     *     statusCodes={
     *         200="Success"
     *     }
     * )
     *
     * @Get("")
     *
     * @return View
     */
    public function getAction()
    {
        return new View($this->wrap($this->getModel()));
    }

    /**
     * @ApiDoc(
     *     section="Anti-abuse settings",
     *     resourceDescription="Admin anti-abuse settings ui",
     *     description="Update anti-abuse settings",
     *     statusCodes={
     *         204="Success",
     *         400="Bad request"
     *     }
     * )
     *
     * @Put("")
     *
     * @param Request $request
     *
     * @return View
     */
    public function putAction(Request $request)
    {
        $model = $this->getModel();
        $form  = $this->get('form.factory')->createNamedBuilder(null, static::$model, $model)->getForm();
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        $this->persistModel($model);

        return new View(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @return mixed
     */
    protected function getModel()
    {
        return $this->get(static::$resolver)->getAntiAbuseSettings();
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\Setting
     */
    protected function getSettingRepository()
    {
        return $this->getRepository(Setting::class);
    }

    /**
     * @param mixed $model
     */
    abstract protected function persistModel($model);
}
