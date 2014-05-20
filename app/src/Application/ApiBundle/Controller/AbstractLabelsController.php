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
 */

namespace Application\ApiBundle\Controller;

use Application\ApiBundle\PermissionStrategy\UserTypePermission;
use Application\DeskPRO\Labels\LabelDefManager;

abstract class AbstractLabelsController extends AbstractController implements ProtectedControllerInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function getPermissionStrategy()
	{
		return new UserTypePermission(UserTypePermission::AGENT);
	}


	/**
	 * @return array
	 */
	abstract protected function getLabelsTypes();


	####################################################################################################################
	# list
	####################################################################################################################

	public function listAction()
	{
		$manager             = $this->_getLabelsManager();
		$labels              = $manager->getLabelsAndCounts($this->getLabelsTypes());
		$api_response_labels = array();
		if (!empty($labels)) {
			foreach ($labels as $label => $count) {
				$api_response_labels[] = array('label' => $label, 'count' => $count);
			}
		}
		$data['labels'] = $api_response_labels;
		return $this->createApiResponse($data);
	}


	####################################################################################################################
	# get
	####################################################################################################################

	public function getAction()
	{
		$label = $this->in->getString('label');
		return $this->createApiResponse(array(
			'label' => $label
		));
	}


	####################################################################################################################
	# add
	####################################################################################################################

	public function addAction()
	{
		$label           = $this->in->getString('label');
		$manager         = $this->_getLabelsManager();
		$existing_labels = $manager->getLabels($this->getLabelsTypes());

		if (!in_array(strtolower($label), $existing_labels) && !in_array($label, $existing_labels)) {
			$manager->createLabelDef($label, $this->getLabelsTypes());
			return $this->createApiResponse(array(), 200);
		} else {
			return $this->createApiResponse(array(), 400);
		}
	}


	####################################################################################################################
	# save
	####################################################################################################################

	public function saveAction()
	{
		$manager   = $this->_getLabelsManager();
		$label_old = $this->in->getString('label_old');
		$label_new = $this->in->getCleanValue('label_new', 'string');
		$manager->renameLabelDef($label_old, $label_new, $this->getLabelsTypes());
		return $this->createApiResponse(array(), 200);
	}


	####################################################################################################################
	# remove
	####################################################################################################################

	public function removeAction()
	{
		$label = $this->in->getString('label');
		$manager = $this->_getLabelsManager();
		try {
			if (!$manager->deleteLabelDef($label, $this->getLabelsTypes())) {
				throw $this->createNotFoundException();
			}
		}
		catch (\Exception $e) {
			throw $this->createNotFoundException();
		}

		return $this->createApiResponse(array(), 200);
	}

	####################################################################################################################

	/**
	 * @return LabelDefManager
	 */
	private function _getLabelsManager()
	{
		$editor = new LabelDefManager($this->em);
		return $editor;
	}
}
