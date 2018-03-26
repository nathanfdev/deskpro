<?php

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
 * @Rest\Route("/person_setting")
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
     *      output="DeskPRO\Bundle\AppBundle\Entity\PersonSetting",
     *      parameters={
     *          { "name" = "name", "dataType" = "string", "format" = "string", "required" = true, "description" = "setting name" },
     *          { "name" = "value", "dataType" = "string", "format" = "string", "required" = true, "description" = "setting value" },
     *      }
     * )
     * @Rest\Post("")
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
     *      output="DeskPRO\Bundle\AppBundle\Entity\PersonSetting",
     *      parameters={
     *          { "name" = "name", "dataType" = "string", "format" = "string", "required" = true, "description" = "setting name" },
     *          { "name" = "value", "dataType" = "string", "format" = "string", "required" = true, "description" = "setting value" },
     *      }
     * )
     * @Rest\Put("")
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
     *      output="array<DeskPRO\Bundle\AppBundle\Entity\PersonSetting>"
     * )
     * @Rest\Get("")
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
     * @Rest\Get("/{name}", name="api_person_setting_get")
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
