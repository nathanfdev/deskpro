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

use Application\DeskPRO\FeedbackTypes\FeedbackTypes;
use Application\DeskPRO\Exception\ValidationException;

use Application\DeskPRO\FeedbackTypes\Form\Type\FeedbackTypeType;
use Application\DeskPRO\FeedbackTypes\FeedbackTypeEdit;

use Orb\Util\Arrays;

class FeedbackTypesController extends AbstractController
{
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

		return $this->createApiResponse(array('feedback_type' => $this->getApiData($feedback_type)));
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

		$feedback_type_edit = new FeedbackTypeEdit($feedback_type);

		$postData = $this->in->getAll('post');

		$form = $this->createForm(new FeedbackTypeType(), $feedback_type_edit, array('cascade_validation' => true));
		$form->submit($this->deleteExtraDataFromRequest($form, $postData, 'feedback_type'), true);

		if ($form->isValid()) {

			$feedback_type_edit->save($this->em);

		} else {

			throw ValidationException::create("feedback_type.save", $this->getFormValidationErrorsString($form));
		}

		return $this->createApiResponse(
			array(
				 'success' => true,
				 'id'      => $feedback_type->getId(),
			)
		);
	}
}