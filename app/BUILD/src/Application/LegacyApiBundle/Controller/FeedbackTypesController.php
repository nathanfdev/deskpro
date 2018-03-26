<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\Exception\ValidationException;
use Application\DeskPRO\FeedbackTypes\FeedbackTypeEdit;
use Application\DeskPRO\FeedbackTypes\Form\Type\FeedbackTypeType;
use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use Application\LegacyApiBundle\PermissionStrategy\MultiPermissions;
use Application\LegacyApiBundle\PermissionStrategy\PassPermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Orb\Util\Arrays;

/**
 * @ApiModes("all")
 */
class FeedbackTypesController extends AbstractController implements ProtectedControllerInterface
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
         * @var \Application\DeskPRO\FeedbackTypes\FeedbackTypes
         */
        $feedback_types = $this->container->getSystemService('feedback_types');

        return $this->createApiResponse(
            [
                 'types' => $this->getApiData(Arrays::flatten($feedback_types->getAll())),
            ]
    // get
);
    }

//##################################################################################################################
    //###################################################################################################################

    public function getAction($id)
    {
        /*
         * @var \Application\DeskPRO\FeedbackTypes\FeedbackTypes
         */
        $feedback_types = $this->container->getSystemService('feedback_types');
        $feedback_type  = $feedback_types->getById($id);

        if (!$feedback_type) {
            throw $this->createNotFoundException();
        }

        $returnedData               = $this->getApiData($feedback_type);
        $returnedData['usergroups'] = $feedback_types->getNonAgentUserGroups($feedback_type);

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
        /*
         * @var \Application\DeskPRO\FeedbackTypes\FeedbackTypes
         */
        $feedbackTypes = $this->container->getSystemService('feedback_types');

        if ($id) {
            $feedbackType = $feedbackTypes->getById($id);

            if (!$feedbackType) {
                throw $this->createNotFoundException();
            }
        } else {
            $feedbackType = $feedbackTypes->createNew();
        }

        $postData = $this->in->getAll('post');

        $feedback_type_edit = new FeedbackTypeEdit($feedbackType);

        $form = $this->createForm(FeedbackTypeType::class, $feedback_type_edit, ['cascade_validation' => true]);
        $form->submit($this->deleteExtraDataFromRequest($form, $postData, 'feedback_type'), true);

        if ($form->isValid()) {
            $feedback_type_edit->save($this->em);
        } else {
            return $this->createApiValidationErrorResponse($this->container->getValidator()->validate($feedbackType));
        }

        return $this->createApiResponse([
             'success' => true,
             'id'      => $feedbackType->getId(),
        ]);
    }

    //###################################################################################################################
    // remove
    //###################################################################################################################

    public function removeAction($id)
    {
        /*
         * @var \Application\DeskPRO\FeedbackTypes\FeedbackTypes
         */
        $feedback_types = $this->container->getSystemService('feedback_types');
        $feedback_type  = $feedback_types->getById($id);

        if (!$feedback_type) {
            throw $this->createNotFoundException();
        }

        $move_to               = $this->in->getUint('move_to');
        $move_to_feedback_type = $feedback_types->getById($move_to);

        if (!$move_to_feedback_type) {
            throw ValidationException::create(
                'feedback_type.remove.move_feedback_types',
                'You must select a feedback type to move existing feedback into'
            );
        }

        if ($move_to_feedback_type->getId() == $feedback_type->getId()) {
            throw ValidationException::create(
                'feedback_type.remove.move_feedback_types',
                'You must choose a different feedback type'
            );
        }

        $old_id = $feedback_type->getId();

        $this->db->beginTransaction();

        try {
            $this->db->executeUpdate(
                'UPDATE feedback SET category_id = ? WHERE category_id = ?',
                [$move_to, $old_id]
            );

            $this->em->remove($feedback_type);
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

        /*
         * @var \Application\DeskPRO\FeedbackTypes\FeedbackTypes
         */
        $feedback_types = $this->container->getSystemService('feedback_types');
        $feedback_types->updateDisplayOrders($display_orders);

        return $this->createSuccessResponse();
    }
}
