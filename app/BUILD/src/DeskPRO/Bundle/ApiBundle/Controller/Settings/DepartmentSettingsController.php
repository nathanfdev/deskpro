<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Settings;

use Application\DeskPRO\Entity\BrandSetting;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiUnstable;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\Settings\Widget\BrandSettings\DefaultDepartmentSettingsType;
use DeskPRO\Bundle\AppBundle\Settings\Model\Tickets\DefaultDepartmentSettings;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * API access to person settings.
 *
 * @ApiModes("all")
 * @Rest\Route("/settings/departments")
 */
class DepartmentSettingsController extends BaseController
{
    /**
     * @ApiDoc(
     *      section="Settings",
     *      description="list if default departments grouped by brand",
     *      statusCodes={
     *          200="Here you are",
     *      },
     *      output="array<DeskPRO\Bundle\AppBundle\Settings\Model\Tickets\DefaultDepartmentSettings>"
     * )
     * @ApiUnstable()
     * @Rest\Get("/default")
     *
     * @return View
     */
    public function listAction()
    {
        return new View($this->wrap($this->container->get('brand_aware_settings_resolver')->getDefaultDepartmentSettings()));
    }

    /**
     * @ApiDoc(
     *     section="Settings",
     *     description="list if default departments grouped by brand",
     *     statusCodes={
     *         204="Update successful",
     *     },
     *     input={
     *       "class"="DeskPRO\Bundle\AppBundle\Form\Type\Settings\Widget\BrandSettings\DefaultDepartmentSettingsType"
     *     }
     * )
     * @ApiUnstable()
     * @Rest\Put("/default")
     *
     * @return View
     */
    public function setSettingsAction(Request $request)
    {
        $form = $this->createForm(DefaultDepartmentSettingsType::class);
        $form->submit($request->request->all());
        if ($form->isValid()) {
            $this->setSetting($form->getData());

            return View::create(null, Response::HTTP_NO_CONTENT);
        }

        throw new InvalidFormException($form);
    }

    private function setSetting(DefaultDepartmentSettings $settings)
    {
        $department = $settings->getDepartment();
        $em         = $this->get('doctrine.orm.default_entity_manager');

        $brandSetting = $em->getRepository(
            BrandSetting::class)->findOneBy([
                'brand' => $settings->getBrand(),
                'name'  => $settings->getName(),
        ]);

        if (!$department) {
            if ($brandSetting) {
                $em->remove($brandSetting);
                $em->flush();
            }

            return;
        }

        if (!$brandSetting) {
            $brandSetting = new BrandSetting();
            $brandSetting
                ->setBrand($settings->getBrand())
                ->setName($settings->getName())
            ;
        }

        $brandSetting->setValue($department->getId());
        $em->persist($brandSetting);
        $em->flush();
    }
}
