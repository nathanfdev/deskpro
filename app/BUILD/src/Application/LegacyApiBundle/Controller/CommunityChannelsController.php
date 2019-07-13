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
class CommunityChannelsController extends AbstractController implements ProtectedControllerInterface
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
                'feedback_type' => $returnedData,
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

        $communityChannel_edit = new CommunityChannelEdit($communityChannel);

        $form = $this->createForm(CommunityChannelType::class, $communityChannel_edit, ['cascade_validation' => true]);
        $form->submit($this->deleteExtraDataFromRequest($form, $postData, 'feedback_type'), true);

        if ($form->isValid()) {
            $communityChannel_edit->save($this->em);
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
                'feedback_type.remove.move_feedback_types',
                'You must select a feedback type to move existing feedback into'
            );
        }

        if ($move_to_community_channel->getId() == $communityChannel->getId()) {
            throw ValidationException::create(
                'feedback_type.remove.move_feedback_types',
                'You must choose a different feedback type'
            );
        }

        $old_id = $communityChannel->getId();

        $this->db->beginTransaction();

        try {
            $this->db->executeUpdate(
                'UPDATE feedback SET category_id = ? WHERE category_id = ?',
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
