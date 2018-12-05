<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\Exception\ValidationException;
use Application\DeskPRO\FeedbackStatuses\FeedbackStatusEdit;
use Application\DeskPRO\FeedbackStatuses\Form\Type\FeedbackStatusType;
use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use Application\LegacyApiBundle\PermissionStrategy\MultiPermissions;
use Application\LegacyApiBundle\PermissionStrategy\PassPermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Orb\Util\Arrays;

/**
 * @ApiModes("all")
 */
class FeedbackStatusesController extends AbstractController implements ProtectedControllerInterface
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
        /*
         * @var \Application\DeskPRO\FeedbackStatuses\FeedbackStatuses
         */
        $feedback_statuses = $this->container->getSystemService('feedback_statuses');

        $active_statuses = $this->getApiData(Arrays::flatten($feedback_statuses->getActiveStatuses()));
        $closed_statuses = $this->getApiData(Arrays::flatten($feedback_statuses->getClosedStatuses()));

        return $this->createApiResponse(
            [
                 'statuses' => [
                     'active_statuses' => $active_statuses,
                     'closed_statuses' => $closed_statuses,
                 ],
            ]
        );
    }

    //##################################################################################################################
    // get
    //###################################################################################################################

    public function getAction($id)
    {
        /*
         * @var \Application\DeskPRO\FeedbackStatuses\FeedbackStatuses
         */
        $feedback_statuses = $this->container->getSystemService('feedback_statuses');
        $feedback_status   = $feedback_statuses->getById($id);

        if (!$feedback_status) {
            throw $this->createNotFoundException();
        }

        return $this->createApiResponse(['feedback_status' => $this->getApiData($feedback_status)]);
    }

    //###################################################################################################################
    // save
    //###################################################################################################################

    public function saveAction($id)
    {
        /*
         * @var \Application\DeskPRO\FeedbackStatuses\FeedbackStatuses
         */
        $feedback_statuses = $this->container->getSystemService('feedback_statuses');

        if ($id) {
            $feedback_status = $feedback_statuses->getById($id);

            if (!$feedback_status) {
                throw $this->createNotFoundException();
            }
        } else {
            $feedback_status = $feedback_statuses->createNew();
        }

        $feedback_status_edit = new FeedbackStatusEdit($feedback_status);

        $postData = $this->in->getAll('post');

        $form = $this->createForm(new FeedbackStatusType(), $feedback_status_edit, ['cascade_validation' => true]);
        $form->submit($this->deleteExtraDataFromRequest($form, $postData, 'feedback_status'), true);

        if ($form->isValid()) {
            $feedback_status_edit->save($this->em);
        } else {
            return $this->createApiValidationErrorResponse(
                $this->container->getValidator()->validate($feedback_status)
            );
        }

        return $this->createApiResponse(
            [
                 'success' => true,
                 'id'      => $feedback_status->getId(),
                 'brand'   => $feedback_status->getBrand() ? $feedback_status->getBrand()->getId() : null,
            ]
        );
    }

    //###################################################################################################################
    // remove
    //###################################################################################################################

    public function removeAction($id)
    {
        /*
         * @var \Application\DeskPRO\FeedbackStatuses\FeedbackStatuses
         */
        $feedback_statuses = $this->container->getSystemService('feedback_statuses');
        $feedback_status   = $feedback_statuses->getById($id);

        if (!$feedback_status) {
            throw $this->createNotFoundException();
        }

        $move_to                 = $this->in->getUint('move_to');
        $move_to_feedback_status = $feedback_statuses->getById($move_to);

        if (!$move_to_feedback_status) {
            throw ValidationException::create(
                'feedback_status.remove.move_feedback_statuses',
                'You must select a feedback status to move existing feedback into'
            );
        }

        if ($move_to_feedback_status->getId() == $feedback_status->getId()) {
            throw ValidationException::create(
                'feedback_status.remove.move_feedback_statuses',
                'You must choose a different feedback status'
            );
        }

        $old_id = $feedback_status->getId();

        $this->db->beginTransaction();

        try {
            $this->db->executeUpdate(
                'UPDATE feedback SET status_category_id = ? WHERE status_category_id = ?',
                [$move_to, $old_id]
            );

            $this->em->remove($feedback_status);
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

        /** @var \Application\DeskPRO\FeedbackStatuses\FeedbackStatuses $feedback_statuses */
        $feedback_statuses = $this->container->getSystemService('feedback_statuses');
        $feedback_statuses->updateDisplayOrders($display_orders);

        return $this->createSuccessResponse();
    }
}
