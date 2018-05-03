<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Settings\AntiAbuse;

use Application\DeskPRO\Entity\Setting;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use FOS\RestBundle\Controller\Annotations as Rest;
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
     * @Rest\Get("")
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
     * @Rest\Put("")
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
