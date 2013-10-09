<?php
namespace Application\ApiBundle\Controller;

use Application\DeskPRO\DependencyInjection\SystemServices\LabelDefManagerService;
use Application\DeskPRO\Labels\LabelDefManager;
use Application\DeskPRO\Labels\LabelManager;

class TicketLabelsController extends AbstractController
{
	private $labels_type = array('ticket');

	public function listAction()
	{
		$manager            = $this->_getLabelsManager();
		$labels             = $manager->getLabelsAndCounts($this->labels_type);
		$data['labels']     = $this->getApiData($labels, false);
		$data['labels_raw'] = $labels;
		$data['debug']      = 'test';
		return $this->createApiResponse($data);
	}

	public function saveAction($label)
	{
		$manager         = $this->_getLabelsManager();
		$existing_labels = $manager->getLabels($this->labels_type);
		if (!in_array(strtolower($label), $existing_labels) && !in_array($label, $existing_labels)) {
			$manager->createLabelDef($label, $this->labels_type);
			$status = 200;
		}
		else {
			$status = 201;
		}
		return $this->createApiResponse(array(), $status);
	}

	public function removeAction($label)
	{
		$manager = $this->_getLabelsManager();
		if (!$manager->deleteLabelDef($label, $this->labels_type)) {
			throw $this->createNotFoundException();
		}

		return $this->createApiResponse(array(), 201);
	}

	/**
	 * @param $id
	 * @return LabelDefManager
	 * @throws
	 */
	private function _getLabelsManager()
	{
		$editor = new LabelDefManager($this->em);
		return $editor;
	}
}
