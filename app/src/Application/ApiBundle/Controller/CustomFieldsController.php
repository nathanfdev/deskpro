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

use Application\ApiBundle\Controller\Helper\CustomFieldHelper;
use Application\ApiBundle\PermissionStrategy\AdminManagePermission;
use Application\ApiBundle\PermissionStrategy\MultiPermissions;
use Application\ApiBundle\PermissionStrategy\PassPermission;
use Application\DeskPRO\Entity\CustomFieldDefinition;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CustomFieldsController extends AbstractController implements ProtectedControllerInterface
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

	public function listAction($type)
	{
		$definitions = $this->em->getRepository('DeskPRO:CustomFieldDefinition')->findBy(array(
			'parent' => null,
			'form_type' => 'Application\DeskPRO\Form\Type\CustomFields\\' . $type . 'Type',
		));

		return $this->createApiResponse($this->getApiData($definitions, false));
	}

	####################################################################################################################
	# get-custom-field
	####################################################################################################################

	public function getCustomFieldAction($id)
	{
		return $this->createApiResponse($this->getDefinition($id)->toApiData());
	}

	####################################################################################################################
	# save-custom-field
	####################################################################################################################

	public function saveCustomFieldAction($id)
	{
		$post = $this->in->getAll('req');
		if ($id) {
			$definition = $this->getDefinition($id);
		} else {
			if (empty($post['form_type']) || empty($post['context_class'])) {
				throw new NotFoundHttpException;
			}
			$definition = new CustomFieldDefinition();
			$definition['form_type'] = 'Application\DeskPRO\Form\Type\CustomFields\\' . $post['form_type'] . 'Type';

			// todo
			$definition['context_class'] = 'Application\DeskPRO\Entity\\' . $post['context_class'];
			$definition['owner_class'] = 'Application\DeskPRO\Entity\Ticket';

			$this->em->persist($definition);
		}

		$form = $this->createForm($definition->createDefinitionType(), $definition);
		$post = array_intersect_key($post, $form->all());
		$form->submit($post);

		if ($form->isValid()) {
			$this->em->flush();
		}

		return $this->getCustomFieldAction($definition['id']);
	}

	####################################################################################################################
	# delete-custom-field
	####################################################################################################################

	public function deleteCustomFieldAction($id)
	{
		$this->em->remove($this->getDefinition($id));
		$this->em->flush();

		return $this->createApiDeleteResponse();
	}

	####################################################################################################################
	# toggleField
	####################################################################################################################

	public function toggleFieldAction($field_id, $is_enabled)
	{
		$definition = $this->getDefinition($field_id);
		$definition['is_enabled'] = $is_enabled;
		$this->em->flush();

		return $this->createSuccessResponse();
	}

	####################################################################################################################
	# save-display-order
	####################################################################################################################

	public function saveDisplayOrderAction()
	{
		$display_orders = $this->in->getCleanValueArray('display_orders', 'uint', 'discard');
		$this->em->getRepository('DeskPRO:CustomDefEntity')->updateDisplayOrders($display_orders);
		return $this->createSuccessResponse();
	}

	/**
	 * @param $id
	 * @return null|object
	 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 */
	protected function getDefinition($id)
	{
		$definition = $this->em->find('DeskPRO:CustomFieldDefinition', $id);
		if (!$definition || $definition->parent) {
			throw $this->createNotFoundException();
		}
		return $definition;
	}
}