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
use Application\DeskPRO\EntityRepository\LabelDef;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LabelsController extends AbstractController implements ProtectedControllerInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function getPermissionStrategy()
	{
		return new UserTypePermission(UserTypePermission::AGENT);
	}

	/**
	 * get settings
	 */
	public function getSettingsAction($type)
	{
		$rep = $this->rep();
		if (!$rep::valid($type)) {
			throw new NotFoundHttpException;
		}

		return $this->createApiResponse(array(
			'agent_can_create' => (bool) $this->settings->get(sprintf('labels.%s.agent_can_create', $type), false),
		));
	}

	/**
	 * get settings
	 */
	public function setSettingsAction($type)
	{
		$rep = $this->rep();
		if (!$rep::valid($type)) {
			throw new NotFoundHttpException;
		}

		$this->settings->setSetting(
			sprintf('labels.%s.agent_can_create', $type),
			$this->in->getBool('agent_can_create')
		);
		return $this->getSettingsAction($type);
	}

	public function getDefinitionsAction()
	{
		return $this->createApiResponse($this->rep()->getAllDefinitions());
	}

	####################################################################################################################
	# update
	####################################################################################################################

	public function updateDefinitionAction()
	{
		$old = $this->in->getArrayValue('old');
		if (!$new = $this->in->getArrayValue('new')) {
			throw new NotFoundHttpException;
		}

		if (!isset($new['label_type']) || !isset($new['label']) || !isset($new['color'])) {
			throw new NotFoundHttpException;
		}

		$rep = $this->rep();

		$oldLabel = null;
		if (!$old) {
			if (!$definition = $rep->getDefinition($new['label_type'], $new['label'])) {
				$definition = new \Application\DeskPRO\Entity\LabelDef($new);
				$this->em->persist($definition);
				$this->em->flush();
				return $this->createApiResponse($definition->toApiData());
			}

			$oldLabel = $definition['label'];
		} else {
			if (!$definition = $rep->getDefinition($old['label_type'], $old['label'])) {
				throw new NotFoundHttpException;
			}

			if ($exist = $rep->getDefinition($new['label_type'], $new['label'])) {
				$this->em->remove($exist);
				$this->em->flush();
			}

			$oldLabel = $old['label'];
		}

		$definition['label'] = trim($new['label']);
		$definition['color'] = $new['color'];
		$this->em->flush();

		$rep->renameLabelDef(trim($oldLabel), trim($new['label']), $new['color'], $definition['label_type']);
		return $this->createApiResponse($definition->toApiData());
	}


	####################################################################################################################
	# remove
	####################################################################################################################

	public function deleteDefinitionAction()
	{
		$label = $this->in->getString('label');
		$type = $this->in->getString('label_type');
		try {
			if (!$definition = $this->rep()->getDefinition($type, $label)) {
				throw $this->createNotFoundException();
			}
			$this->rep()->deleteDefinition($definition);
		}
		catch (\Exception $e) {
			throw $this->createNotFoundException();
		}

		return $this->createApiResponse(array(), 200);
	}

	/**
	 * @return LabelDef
	 */
	protected function rep()
	{
		return $this->em->getRepository('DeskPRO:LabelDef');
	}
}
