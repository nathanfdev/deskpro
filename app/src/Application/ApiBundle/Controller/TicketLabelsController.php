<?php
namespace Application\ApiBundle\Controller;

use Application\DeskPRO\DependencyInjection\SystemServices\LabelDefManagerService;
use Application\DeskPRO\Labels\LabelDefManager;
use Application\DeskPRO\Labels\LabelManager;

class TicketLabelsController extends AbstractController
{
	public function listAction()
	{
		$data = array();

		$labels = $this->em->createQuery("
			SELECT label
			FROM DeskPRO:LabelTicket label
		")->execute();

		$data['labels'] = $this->getApiData($labels, false);

		return $this->createApiResponse($data);
	}

	public function getAction($id)
	{
		$label = $this->em->find('DeskPRO:LabelTicket', $id);

		if (!$label) {
			throw new $this->createNotFoundException();
		}

		$data = array();
		$data['label'] = $this->getApiData($label);

		return $this->createApiResponse($data);
	}

	public function saveAction($label)
	{
		$manager = $this->_getLabelsManager();
		$existing_labels = $manager->getLabels(['ticket']);
		if (!in_array(strtolower($label), $existing_labels) && !in_array($label, $existing_labels)) {
			$manager->createLabelDef($label,['ticket']);
		}
	}

	public function removeAction($label)
	{
		$manager = $this->_getLabelsManager();
		if (!$manager->deleteLabelDef($label)) {
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
