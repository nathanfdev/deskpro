<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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
use Application\DeskPRO\FeedbackStatuses\FeedbackStatusEdit;
use Application\DeskPRO\FeedbackStatuses\Form\Type\FeedbackStatusType;
use Orb\Util\Arrays;

class FeedbackStatusesController extends AbstractController implements ProtectedControllerInterface
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
         * @var \Application\DeskPRO\FeedbackStatuses\FeedbackStatuses $feedback_statuses
         */

        $feedback_statuses = $this->container->getSystemService('feedback_statuses');

		$active_statuses = $this->getApiData(Arrays::flatten($feedback_statuses->getActiveStatuses()));
        $closed_statuses = $this->getApiData(Arrays::flatten($feedback_statuses->getClosedStatuses()));

        return $this->createApiResponse(
            array(
                 'statuses' => array(
                     'active_statuses' => $active_statuses,
                     'closed_statuses' => $closed_statuses,
                 )
            )
        );
	}

	###################################################################################################################
	# get
	####################################################################################################################

	public function getAction($id)
	{
		/**
		 * @var \Application\DeskPRO\FeedbackStatuses\FeedbackStatuses $feedback_statuses
		 */

		$feedback_statuses = $this->container->getSystemService('feedback_statuses');
		$feedback_status   = $feedback_statuses->getById($id);

		if (!$feedback_status) {
			throw $this->createNotFoundException();
		}

		return $this->createApiResponse(array('feedback_status' => $this->getApiData($feedback_status)));
	}

	####################################################################################################################
	# save
	####################################################################################################################

	public function saveAction($id)
	{
		/**
		 * @var \Application\DeskPRO\FeedbackStatuses\FeedbackStatuses $feedback_statuses
		 */

		$feedback_statuses = $this->container->getSystemService('feedback_statuses');

		if ($id) {

			$feedback_status   = $feedback_statuses->getById($id);

			if (!$feedback_status) {
				throw $this->createNotFoundException();
			}
		} else {

			$feedback_status = $feedback_statuses->createNew();
		}

		$feedback_status_edit = new FeedbackStatusEdit($feedback_status);

		$postData      = $this->in->getAll('post');

		$form = $this->createForm(new FeedbackStatusType(), $feedback_status_edit, array('cascade_validation' => true));
		$form->submit($this->deleteExtraDataFromRequest($form, $postData, 'feedback_status'), true);

		if ($form->isValid()) {

			$feedback_status_edit->save($this->em);
		}
		else {

			return $this->createApiValidationErrorResponse(
				$this->container->getValidator()->validate($feedback_status)
			);
		}

		return $this->createApiResponse(
			array(
				 'success'     => true,
				 'id'          => $feedback_status->getId(),
			)
		);
	}

	####################################################################################################################
	# remove
	####################################################################################################################

	public function removeAction($id)
	{
		/**
		 * @var \Application\DeskPRO\FeedbackStatuses\FeedbackStatuses $feedback_statuses
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
				"feedback_status.remove.move_feedback_statuses",
				"You must select a feedback status to move existing feedback into"
			);
		}

		if ($move_to_feedback_status->getId() == $feedback_status->getId()) {

			throw ValidationException::create(
				"feedback_status.remove.move_feedback_statuses",
				"You must choose a different feedback status"
			);
		}

		$old_id = $feedback_status->getId();

		$this->db->beginTransaction();

		try {

			$this->db->executeUpdate(
				"UPDATE feedback SET status_category_id = ? WHERE status_category_id = ?",
				array($move_to, $old_id)
			);

			$this->em->remove($feedback_status);
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
		 * @var \Application\DeskPRO\FeedbackStatuses\FeedbackStatuses $feedback_statuses
		 */

		$feedback_statuses = $this->container->getSystemService('feedback_statuses');
		$feedback_statuses->updateDisplayOrders($display_orders);

		return $this->createSuccessResponse();
	}
}