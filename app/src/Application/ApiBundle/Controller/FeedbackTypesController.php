<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage ApiBundle
 */

namespace Application\ApiBundle\Controller;

use Application\ApiBundle\PermissionStrategy\AdminManagePermission;
use Application\ApiBundle\PermissionStrategy\MultiPermissions;
use Application\ApiBundle\PermissionStrategy\PassPermission;
use Application\DeskPRO\Exception\ValidationException;
use Application\DeskPRO\FeedbackTypes\FeedbackTypeEdit;
use Application\DeskPRO\FeedbackTypes\Form\Type\FeedbackTypeType;
use Orb\Util\Arrays;

class FeedbackTypesController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritDoc}
     */
    public function getPermissionStrategy()
    {
        $multi = new MultiPermissions();
        $multi->addPermissionStrategy(new AdminManagePermission());
        $multi->addPermissionStrategy(new PassPermission(), 'listAction');

        return $multi;
    }


    ####################################################################################################################
    # list
    ####################################################################################################################

    public function listAction()
    {
        /**
         * @var \Application\DeskPRO\FeedbackTypes\FeedbackTypes $feedback_types
         */

        $feedback_types = $this->container->getSystemService('feedback_types');

        return $this->createApiResponse(
            array(
                 'types' => $this->getApiData(Arrays::flatten($feedback_types->getAll()))
            )
        );
    }

    ###################################################################################################################
    # get
    ####################################################################################################################

    public function getAction($id)
    {
        /**
         * @var \Application\DeskPRO\FeedbackTypes\FeedbackTypes $feedback_types
         */

        $feedback_types = $this->container->getSystemService('feedback_types');
        $feedback_type  = $feedback_types->getById($id);

        if (!$feedback_type) {

            throw $this->createNotFoundException();
        }

        $returnedData               = $this->getApiData($feedback_type);
        $returnedData['usergroups'] = $feedback_types->getNonAgentUserGroups($feedback_type);

        return $this->createApiResponse(
            array(
                 'feedback_type' => $returnedData
            )
        );
    }

    ####################################################################################################################
    # save
    ####################################################################################################################

    public function saveAction($id)
    {
        /**
         * @var \Application\DeskPRO\FeedbackTypes\FeedbackTypes $feedback_types
         */

        $feedback_types = $this->container->getSystemService('feedback_types');

        if ($id) {

            $feedback_type = $feedback_types->getById($id);

            if (!$feedback_type) {

                throw $this->createNotFoundException();
            }
        } else {

            $feedback_type = $feedback_types->createNew();
        }

        $postData = $this->in->getAll('post');

        $feedback_type_edit = new FeedbackTypeEdit($feedback_type);

        $form = $this->createForm(new FeedbackTypeType(), $feedback_type_edit, array('cascade_validation' => true));
        $form->submit($this->deleteExtraDataFromRequest($form, $postData, 'feedback_type'), true);

        if ($form->isValid()) {

            $feedback_type_edit->save($this->em);

        } else {
            return $this->createApiValidationErrorResponse($this->container->getValidator()->validate($feedback_type));
        }

        return $this->createApiResponse(
            array(
                 'success' => true,
                 'id'      => $feedback_type->getId(),
            )
        );
    }

    ####################################################################################################################
    # remove
    ####################################################################################################################

    public function removeAction($id)
    {
        /**
         * @var \Application\DeskPRO\FeedbackTypes\FeedbackTypes $feedback_types
         */

        $feedback_types = $this->container->getSystemService('feedback_types');
        $feedback_type   = $feedback_types->getById($id);

        if (!$feedback_type) {

            throw $this->createNotFoundException();
        }

        $move_to               = $this->in->getUint('move_to');
        $move_to_feedback_type = $feedback_types->getById($move_to);

        if (!$move_to_feedback_type) {

            throw ValidationException::create(
                "feedback_type.remove.move_feedback_types",
                "You must select a feedback type to move existing feedback into"
            );
        }

        if ($move_to_feedback_type->getId() == $feedback_type->getId()) {

            throw ValidationException::create(
                "feedback_type.remove.move_feedback_types",
                "You must choose a different feedback type"
            );
        }

        $old_id = $feedback_type->getId();

        $this->db->beginTransaction();

        try {

            $this->db->executeUpdate(
                "UPDATE feedback SET category_id = ? WHERE category_id = ?",
                array($move_to, $old_id)
            );

            $this->em->remove($feedback_type);
            $this->em->flush();

            $this->db->commit();

        } catch(\Exception $e) {

            $this->db->rollback();
            throw $e;
        }

        return $this->createSuccessResponse(array('old_id' => $old_id));
    }

    ####################################################################################################################
    # save-display-order
    ####################################################################################################################

    public function saveDisplayOrderAction()
    {
        $display_orders = $this->in->getArrayOfUInts('display_orders');

        /**
         * @var \Application\DeskPRO\FeedbackTypes\FeedbackTypes $feedback_types
         */

        $feedback_types = $this->container->getSystemService('feedback_types');
        $feedback_types->updateDisplayOrders($display_orders);

        return $this->createSuccessResponse();
    }
}
