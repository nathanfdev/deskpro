<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
use Application\DeskPRO\Hierarchy\HierarchyStructureProcessor;

use \Symfony\Component\HttpFoundation\Response;

/**
 * Operations about Ticket fields
 *
 * @SWG\Resource(
 * 	resourcePath="/ticket_fields",
 * 	description="Operations about Ticket fields",
 * 	basePath="/api"
 * )
 */
class TicketFieldsController extends AbstractController implements ProtectedControllerInterface
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


    /**
     * @return Response;
     *
     * @SWG\Api(
     * 	path="/ticket_fields",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Get list of ticket fields, including custom fields",
     * 		notes="",
     *		type="array",
     *  )
     * )
     */
	public function listAction()
	{
		$data = array();

		/** @var \Application\DeskPRO\CustomFields\TicketFieldManager $field_manager */
		$field_manager = $this->container->getSystemService('ticket_fields_manager');

		$custom_fields = $field_manager->getDefinedFields();
		$data['custom_fields']  = $this->getApiData($custom_fields, false);

		$data['product_enabled']  = $field_manager->isProductEnabled();
		$data['category_enabled'] = $field_manager->isCategoryEnabled();
		$data['priority_enabled'] = $field_manager->isPriorityEnabled();
		$data['workflow_enabled'] = $field_manager->isWorkflowEnabled();

		return $this->createApiResponse($data);
	}


    /**
     * @param $id
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @SWG\Api(
     * 	path="/ticket_fields/{id}",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Get custom ticket field by Id",
     * 		notes="",
     *		type="array",
     *      @SWG\Parameters (
     *          @SWG\Parameter(
     *				name="id",
     *				description="Custom field id",
     *				paramType="path",
     *				required=true,
     *				type="integer",
     *			),
     *      )
     *  )
     * )
     */
	public function getCustomFieldAction($id)
	{
		$field = $this->em->find('DeskPRO:CustomDefTicket', $id);
		if (!$field || $field->parent) {
			throw $this->createNotFoundException();
		}

		$data = array();
		$data['field'] = $field->toApiData();

		return $this->createApiResponse($data);
	}

    /**
     * @param $id
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Exception
     *
     * @SWG\Api(
     * 	path="/ticket_fields/{id}",
     * 	@SWG\Operation(
     * 		method="POST",
     * 		summary="Save custom ticket field by ID",
     * 		notes="All you will pass in this query will be saved",
     *		type="array",
     *      @SWG\Parameters (
     *          @SWG\Parameter(
     *				name="id",
     *				description="Custom field id",
     *				paramType="path",
     *				required=true,
     *				type="integer",
     *			),
     *      )
     *  )
     * )

     * @SWG\Api(
     * 	path="/ticket_fields",
     * 	@SWG\Operation(
     * 		method="PUT",
     * 		summary="Create custom ticket field",
     * 		notes="All you will pass in this query will be saved",
     *		type="array",
     *  )
     * )
     */
	public function saveCustomFieldAction($id)
	{
		if ($id) {
			$field = $this->em->find('DeskPRO:CustomDefTicket', $id);
			if (!$field || $field->parent) {
				throw $this->createNotFoundException();
			}
		} else {
			$field = $this->container->getTicketFieldManager()->createNewDefEntity();
			$field->handler_class = $this->in->getString('handler_class');
		}

		$post = $this->in->getAll('req');

		$helper = new CustomFieldHelper($this);
		$helper->saveFormToField($field, $post);

		if ($id) {
			return $this->createSuccessResponse(array(
				'field_id' => $field->id
			));
		} else {
			return $this->createSuccessResponse(array(
				'field_id' => $field->id,
				$this->generateUrl('api_ticket_fields_get', array('id' => $field->id))
			));
		}
	}


    /**
     * @param $id
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @SWG\Api(
     * 	path="/ticket_fields/{id}",
     * 	@SWG\Operation(
     * 		method="DELETE",
     * 		summary="Delete custom field by ID",
     * 		notes="",
     *		type="array",
     *      @SWG\Parameters (
     *          @SWG\Parameter(
     *				name="id",
     *				description="Custom field ID",
     *				paramType="path",
     *				required=true,
     *				type="integer",
     *			),
     *      )
     *  )
     * )
     */
	public function deleteCustomFieldAction($id)
	{
		$field = $this->em->find('DeskPRO:CustomDefTicket', $id);
		if (!$field || $field->parent) {
			throw $this->createNotFoundException();
		}

		$this->em->remove($field);
		$this->em->flush();

		return $this->createApiDeleteResponse();
	}

    /**
     * @param $field_id
     * @param $is_enabled
     * @return Response
     *
     * @SWG\Api(
     * 	path="/ticket_fields/set-enabled/{field_id}/{is_enabled}",
     * 	@SWG\Operation(
     * 		method="POST",
     * 		summary="Set custom field enabled/disabled",
     * 		notes="",
     *		type="array",
     *      @SWG\Parameters (
     *          @SWG\Parameter(
     *				name="id",
     *				description="Custom field ID",
     *				paramType="path",
     *				required=true,
     *				type="integer",
     *			),
     *          @SWG\Parameter(
     *				name="is_enabled",
     *				description="Enabled marker",
     *				paramType="path",
     *				required=true,
     *				type="boolean",
     *			),
     *      )
     *  )
     * )
     */
	public function toggleFieldAction($field_id, $is_enabled)
	{
		$field_manager = $this->container->getSystemService('ticket_fields_manager');
		$field_manager->setFieldEnabledById($field_id, $is_enabled);

		return $this->createSuccessResponse();
	}


	####################################################################################################################
	# list-categories
	####################################################################################################################

	public function listCategoriesAction()
	{
		$data = array();

		/** @var \Application\DeskPRO\CustomFields\TicketFieldManager $field_manager */
		$field_manager = $this->container->getSystemService('ticket_fields_manager');

		$ticket_cats = $this->container->getSystemService('ticket_categories');
		$flat_array = $ticket_cats->getFlatArray();

		$cats = array();
		foreach ($flat_array as $row) {
			$cats[] = $row['object'];
		}

		$data['categories']     = $this->getApiData($cats, false);
		$data['default_id']     = $ticket_cats->count() ? $ticket_cats->getDefaultCategory()->getId() : 0;
		$data['user_required']  = $this->settings->get('core_tickets.field_validation_ticket_cat_user_required') ? true : false;
		$data['agent_required'] = $this->settings->get('core_tickets.field_validation_ticket_cat_agent_required') ? true : false;
		$data['enabled']        = $field_manager->isCategoryEnabled();

		return $this->createApiResponse($data);
	}


	####################################################################################################################
	# save-categories
	####################################################################################################################

	public function saveCategoriesAction()
	{
		$structure  = $this->in->getArrayValue('categories');

		#------------------------------
		# Save structure
		#------------------------------

		$proc = new HierarchyStructureProcessor($this->em, 'DeskPRO:TicketCategory');
		$recs = $proc->getRecords($structure);
		$recs = $proc->saveRecords($recs, true);

		// Save status
		if (count($recs)) {
			$this->settings->setSetting('core.use_ticket_category', $this->in->getBoolInt('enabled'));
		} else {
			$this->settings->setSetting('core.use_ticket_category', '0');
		}

		#------------------------------
		# Save default
		#------------------------------

		$default_id = $this->in->getString('default_id');

		if (isset($recs[$default_id])) {
			// Get id from $recs since the id might've been one
			// generated on the client
			$id = $recs[$default_id]->id;
		} else {
			$id = '0';
		}

		$this->settings->setSetting('core.default_ticket_cat', $id);

		#------------------------------
		# Save validation settings
		#------------------------------

		$this->settings->setSetting('core_tickets.field_validation_ticket_cat_user_required', $this->in->getBoolInt('user_required'));
		$this->settings->setSetting('core_tickets.field_validation_ticket_cat_agent_required', $this->in->getBoolInt('agent_required'));

		return $this->createSuccessResponse();
	}

	####################################################################################################################
	# list-products
	####################################################################################################################

	public function listProductsAction()
	{
		$data = array();

		/** @var \Application\DeskPRO\CustomFields\TicketFieldManager $field_manager */
		$field_manager = $this->container->getSystemService('ticket_fields_manager');

		$ticket_prods = $this->container->getSystemService('products');
		$flat_array = $ticket_prods->getFlatArray();

		$cats = array();
		foreach ($flat_array as $row) {
			$cats[] = $row['object'];
		}

		$data['products']       = $this->getApiData($cats, false);
		$data['default_id']     = $ticket_prods->count() ? $ticket_prods->getDefaultProduct()->getId() : 0;
		$data['user_required']  = $this->settings->get('core_tickets.field_validation_ticket_prod_user_required') ? true : false;
		$data['agent_required'] = $this->settings->get('core_tickets.field_validation_ticket_prod_agent_required') ? true : false;
		$data['enabled']        = $field_manager->isProductEnabled();

		return $this->createApiResponse($data);
	}


	####################################################################################################################
	# save-products
	####################################################################################################################

	public function saveProductsAction()
	{
		$structure  = $this->in->getArrayValue('products');

		#------------------------------
		# Save structure
		#------------------------------

		$proc = new HierarchyStructureProcessor($this->em, 'DeskPRO:Product');
		$recs = $proc->getRecords($structure);
		$recs = $proc->saveRecords($recs, true);

		// Save status
		if (count($recs)) {
			$this->settings->setSetting('core.use_product', $this->in->getBoolInt('enabled'));
		} else {
			$this->settings->setSetting('core.use_product', '0');
		}

		#------------------------------
		# Save default
		#------------------------------

		$default_id = $this->in->getString('default_id');

		if (isset($recs[$default_id])) {
			// Get id from $recs since the id might've been one
			// generated on the client
			$id = $recs[$default_id]->id;
		} else {
			$id = '0';
		}

		$this->settings->setSetting('core.default_prod_id', $id);

		#------------------------------
		# Save validation settings
		#------------------------------

		$this->settings->setSetting('core_tickets.field_validation_ticket_prod_user_required', $this->in->getBoolInt('user_required'));
		$this->settings->setSetting('core_tickets.field_validation_ticket_prod_agent_required', $this->in->getBoolInt('agent_required'));

		return $this->createSuccessResponse();
	}


	####################################################################################################################
	# list-workflows
	####################################################################################################################

	public function listWorkflowsAction()
	{
		$data = array();

		/** @var \Application\DeskPRO\CustomFields\TicketFieldManager $field_manager */
		$field_manager = $this->container->getSystemService('ticket_fields_manager');

		$ticket_works = $this->container->getSystemService('ticket_workflows');

		$data['workflows']      = $this->getApiData($ticket_works->getAll(), false);
		$data['default_id']     = $ticket_works->count() ? $ticket_works->getDefaultWorkflow()->getId() : 0;
		$data['user_required']  = $this->settings->get('core_tickets.field_validation_ticket_work_user_required') ? true : false;
		$data['agent_required'] = $this->settings->get('core_tickets.field_validation_ticket_work_agent_required') ? true : false;
		$data['enabled']        = $field_manager->isWorkflowEnabled();

		return $this->createApiResponse($data);
	}


	####################################################################################################################
	# save-workflows
	####################################################################################################################

	public function saveWorkflowsAction()
	{
		$structure  = $this->in->getArrayValue('workflows');

		#------------------------------
		# Save structure
		#------------------------------

		$proc = new HierarchyStructureProcessor($this->em, 'DeskPRO:TicketWorkflow');
		$proc->disableHierarchy();

		$recs = $proc->getRecords($structure);
		$recs = $proc->saveRecords($recs, true);

		// Save status
		if (count($recs)) {
			$this->settings->setSetting('core.use_ticket_workflow', $this->in->getBoolInt('enabled'));
		} else {
			$this->settings->setSetting('core.use_ticket_workflow', '0');
		}

		#------------------------------
		# Save default
		#------------------------------

		$default_id = $this->in->getString('default_id');

		if (isset($recs[$default_id])) {
			// Get id from $recs since the id might've been one
			// generated on the client
			$id = $recs[$default_id]->id;
		} else {
			$id = '0';
		}

		$this->settings->setSetting('core.default_ticket_work', $id);

		#------------------------------
		# Save validation settings
		#------------------------------

		$this->settings->setSetting('core_tickets.field_validation_ticket_work_user_required', $this->in->getBoolInt('user_required'));
		$this->settings->setSetting('core_tickets.field_validation_ticket_work_agent_required', $this->in->getBoolInt('agent_required'));

		return $this->createSuccessResponse();
	}


	####################################################################################################################
	# list-priorities
	####################################################################################################################

	public function listPrioritiesAction()
	{
		$data = array();

		/** @var \Application\DeskPRO\CustomFields\TicketFieldManager $field_manager */
		$field_manager = $this->container->getSystemService('ticket_fields_manager');

		$ticket_pris = $this->container->getSystemService('ticket_priorities');

		$data['priorities']     = $this->getApiData($ticket_pris->getAll(), false);
		$data['default_id']     = $ticket_pris->count() ? $ticket_pris->getDefaultPriority()->getId() : 0;
		$data['user_required']  = $this->settings->get('core_tickets.field_validation_ticket_pri_user_required') ? true : false;
		$data['agent_required'] = $this->settings->get('core_tickets.field_validation_ticket_pri_agent_required') ? true : false;
		$data['enabled']        = $field_manager->isPriorityEnabled();

		return $this->createApiResponse($data);
	}


	####################################################################################################################
	# save-priorities
	####################################################################################################################

	public function savePrioritiesAction()
	{
		$structure  = $this->in->getArrayValue('priorities');

		#------------------------------
		# Save structure
		#------------------------------

		$proc = new HierarchyStructureProcessor($this->em, 'DeskPRO:TicketPriority', array('priority'));
		$proc->disableHierarchy();

		$recs = $proc->getRecords($structure);
		$recs = $proc->saveRecords($recs, true);

		// Save status
		if (count($recs)) {
			$this->settings->setSetting('core.use_ticket_priority', $this->in->getBoolInt('enabled'));
		} else {
			$this->settings->setSetting('core.use_ticket_priority', '0');
		}

		#------------------------------
		# Save default
		#------------------------------

		$default_id = $this->in->getString('default_id');

		if (isset($recs[$default_id])) {
			// Get id from $recs since the id might've been one
			// generated on the client
			$id = $recs[$default_id]->id;
		} else {
			$id = '0';
		}

		$this->settings->setSetting('core.default_ticket_pri', $id);

		#------------------------------
		# Save validation settings
		#------------------------------

		$this->settings->setSetting('core_tickets.field_validation_ticket_pri_user_required', $this->in->getBoolInt('user_required'));
		$this->settings->setSetting('core_tickets.field_validation_ticket_pri_agent_required', $this->in->getBoolInt('agent_required'));

		return $this->createSuccessResponse();
	}
}