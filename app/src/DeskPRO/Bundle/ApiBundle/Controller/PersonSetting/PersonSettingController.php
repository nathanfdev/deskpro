<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\ApiBundle\Controller\PersonSetting;

use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\ApiBundle\Exception\WrappedApiErrorException;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiTags;
use DeskPRO\Bundle\AppBundle\Entity\PersonSetting;
use DeskPRO\Bundle\AppBundle\Error\Exception\InvalidFormException;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * API access to person settings.
 *
 * @ApiModes("all")
 * @ApiTags("agent.person_settings.person_settings")
 */
class PersonSettingController extends BaseController
{
    /**
     * @ApiDoc(
     *      description="create a new person setting",
     *      statusCodes={
     *          201="Created",
     *          400="Bad Request"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\PersonSetting"
     * )
     * @Post("/person_setting", name="api_person_setting_post")
     *
     * @param Request $request
     *
     * @throws WrappedApiErrorException
     * @throws InvalidFormException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     *
     * @return View
     */
    public function postAction(Request $request)
    {
        $em      = $this->getDoctrine()->getManager();
        $name    = $request->request->get('name');
        $value   = $request->request->get('value');
        $setting = new PersonSetting($this->getUser(), $name);
        $setting->setValue($value);
        $em->persist($setting);
        $em->flush();
        $location = $this->generateUrl(
            'api_person_setting_get',
            ['name' => $setting->getName()]
        );

        return View::create(
            $this->dataSerialize($setting),
            Response::HTTP_CREATED,
            [
                'Location' => $location,
            ]
        );
    }

    /**
     * @ApiDoc(
     *      description="update person setting",
     *      statusCodes={
     *          204="Updated",
     *          400="Bad Request",
     *          404="Not Found"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\PersonSetting"
     * )
     * @Put("/person_setting", name="api_person_setting_put")
     *
     * @param Request $request
     *
     * @throws WrappedApiErrorException
     * @throws InvalidFormException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     *
     * @return View
     */
    public function putAction(Request $request)
    {
        $em      = $this->getDoctrine()->getManager();
        $name    = $request->request->get('name');
        $value   = $request->request->get('value');
        $setting = $this->getDoctrine()->getManager()->getRepository('App:PersonSetting')
            ->find(['name' => $name, 'person' => $this->getUser()]);
        $setting->setValue($value);
        $em->flush();
        $location = $this->generateUrl(
            'api_person_setting_get',
            ['name' => $setting->getName()]
        );

        return View::create(
            $this->dataSerialize($setting),
            Response::HTTP_CREATED,
            [
                'Location' => $location,
            ]
        );
    }

    /**
     * @ApiDoc(
     *      description="get person settings",
     *      statusCodes={
     *          200="Success"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\PersonSetting"
     * )
     * @Get("/person_setting", name="api_person_setting_cget")
     *
     * @return View
     */
    public function cgetAction()
    {
        $settings = $this
            ->getDoctrine()
            ->getRepository(PersonSetting::class)
            ->findBy([
                'person' => $this->getUser(),
            ])
        ;

        return View::create(
            $this->dataSerialize($settings),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
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
     *          404="Not Found"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\PersonSetting"
     * )
     * @Get("/person_setting/{name}", name="api_person_setting_get")
     *
     * @param string $name
     *
     * @return View
     */
    public function getAction($name)
    {
        $setting = $this
            ->getDoctrine()
            ->getRepository(PersonSetting::class)
            ->find([
                'name'   => $name,
                'person' => $this->getUser(),
            ])
        ;

        if (null === $setting) {
            throw $this->createNotFoundException();
        }

        return View::create(
            $this->dataSerialize($setting),
            Response::HTTP_OK
        );
    }
}
