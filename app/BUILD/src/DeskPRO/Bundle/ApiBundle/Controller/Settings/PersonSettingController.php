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

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\PersonSetting;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * API access to person settings.
 *
 * @ApiModes("all")
 */
class PersonSettingController extends BaseController
{
    /**
     * @ApiDoc(
     *      section="Person settings",
     *      description="create a new person setting",
     *      statusCodes={
     *          201="Created",
     *          400="Bad Request"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\PersonSetting"
     * )
     * @Rest\Post("/person_setting")
     *
     * @param Request $request
     *
     * @return View
     */
    public function postAction(Request $request)
    {
        $name  = $request->request->get('name');
        $value = $request->request->get('value');

        $setting = new PersonSetting($this->getUser(), $name);
        $setting->setValue($value);

        $em = $this->getManager();
        $em->persist($setting);
        $em->flush();

        return View::create($this->wrap($setting), Response::HTTP_CREATED, [
            'Location' => $this->generateUrl('api_person_setting_get', ['name' => $setting->getName()]),
        ]);
    }

    /**
     * @ApiDoc(
     *      section="Person settings",
     *      description="update person setting",
     *      statusCodes={
     *          204="Updated",
     *          400="Bad Request",
     *          404="Not Found"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\PersonSetting"
     * )
     * @Rest\Put("/person_setting")
     *
     * @param Request $request
     *
     * @return View
     */
    public function putAction(Request $request)
    {
        $name    = $request->request->get('name');
        $value   = $request->request->get('value');
        $setting = $this->getRepository(PersonSetting::class)->find([
            'name'   => $name,
            'person' => $this->getUser(),
        ]);

        $setting->setValue($value);

        $em = $this->getManager();
        $em->flush();

        return View::create($this->wrap($setting), Response::HTTP_CREATED, [
            'Location' => $this->generateUrl('api_person_setting_get', ['name' => $setting->getName()]),
        ]);
    }

    /**
     * @ApiDoc(
     *      section="Person settings",
     *      description="get person settings",
     *      statusCodes={
     *          200="Success"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\PersonSetting"
     * )
     * @Rest\Get("/person_setting")
     *
     * @return View
     */
    public function listAction()
    {
        $settings = $this->getDoctrine()->getRepository(PersonSetting::class)->findBy([
            'person' => $this->getUser(),
        ]);

        return View::create($this->wrap($settings));
    }

    /**
     * @ApiDoc(
     *      section="Person settings",
     *      description="get a person setting",
     *      requirements={
     *          {
     *              "name"="name",
     *              "requirement"="\w+",
     *              "description"="the name of the setting",
     *              "dataType"="string"
     *          }
     *      },
     *      statusCodes={
     *          200="Success",
     *          404="We can't find setting with given name"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\PersonSetting"
     * )
     * @Rest\Get("/person_setting/{name}", name="api_person_setting_get")
     *
     * @param string $name
     *
     * @return View
     */
    public function getAction($name)
    {
        $setting = $this->getDoctrine()->getRepository(PersonSetting::class)->find([
            'name'   => $name,
            'person' => $this->getUser(),
        ]);

        if (null === $setting) {
            throw $this->createNotFoundException();
        }

        return View::create($this->wrap($setting));
    }
}
