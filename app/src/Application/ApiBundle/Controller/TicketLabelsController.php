<?php
namespace Application\ApiBundle\Controller;

use Application\DeskPRO\DependencyInjection\SystemServices\LabelDefManagerService;
use Application\DeskPRO\Labels\LabelDefManager;
use Application\DeskPRO\Labels\LabelManager;
use Symfony\Component\HttpFoundation\Request;

class TicketLabelsController extends AbstractController
{
	private $labels_type = array('tickets');

	public function listAction()
	{
		$manager = $this->_getLabelsManager();
		$labels  = $manager->getLabelsAndCounts($this->labels_type);
		// debug
		if (empty($labels)) {
			$labels = array('test_me' => 1, 'label test' => 18, 'zzz' => 15, 'xxxx' => 4);
		}
		$api_response_labels = array();
		if (!empty($labels)) {
			foreach ($labels as $label => $count) {
				$api_response_labels[] = array('label' => $label, 'count' => $count);
			}
		}
		$data['labels'] = $api_response_labels;
		return $this->createApiResponse($data);
	}

	public function addAction()
	{
		$label           = $this->in->getString('label');
		$manager         = $this->_getLabelsManager();
		$existing_labels = $manager->getLabels($this->labels_type);
		if (!in_array(strtolower($label), $existing_labels) && !in_array($label, $existing_labels)) {
			$manager->createLabelDef($label, $this->labels_type);
			return $this->createApiResponse(array(), 200);
		}
		else {
			return $this->createApiResponse(array('labels' => $existing_labels, 'label' => $label), 400);
		}
	}

	public function saveAction()
	{
		$manager   = $this->_getLabelsManager();
		$label_old = $this->in->getString('label_old');
		$label_new = $this->in->getCleanValue('label_new', 'string');
		$manager->renameLabelDef($label_old, $label_new, $this->labels_type);
		return $this->createApiResponse(array('old' => $label_old, 'new' => $label_new), 200);
	}

	public function removeAction($label)
	{
		$manager = $this->_getLabelsManager();
		try {
			if (!$manager->deleteLabelDef($label, $this->labels_type)) {
				throw $this->createNotFoundException();
			}
		}
		catch (\Exception $e) {
			throw $this->createNotFoundException();
		}

		return $this->createApiResponse(array(), 200);
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
