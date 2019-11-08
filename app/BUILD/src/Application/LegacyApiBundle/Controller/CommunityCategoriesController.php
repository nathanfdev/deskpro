<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Exception\ValidationException;
use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use Application\LegacyApiBundle\PermissionStrategy\AgentPermission;
use Application\LegacyApiBundle\PermissionStrategy\MultiPermissions;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;

/**
 * @ApiModes("all")
 */
class CommunityCategoriesController extends AbstractController
{
    /**
     * {@inheritdoc}
     */
    public function getPermissionStrategy()
    {
        $multi = new MultiPermissions();
        $multi->addPermissionStrategy(new AdminManagePermission());
        $multi->addPermissionStrategy(new AgentPermission(), 'listAction');

        return $multi;
    }

    //###################################################################################################################
    // list
    //###################################################################################################################

    public function listAction()
    {
        $communityForums = $this->container->getSystemService('community_categories');

        return $this->createApiResponse(
            [
                'community_categories' => $communityForums->getAll(),
            ]
        );
    }

    //###################################################################################################################
    // get
    //###################################################################################################################

    public function getAction($id)
    {
        $communityForumsCustomService = $this->container->getSystemService('community_categories');
        $communityForum               = $communityForumsCustomService->getById($id);

        if (!$communityForum) {
            throw $this->createNotFoundException();
        }

        $returnedData = $this->getApiData($communityForum);

        return $this->createApiResponse(
            [
                'community_category' => $returnedData,
            ]
        );
    }

    //###################################################################################################################
    // save
    //###################################################################################################################

    public function saveAction($id)
    {
        $communityCategoriesService = $this->container->getSystemService('community_categories');

        if ($id) {
            $communityCategory = $communityCategoriesService->getById($id);

            if (!$communityCategory) {
                throw $this->createNotFoundException();
            }
        } else {
            $communityCategory = $communityCategoriesService->createNew();
        }

        $this->em->getConnection()->beginTransaction();

        try {
            $postData = $this->in->getAll('post');

            // @TODO should be refactored to usage of symfony form mechanism later, this one is quite ugly

            $parent_id =
                isset($postData['community_category']['options']) ?
                    $postData['community_category']['options']['parent_id'] : '';

            $brand = null;
            if (!empty($postData['community_category']['brand'])) {
                $brand = $this->em->getRepository(Brand::class)->find($postData['community_category']['brand']);
            }
            if (!$brand) {
                $brand = $this->get('default_brand_finder')->getDefaultBrand();
            }

            $communityCategory->title  = $postData['community_category']['title'];
            $communityCategory->parent = $communityCategoriesService->getParentChannel($brand);
            $communityCategory->setOption('parent_id', $parent_id);

            $this->em->persist($communityCategory);
            $this->em->flush();

            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollback();
            throw $e;
        }

        return $this->createApiResponse(
            [
                'success' => true,
                'id'      => $communityCategory->getId(),
                'brand'   => $communityCategory->getBrand() ? $communityCategory->getBrand()->getId() : null,
            ]
        );
    }

    //###################################################################################################################
    // remove
    //###################################################################################################################

    public function removeAction($id)
    {
        $communityCategoriesService = $this->container->getSystemService('community_categories');
        $communityCategory          = $communityCategoriesService->getById($id);

        if (!$communityCategory) {
            throw $this->createNotFoundException();
        }

        $moveTo                 = $this->in->getUint('move_to');
        $moveToCommunityChannel = $communityCategoriesService->getById($moveTo);

        $skipMoving = false;

        if (!$moveToCommunityChannel) {
            $skipMoving = true;
        }

        if (!$skipMoving && $moveToCommunityChannel->getId() == $communityCategory->getId()) {
            throw ValidationException::create(
                'community_forum.remove.move_community_category',
                'You must choose a different community forum'
            );
        }

        $oldId = $communityCategory->getId();

        $this->db->beginTransaction();

        try {
            if (!$skipMoving) {
                $this->db->executeUpdate(
                    'UPDATE custom_data_community_topic SET field_id = ? WHERE field_id = ?',
                    [$moveTo, $oldId]
                );
            }

            $this->em->remove($communityCategory);
            $this->em->flush();

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }

        return $this->createSuccessResponse(['old_id' => $oldId]);
    }

    //###################################################################################################################
    // save-display-order
    //###################################################################################################################

    public function saveDisplayOrderAction()
    {
        $displayOrders = $this->in->getArrayOfUInts('display_orders');

        $communityCategoriesService = $this->container->getSystemService('community_categories');
        $communityCategoriesService->updateDisplayOrders($displayOrders);

        return $this->createSuccessResponse();
    }
}
