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

use Application\ApiBundle\PermissionStrategy\UserTypePermission;
use Application\DeskPRO\Banning\EmailBanEdit;
use Application\DeskPRO\Banning\EmailBans;
use Application\DeskPRO\Banning\Form\Type\EmailBanType;
use Application\DeskPRO\Banning\Form\Type\IpBanType;
use Application\DeskPRO\Banning\IpBanEdit;
use Application\DeskPRO\Entity\RoundRobin;
use Application\DeskPRO\Entity\RoundRobinAgent;
use Application\DeskPRO\EntityRepository\BanEmail;
use Application\DeskPRO\Exception\ValidationException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RoundRobinController extends AbstractController implements ProtectedControllerInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function getPermissionStrategy()
	{
		return new UserTypePermission(UserTypePermission::AGENT);
	}


	####################################################################################################################
	# list
	####################################################################################################################

	public function listAction()
	{
		$data = array();
		foreach ($this->em->getRepository('DeskPRO:RoundRobin')->findAll() as $rr) {
			$data[] = $rr->toApiData();
		}

		return $this->createApiResponse($data);
	}

	###################################################################################################################
	# get RR
	####################################################################################################################

	public function getAction($id)
	{
		/** @var $rr RoundRobin */
		if (!$rr = $this->em->getRepository('DeskPRO:RoundRobin')->find($id)) {
			throw $this->createNotFoundException();
		}

		return $this->createApiResponse($rr->toApiData());
	}

	###################################################################################################################
	# save RR
	####################################################################################################################

	public function setAction($id)
	{
		/** @var \Application\DeskPRO\EntityRepository\RoundRobin $rep */
		$rep = $this->em->getRepository('DeskPRO:RoundRobin');

		/** @var $rr RoundRobin */
		if (!$id) {
			$rr = new RoundRobin();
			$this->em->persist($rr);
		} elseif (!$rr = $rep->find($id)) {
			throw $this->createNotFoundException();
		}

		$data = $this->in->getAll('req');
		unset($data['next']);

		$rep->setAgents($rr, $data['agents']);
		unset($data['agents']);

		$rr->fromArray($data);
		$this->em->flush();

		return $this->getAction($rr['id']);
	}

	###################################################################################################################
	# delete RR
	####################################################################################################################

	public function deleteAction($id)
	{
		/** @var $rr RoundRobin */
		if (!$rr = $this->em->getRepository('DeskPRO:RoundRobin')->find($id)) {
			throw $this->createNotFoundException();
		}

		$this->em->remove($rr);
		$this->em->flush();

		return $this->createApiResponse(array());
	}

	###################################################################################################################
	# setup RR
	####################################################################################################################

	public function settingsAction()
	{
		if ($this->request->isMethod('PUT')) {
			$this->settings->setSetting('core.round_robin.enabled', $this->in->getBool('enabled'));
		}

		return $this->createApiResponse(array(
			'enabled' => (bool) $this->settings->get('core.round_robin.enabled', false)
		));
	}
}