<?php

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\Entity\Brand;
use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use Application\LegacyApiBundle\PermissionStrategy\MultiPermissions;
use Application\LegacyApiBundle\PermissionStrategy\PassPermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;

/**
 * @ApiModes("all")
 */
class TicketBrandsController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPermissionStrategy()
    {
        $multi = new MultiPermissions();
        $multi->addPermissionStrategy(new AdminManagePermission());
        $multi->addPermissionStrategy(new PassPermission(), 'listAction');

        return $multi;
    }

    //###################################################################################################################
    // list
    //###################################################################################################################

    public function listAction()
    {
        $data = [];

        /** @var Brand[] $brandsObjects */
        $brandsObjects = $this->em->getRepository(Brand::class)->findBy([], ['name' => 'ASC']);

        $brands = [];
        foreach ($brandsObjects as $brand) {
            $r = $brand->toApiData(true, false);

            $brands[] = $r;
        }

        $data['brands'] = $brands;

        return $this->createApiResponse($data);
    }
}
