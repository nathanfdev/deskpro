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

use Application\DeskPRO\Exception\ValidationException;

use Orb\Util\Arrays;

class FeedbackCategoriesController extends AbstractController
{
	####################################################################################################################
	# list
	####################################################################################################################

	public function listAction()
	{
        /**
         * @var \Application\DeskPRO\FeedbackCategories\FeedbackCategories $feedback_categories
         */

        $feedback_categories = $this->container->getSystemService('feedback_categories');

        return $this->createApiResponse(
            array(
                 'feedback_categories' => $feedback_categories->getAll()
            )
        );
	}

	####################################################################################################################
	# get
	####################################################################################################################

	public function getAction($id)
	{
		/**
		 * @var \Application\DeskPRO\FeedbackCategories\FeedbackCategories $feedback_categories
	     */

		$feedback_categories = $this->container->getSystemService('feedback_categories');
		$feedback_category = $feedback_categories->getById($id);

		if (!$feedback_category) {

			throw $this->createNotFoundException();
		}

		$returnedData = $this->getApiData($feedback_category);

		return $this->createApiResponse(
			array(
				 'feedback_category' => $returnedData
			)
		);
	}

	####################################################################################################################
	# save
	####################################################################################################################

	public function saveAction($id)
	{
		/**
		 * @var \Application\DeskPRO\FeedbackCategories\FeedbackCategories $feedback_categories
		 */

		$feedback_categories = $this->container->getSystemService('feedback_categories');

		if ($id) {

			$feedback_category = $feedback_categories->getById($id);

			if (!$feedback_category) {

				throw $this->createNotFoundException();
			}
		} else {

			$feedback_category = $feedback_categories->createNew();
		}

		$this->em->getConnection()->beginTransaction();

		try {

			$postData  = $this->in->getAll('post');
			$parent_id = $postData['feedback_category']['options']['parent_id'];

			if(!$parent_id) {

				$parent_id = '';
			}

			$feedback_category->title  = $postData['feedback_category']['title'];
			$feedback_category->parent = $feedback_categories->getParentCategory();
			$feedback_category->setOption('parent_id', $parent_id);

			$this->em->persist($feedback_category);
			$this->em->flush();

			$this->em->getConnection()->commit();

		} catch (\Exception $e) {

			$this->em->getConnection()->rollback();
			throw $e;
		}

		return $this->createApiResponse(
			array(
				 'success' => true,
				 'id'      => $feedback_category->getId(),
			)
		);
	}
}