<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Exception\ValidationException;
use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use Application\LegacyApiBundle\PermissionStrategy\MultiPermissions;
use Application\LegacyApiBundle\PermissionStrategy\PassPermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;

/**
 * @ApiModes("all")
 */
class CommunityCustomChannelsController extends AbstractController implements ProtectedControllerInterface
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
        $communityChannels = $this->container->getSystemService('community_channels_custom');

        return $this->createApiResponse(
            [
                'community_custom_channels' => $communityChannels->getAll(),
            ]
        );
    }

    //###################################################################################################################
    // get
    //###################################################################################################################

    public function getAction($id)
    {
        $communityChannelsCustomService = $this->container->getSystemService('community_channels_custom');
        $communityChannel               = $communityChannelsCustomService->getById($id);

        if (!$communityChannel) {
            throw $this->createNotFoundException();
        }

        $returnedData = $this->getApiData($communityChannel);

        return $this->createApiResponse(
            [
                'community_custom_channel' => $returnedData,
            ]
        );
    }

    //###################################################################################################################
    // save
    //###################################################################################################################

    public function saveAction($id)
    {
        $customCommunityChannelsService = $this->container->getSystemService('community_channels_custom');

        if ($id) {
            $customCommunityChannel = $customCommunityChannelsService->getById($id);

            if (!$customCommunityChannel) {
                throw $this->createNotFoundException();
            }
        } else {
            $customCommunityChannel = $customCommunityChannelsService->createNew();
        }

        $this->em->getConnection()->beginTransaction();

        try {
            $postData = $this->in->getAll('post');

            // @TODO should be refactored to usage of symfony form mechanism later, this one is quite ugly

            $parent_id =
                isset($postData['community_custom_channel']['options']) ?
                    $postData['community_custom_channel']['options']['parent_id'] : '';

            $brand = null;
            if (!empty($postData['community_custom_channel']['brand'])) {
                $brand = $this->em->getRepository(Brand::class)->find($postData['community_custom_channel']['brand']);
            }
            if (!$brand) {
                $brand = $this->get('default_brand_finder')->getDefaultBrand();
            }

            $customCommunityChannel->title  = $postData['community_custom_channel']['title'];
            $customCommunityChannel->parent = $customCommunityChannelsService->getParentCategory($brand);
            $customCommunityChannel->setOption('parent_id', $parent_id);

            $this->em->persist($customCommunityChannel);
            $this->em->flush();

            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollback();
            throw $e;
        }

        return $this->createApiResponse(
            [
                'success' => true,
                'id'      => $customCommunityChannel->getId(),
                'brand'   => $customCommunityChannel->getBrand() ? $customCommunityChannel->getBrand()->getId() : null,
            ]
        );
    }

    //###################################################################################################################
    // remove
    //###################################################################################################################

    public function removeAction($id)
    {
        $customCommunityChannelsService = $this->container->getSystemService('community_channels_custom');
        $customCommunityChannel         = $customCommunityChannelsService->getById($id);

        if (!$customCommunityChannel) {
            throw $this->createNotFoundException();
        }

        $moveTo                 = $this->in->getUint('move_to');
        $moveToCommunityChannel = $customCommunityChannelsService->getById($moveTo);

        $skipMoving = false;

        if (!$moveToCommunityChannel) {
            $skipMoving = true;
        }

        if (!$skipMoving && $moveToCommunityChannel->getId() == $customCommunityChannel->getId()) {
            throw ValidationException::create(
                'community_channel.remove.move_custom_community_channel',
                'You must choose a different community channel'
            );
        }

        $oldId = $customCommunityChannel->getId();

        $this->db->beginTransaction();

        try {
            if (!$skipMoving) {
                $this->db->executeUpdate(
                    'UPDATE custom_data_feedback SET field_id = ? WHERE field_id = ?',
                    [$moveTo, $oldId]
                );
            }

            $this->em->remove($customCommunityChannel);
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

        $customCommunityChannelsService = $this->container->getSystemService('community_channels_custom');
        $customCommunityChannelsService->updateDisplayOrders($displayOrders);

        return $this->createSuccessResponse();
    }
}
