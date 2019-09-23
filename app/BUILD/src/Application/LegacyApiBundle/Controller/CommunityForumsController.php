<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\Community\CommunityForumEdit;
use Application\DeskPRO\Community\Form\Type\CommunityForumType;
use Application\DeskPRO\Exception\ValidationException;
use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use Application\LegacyApiBundle\PermissionStrategy\MultiPermissions;
use Application\LegacyApiBundle\PermissionStrategy\PassPermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Orb\Util\Arrays;

/**
 * @ApiModes("all")
 */
class CommunityForumsController extends AbstractController implements ProtectedControllerInterface
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
        $communityForums = $this->container->getSystemService('community_forums');

        return $this->createApiResponse(
            [
                'types' => $this->getApiData(Arrays::flatten($communityForums->getAll())),
            ]
        );
    }

//##################################################################################################################
    //###################################################################################################################

    public function getAction($id)
    {
        $communityForums = $this->container->getSystemService('community_forums');
        $communityForum  = $communityForums->getById($id);

        if (!$communityForum) {
            throw $this->createNotFoundException();
        }

        $returnedData               = $this->getApiData($communityForum);
        $returnedData['usergroups'] = $communityForums->getNonAgentUserGroups($communityForum);

        return $this->createApiResponse(
            [
                'community_forum' => $returnedData,
            ]
        );
    }

    //###################################################################################################################
    // save
    //###################################################################################################################

    public function saveAction($id)
    {
        $communityForums = $this->container->getSystemService('community_forums');

        if ($id) {
            $communityForum = $communityForums->getById($id);

            if (!$communityForum) {
                throw $this->createNotFoundException();
            }
        } else {
            $communityForum = $communityForums->createNew();
        }

        $postData = $this->in->getAll('post');

        $communityForumEdit = new CommunityForumEdit($communityForum);

        $form = $this->createForm(CommunityForumType::class, $communityForumEdit, ['cascade_validation' => true]);
        $form->submit($this->deleteExtraDataFromRequest($form, $postData, 'community_forum'), true);

        if ($form->isValid()) {
            $communityForumEdit->save($this->em);
        } else {
            return $this->createApiValidationErrorResponse($this->container->getValidator()->validate($communityForum));
        }

        return $this->createApiResponse([
            'success' => true,
            'id'      => $communityForum->getId(),
            'brand'   => $communityForum->getBrand() ? $communityForum->getBrand()->getId() : null,
        ]);
    }

    //###################################################################################################################
    // remove
    //###################################################################################################################

    public function removeAction($id)
    {
        $communityForums = $this->container->getSystemService('community_forums');
        $communityForum  = $communityForums->getById($id);

        if (!$communityForum) {
            throw $this->createNotFoundException();
        }

        $move_to                   = $this->in->getUint('move_to');
        $move_to_community_forum = $communityForums->getById($move_to);

        if (!$move_to_community_forum) {
            throw ValidationException::create(
                'community_forum.remove.move_community_forums',
                'You must select a community forum to move existing community topics into'
            );
        }

        if ($move_to_community_forum->getId() == $communityForum->getId()) {
            throw ValidationException::create(
                'community_forum.remove.move_community_forums',
                'You must choose a different community forums'
            );
        }

        $old_id = $communityForum->getId();

        $this->db->beginTransaction();

        try {
            $this->db->executeUpdate(
                'UPDATE community_topics SET forum_id = ? WHERE forum_id = ?',
                [$move_to, $old_id]
            );

            $this->em->remove($communityForum);
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

        $communityForums = $this->container->getSystemService('community_forums');
        $communityForums->updateDisplayOrders($display_orders);

        return $this->createSuccessResponse();
    }
}
