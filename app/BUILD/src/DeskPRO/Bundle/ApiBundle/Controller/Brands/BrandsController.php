<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Brands;

use Application\DeskPRO\Entity\Brand;
use Cloud\LegacyApiBundle\Helper\CloudBrandHelper;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\BrandType;
use DeskPRO\Bundle\AppBundle\Helper\UrlHostChecker;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class BrandsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/brands")
 * @ApiDoc(target="all", section="Brands", output="Application\DeskPRO\Entity\Brand")
 * @ApiDoc(
 *     target="postAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\BrandType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\Brand"
 *      }
 *     }
 * )
 */
class BrandsController extends CrudController
{
    public static $exposeOnly   = ['get', 'list', 'post', 'delete'];
    public static $entity       = Brand::class;
    public static $listPaginate = false;
    public static $listOrder    = 'ASC';
    public static $listSort     = 'name';

    /**
     * Get resource with provided id.
     *
     * @ApiDoc(
     *      description="Get a brand",
     *      tags={"CRUD"="#ffa500"},
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="default|\d+",
     *              "description"="The id of the resource or default",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="We will return such status in case we found your entity",
     *          404="Not Found error will returned in case we can't find entity with specified ID"
     *      }
     * )
     * @Rest\Get("/{id}", requirements={"id"="default|\d+"})
     *
     * @param Request $request
     * @param int     $id
     *
     * @return View
     */
    public function getAction(Request $request, $id)
    {
        if ($id == 'default') {
            $id = $this->get('settings_resolver')->getGlobalSettings()->get('portal.default_brand');
        }

        return parent::getAction($request, $id);
    }

    /**
     * This endpoint gives an ability to create a new Brand.
     *
     * @ApiDoc(
     *     section = "Brands",
     *     resourceDescription="Operations about agent chats",
     *     description = "create a brand",
     *     statusCodes = {
     *       201 = "Brand was created",
     *     },
     *     input="DeskPRO\Bundle\AppBundle\Form\Type\BrandType",
     *     output="Application\DeskPRO\Entity\Brand"
     * )
     * @Rest\Post("")
     *
     * @param Request $request
     *
     * @throws InvalidFormException
     *
     * @return View
     */
    public function postAction(Request $request)
    {
        $this->denyAccessUnlessGranted(PermissionGroupVoter::CREATE, $this->getPermissionGroupContext($request));

        $brand = new Brand();

        $form = $this->createForm(BrandType::class, $brand);
        $form->submit($request->request->all());

        /** @var UrlHostChecker $urlHostChecker */
        $urlHostChecker = $this->get('url_host_checker');

        $url         = $urlHostChecker->simplifyUrl($brand->getUrl());
        $helpdeskUrl = $this->get('settings_resolver')->getGlobalSettings()->get('core.deskpro_url');
        $helpdeskUrl = $urlHostChecker->simplifyUrl($helpdeskUrl);

        if ($url === $helpdeskUrl) {
            throw new BadRequestHttpException(
                'Your brand URL must be a completely separate URL, it cannot be a sub-directory of any of your existing brands.'
            );
        }

        $brand->setUrl($url);

        $themeSet = new ThemeSet();
        $themeSet->setThemeId('standard');
        $this->persistModel($themeSet);

        $editThemeSet = new ThemeSet();
        $editThemeSet->setThemeId('standard');
        $this->persistModel($editThemeSet);

        $brand->setThemeSet($themeSet);
        $brand->setEditThemeSet($editThemeSet);

        $this->persistModel($brand);

        $view = View::create($this->wrap($brand), Response::HTTP_CREATED);
        $view->setLocation($this->getLocationUrl($brand, $request));

        if (defined('DPC_IS_CLOUD')) {
            CloudBrandHelper::flushBrandDomains();
        }

        return $view;
    }

    /**
     * Obviously it's an ability to erase what you've done.
     * Be careful there is no CTRL+Z shortcut.
     *
     * @ApiDoc(
     *      description="Delete a resource",
     *      tags={"CRUD"="#ffa500"},
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="The id of the resource",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Returned if everything is ok and there is no such resource anymore",
     *          404="Well, looks like either resource already deleted either it doesn't exists at all"
     *      }
     * )
     * @Rest\Delete("/{id}", requirements={"id"="\d+"})
     *
     * @param int     $id
     * @param Request $request
     *
     * @return View
     */
    public function deleteAction($id, Request $request)
    {
        if ($id == $this->get('settings_resolver')->getGlobalSettings()->get('portal.default_brand')) {
            throw $this->createAccessDeniedException('Default brand can\'be deleted');
        }
        /** @var Brand $brand */
        $brand        = $this->findEntity($id, $request);
        $themeSet     = $brand->getThemeSet();
        $editThemeSet = $brand->getEditThemeSet();

        parent::deleteAction($id, $request);

        $themeSetCopyingService = $this->get('dp.portal.designer.theme_set_copying_service');
        if ($themeSet) {
            $themeSetCopyingService->drop($themeSet);
        }
        if ($editThemeSet) {
            $themeSetCopyingService->drop($editThemeSet);
        }
    }

    /**
     * @Rest\Post("/check_url")
     *
     * @param Request $request
     *
     * @return View
     */
    public function checkBrandUrlAction(Request $request)
    {
        $url         = $this->get('url_host_checker')->simplifyUrl($request->request->get('url'));
        $brand       = $this->getRepository(Brand::class)->findOneBy(['url' => $url]);
        $helpdeskUrl = $this->get('settings_resolver')->getGlobalSettings()->get('core.deskpro_url');
        $helpdeskUrl = $this->get('url_host_checker')->simplifyUrl($helpdeskUrl);
        $response    = ['free' => !$brand];

        if ($url === $helpdeskUrl) {
            $response['free']   = false;
            $response['reason'] = 'Your brand URL must be a completely separate URL, it cannot be a sub-directory of any of your existing brands.';
        }

        return new View($this->wrap($response));
    }
}
