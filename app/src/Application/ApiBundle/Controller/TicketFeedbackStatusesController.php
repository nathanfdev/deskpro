<?php
namespace Application\ApiBundle\Controller;

use Application\DeskPRO\Feedback\Statuses;

class TicketFeedbackStatusesController extends AbstractController
{
	private $manager;

	public function listAction()
	{
		//TODO: implement
		$statuses         = array(
			array('title' => 'Active Enabled', 'is_enabled' => true, 'is_active' => true),
			array('title' => 'Active 2', 'is_enabled' => false, 'is_active' => true),
			array('title' => 'Active 3', 'is_enabled' => false, 'is_active' => true),
			array('title' => 'Closed 1', 'is_enabled' => false, 'is_active' => false),
			array('title' => 'Closed Enabled', 'is_enabled' => true, 'is_active' => false),
			array('title' => 'Closed 2', 'is_enabled' => false, 'is_active' => false),
			array('title' => 'Closed 3', 'is_enabled' => true, 'is_active' => false),
		);
		$data['statuses'] = $statuses;
		return $this->createApiResponse($data);
	}

	public function saveAction()
	{
		//TODO: implement
	}

	public function addAction()
	{
		//TODO: implement
	}

	public function removeAction($id)
	{
		//TODO: implement
	}

	public function switchAction($id)
	{
		//TODO: implement
	}

	public function orderAction()
	{
		//TODO: implement
	}

	protected function getManager()
	{
		if (empty($this->manager)) {
			$this->manager = new Statuses($this->em);
		}
		return $this->manager;
	}
}
