<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\Entity\Brand;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;

/**
 * Class BrandController.
 *
 * @ApiModes("all")
 */
class BrandController extends AbstractController
{
    public function listAction()
    {
        $brands = $this->getBrandRepo()->findAll();

        $serialized_brands = $this->container->getSerializer()->serializeArray($brands);

        return $this->createApiResponse(['brands' => $serialized_brands]);
    }

    public function showAction($id)
    {
        $brand = $this->getBrandRepo()->find($id);

        if (!$brand) {
            throw $this->createNotFoundException('could not find id="'.$id.'"');
        }

        $serialized_brand = $this->container->getSerializer()->serialize($brand);

        return $this->createApiResponse($serialized_brand);
    }

    public function saveAction($id = 0)
    {
        if ($id) {
            $brand       = $this->getBrandRepo()->find($id);
            $http_status = 200;
        } else {
            $brand       = new Brand();
            $http_status = 201;
        }

        $errors = [];

        $name = $this->in->getString('brand.name');
        if (!$name) {
            $errors[] = 'Your brand must have a name';
        }

        if (!count($errors)) {
            $brand->name = $name;

            $this->container->getEm()->persist($brand);
            $this->container->getEm()->flush();

            $serialized_brand = $this->container->getSerializer()->serialize($brand);

            return $this->createApiSuccessResponse($serialized_brand, $http_status);
        }

        return $this->createApiMultipleErrorResponse($errors);
    }

    public function removeAction($id)
    {
        if ($id == 1) {
            return $this->createApiErrorResponse('not_allowed', 'cannot delete default brand');
        }

        $brand = null;
        if ($id) {
            $brand = $this->getBrandRepo()->find($id);
        }

        if (!$brand) {
            throw $this->createNotFoundException('brand not found for id = "'.$id.'"');
        }

        $this->container->getEm()->remove($brand);
        $this->container->getEm()->flush();

        return $this->createApiSuccessResponse();
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\Brand
     */
    protected function getBrandRepo()
    {
        return $this->container->getEm()->getRepository('DeskPRO:Brand');
    }
}
