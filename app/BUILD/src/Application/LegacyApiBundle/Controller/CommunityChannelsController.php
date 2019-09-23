<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\Community\CommunityChannelEdit;
use Application\DeskPRO\Community\Form\Type\CommunityChannelType;
use Application\DeskPRO\Exception\ValidationException;
use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use Application\LegacyApiBundle\PermissionStrategy\MultiPermissions;
use Application\LegacyApiBundle\PermissionStrategy\PassPermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Orb\Util\Arrays;

/**
 * @ApiModes("all")
 */
class CommunityChannelsController extends AbstractController
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
        $communityChannels = $this->container->getSystemService('community_channels');

        return $this->createApiResponse(
            [
                'types' => $this->getApiData(Arrays::flatten($communityChannels->getAll())),
            ]
        );
    }

//##################################################################################################################
    //###################################################################################################################

    public function getAction($id)
    {
        $communityChannels = $this->container->getSystemService('community_channels');
        $communityChannel  = $communityChannels->getById($id);

        if (!$communityChannel) {
            throw $this->createNotFoundException();
        }

        $returnedData               = $this->getApiData($communityChannel);
        $returnedData['usergroups'] = $communityChannels->getNonAgentUserGroups($communityChannel);

        return $this->createApiResponse(
            [
                'community_channel' => $returnedData,
            ]
        );
    }

    //###################################################################################################################
    // save
    //###################################################################################################################

    public function saveAction($id)
    {
        $communityChannels = $this->container->getSystemService('community_channels');

        if ($id) {
            $communityChannel = $communityChannels->getById($id);

            if (!$communityChannel) {
                throw $this->createNotFoundException();
            }
        } else {
            $communityChannel = $communityChannels->createNew();
        }

        $postData = $this->in->getAll('post');

        $communityChannelEdit = new CommunityChannelEdit($communityChannel);

        $form = $this->createForm(CommunityChannelType::class, $communityChannelEdit, ['cascade_validation' => true]);
        $form->submit($this->deleteExtraDataFromRequest($form, $postData, 'community_channel'), true);

        if ($form->isValid()) {
            $communityChannelEdit->save($this->em);
        } else {
            return $this->createApiValidationErrorResponse($this->container->getValidator()->validate($communityChannel));
        }

        return $this->createApiResponse([
            'success' => true,
            'id'      => $communityChannel->getId(),
            'brand'   => $communityChannel->getBrand() ? $communityChannel->getBrand()->getId() : null,
        ]);
    }

    //###################################################################################################################
    // remove
    //###################################################################################################################

    public function removeAction($id)
    {
        $communityChannels = $this->container->getSystemService('community_channels');
        $communityChannel  = $communityChannels->getById($id);

        if (!$communityChannel) {
            throw $this->createNotFoundException();
        }

        $move_to                   = $this->in->getUint('move_to');
        $move_to_community_channel = $communityChannels->getById($move_to);

        if (!$move_to_community_channel) {
            throw ValidationException::create(
                'community_channel.remove.move_community_channels',
                'You must select a community channel to move existing community topics into'
            );
        }

        if ($move_to_community_channel->getId() == $communityChannel->getId()) {
            throw ValidationException::create(
                'community_channel.remove.move_community_channels',
                'You must choose a different community channels'
            );
        }

        $old_id = $communityChannel->getId();

        $this->db->beginTransaction();

        try {
            $this->db->executeUpdate(
                'UPDATE community_topics SET channel_id = ? WHERE channel_id = ?',
                [$move_to, $old_id]
            );

            $this->em->remove($communityChannel);
            $this->em->flush();

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }

        return $this->createSuccessResponse(['old_id' => $old_id]);
    }

    //###################################################################################################################
    // save-display-order
    //###################################################################################################################

    public function saveDisplayOrderAction()
    {
        $display_orders = $this->in->getArrayOfUInts('display_orders');

        $communityChannels = $this->container->getSystemService('community_channels');
        $communityChannels->updateDisplayOrders($display_orders);

        return $this->createSuccessResponse();
    }
}
