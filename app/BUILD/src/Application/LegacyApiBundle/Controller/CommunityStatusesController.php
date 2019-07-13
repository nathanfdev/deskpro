<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\Community\CommunityStatusEdit;
use Application\DeskPRO\Community\Form\Type\CommunityStatusType;
use Application\DeskPRO\Exception\ValidationException;
use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use Application\LegacyApiBundle\PermissionStrategy\MultiPermissions;
use Application\LegacyApiBundle\PermissionStrategy\PassPermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Orb\Util\Arrays;

/**
 * @ApiModes("all")
 */
class CommunityStatusesController extends AbstractController implements ProtectedControllerInterface
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
        $communityStatuses = $this->container->getSystemService('community_statuses');

        $activeStatuses = $this->getApiData(Arrays::flatten($communityStatuses->getActiveStatuses()));
        $closedStatuses = $this->getApiData(Arrays::flatten($communityStatuses->getClosedStatuses()));

        return $this->createApiResponse(
            [
                 'statuses' => [
                     'active_statuses' => $activeStatuses,
                     'closed_statuses' => $closedStatuses,
                 ],
            ]
        );
    }

    //##################################################################################################################
    // get
    //###################################################################################################################

    public function getAction($id)
    {
        $communityStatusesService = $this->container->getSystemService('community_statuses');
        $communityStatus          = $communityStatusesService->getById($id);

        if (!$communityStatus) {
            throw $this->createNotFoundException();
        }

        return $this->createApiResponse(['community_status' => $this->getApiData($communityStatus)]);
    }

    //###################################################################################################################
    // save
    //###################################################################################################################

    public function saveAction($id)
    {
        $communityStatusesService = $this->container->getSystemService('community_statuses');

        if ($id) {
            $communityStatus = $communityStatusesService->getById($id);

            if (!$communityStatus) {
                throw $this->createNotFoundException();
            }
        } else {
            $communityStatus = $communityStatusesService->createNew();
        }

        $communityStatusEdit = new CommunityStatusEdit($communityStatus);

        $postData = $this->in->getAll('post');

        $form = $this->createForm(new CommunityStatusType(), $communityStatusEdit, ['cascade_validation' => true]);
        $form->submit($this->deleteExtraDataFromRequest($form, $postData, 'community_status'), true);

        if ($form->isValid()) {
            $communityStatusEdit->save($this->em);
        } else {
            return $this->createApiValidationErrorResponse(
                $this->container->getValidator()->validate($communityStatus)
            );
        }

        return $this->createApiResponse(
            [
                 'success' => true,
                 'id'      => $communityStatus->getId(),
                 'brand'   => $communityStatus->getBrand() ? $communityStatus->getBrand()->getId() : null,
            ]
        );
    }

    //###################################################################################################################
    // remove
    //###################################################################################################################

    public function removeAction($id)
    {
        $communityStatusesService = $this->container->getSystemService('community_statuses');
        $communityStatus          = $communityStatusesService->getById($id);

        if (!$communityStatus) {
            throw $this->createNotFoundException();
        }

        $moveTo                = $this->in->getUint('move_to');
        $moveToCommunityStatus = $communityStatusesService->getById($moveTo);

        if (!$moveToCommunityStatus) {
            throw ValidationException::create(
                'community_status.remove.move_community_statuses',
                'You must select a community status to move existing community topic into'
            );
        }

        if ($moveToCommunityStatus->getId() == $communityStatus->getId()) {
            throw ValidationException::create(
                'community_status.remove.move_community_statuses',
                'You must choose a different community status'
            );
        }

        $old_id = $communityStatus->getId();

        $this->db->beginTransaction();

        try {
            $this->db->executeUpdate(
                'UPDATE community_topics SET status_category_id = ? WHERE status_category_id = ?',
                [$moveTo, $old_id]
            );

            $this->em->remove($communityStatus);
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
        $displayOrders = $this->in->getArrayOfUInts('display_orders');

        /** @var \Application\DeskPRO\Community\CommunityStatuses $communityStatusesService */
        $communityStatusesService = $this->container->getSystemService('community_statuses');
        $communityStatusesService->updateDisplayOrders($displayOrders);

        return $this->createSuccessResponse();
    }
}
