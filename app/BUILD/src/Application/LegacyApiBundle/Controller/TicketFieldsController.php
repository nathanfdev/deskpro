<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\Entity\Product;
use Application\DeskPRO\Entity\TicketCategory;
use Application\DeskPRO\Entity\TicketLayout;
use Application\DeskPRO\Entity\TicketPriority;
use Application\DeskPRO\Hierarchy\HierarchyStructureProcessor;
use Application\DeskPRO\TicketLayout\LayoutField;

use Application\LegacyApiBundle\HttpFoundation\JsonResponse;
use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use Application\LegacyApiBundle\PermissionStrategy\MultiPermissions;
use Application\LegacyApiBundle\PermissionStrategy\PassPermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Application\DeskPRO\CustomFields\Form;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Operations about Ticket fields.
 *
 * SWG\Resource(
 * 	resourcePath="/ticket_fields",
 * 	description="Operations about Ticket fields",
 * 	basePath="/api"
 * )
 *
 * @ApiModes("all")
 */
class TicketFieldsController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPermissionStrategy()
    {
        $multi = new MultiPermissions();
        $multi->addPermissionStrategy(new AdminManagePermission());
        $multi->addPermissionStrategy(new PassPermission(), 'listAction');

        return $multi;
    }

    //###################################################################################################################
    // list
    //###################################################################################################################

    /**
     * @return JsonResponse;
     *
     * SWG\Api(
     * 	path="/ticket_fields",
     * 	SWG\Operation(
     * 		method="GET",
     * 		summary="Get list of ticket fields, including custom fields",
     * 		notes="",
     *		type="array",
     *  )
     * )
     */
    public function listAction()
    {
        $data = [];

        /** @var \Application\DeskPRO\CustomFields\TicketFieldManager $field_manager */
        $field_manager = $this->container->getSystemService('ticket_fields_manager');

        $custom_fields         = $field_manager->getDefinedFields();
        $data['custom_fields'] = $this->getApiData($custom_fields, false);

        $data['product_enabled']  = $field_manager->isProductEnabled();
        $data['category_enabled'] = $field_manager->isCategoryEnabled();
        $data['priority_enabled'] = $field_manager->isPriorityEnabled();
        $data['workflow_enabled'] = $field_manager->isWorkflowEnabled();

        return $this->createApiResponse($data);
    }

    //###################################################################################################################
    // get-custom-field
    //###################################################################################################################

    /**
     * @param $id
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return JsonResponse
     *
     *
     * SWG\Api(
     * 	path="/ticket_fields/{id}",
     * 	SWG\Operation(
     * 		method="GET",
     * 		summary="Get custom ticket field by Id",
     * 		notes="",
     *		type="array",
     *      SWG\Parameters (
     *          SWG\Parameter(
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
        /** @var CustomDefTicket $field */
        $field = $this->em->find('DeskPRO:CustomDefTicket', $id);
        if (!$field || $field->parent) {
            throw $this->createNotFoundException();
        }

        $appAliases = [];
        $adminAliases = [];
        foreach ($field->getAliases() as $alias) {
            $appInstance = $alias->getAppInstance();
            if ($appInstance) {
                $appAliases[] = [
                    'entity' => 'app',
                    'appId' => $appInstance->getApp()->getId(),
                    'appName' => $appInstance->getApp()->getManifest()->getTitle(),
                ];
            } else {
                $adminAliases[] = $alias->getQualifiedName();
            }
        }

        // a custom field can have at most one admin alias.
        // if however we discover more than one (something broke) then we should throw
        $data = null;
        $nrAdminAliases = count($adminAliases);
        if ($nrAdminAliases === 1) {
            $data          = [
                'field' => array_merge($field->toApiData(), ['alias' => $adminAliases[0]]) ,
                'referencedBy' => $appAliases
            ];
        } else if ($nrAdminAliases === 0) {
            $data          = [
                'field' => $field->toApiData(),
                'referencedBy' => $appAliases
            ];
        }

        if (is_array($data)) {
            return $this->createApiResponse($data);
        }

        $msg = sprintf('Found more than one admin aliases for field id: %s: %s', $id, implode(', ', $adminAliases));
        throw new \RuntimeException($msg);
    }

    //###################################################################################################################
    // save-custom-field
    //###################################################################################################################

    /**
     * @param $id
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Exception
     *
     * @return JsonResponse
     *
     *
     * SWG\Api(
     * 	path="/ticket_fields/{id}",
     * 	SWG\Operation(
     * 		method="POST",
     * 		summary="Save custom ticket field by ID",
     * 		notes="All you will pass in this query will be saved",
     *		type="array",
     *      SWG\Parameters (
     *          SWG\Parameter(
     *				name="id",
     *				description="Custom field id",
     *				paramType="path",
     *				required=true,
     *				type="integer",
     *			),
     *      )
     *  )
     * )

     * SWG\Api(
     * 	path="/ticket_fields",
     * 	SWG\Operation(
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
            $field                = $this->container->getTicketFieldManager()->createNewDefEntity();
            $field->handler_class = $this->in->getString('handler_class');
        }

        $post = $this->in->getAll('req');
        if (empty($post['title'])) {
            return $this->createApiErrorResponse('validation_error', 'Empty title');
        }

        // there is no separate api to change a single alias, must change all aliases
        // so we either add or remove the new alias to/from the list of existing aliases
        if ($id && array_key_exists('alias', $post)) {
            $newAdminAlias = trim($post['alias']); //normalize the alias
            $existingAdminAlias = null;
            $existingAliases = [];

            /** @var CustomDefTicket $field */
            foreach ($field->getAliases() as $aliasObject) {
                $appInstance = $aliasObject->getAppInstance();
                $alias = $aliasObject->getQualifiedName();

                if (! $appInstance) {
                    if (is_null($existingAdminAlias)) {
                        $existingAdminAlias = $alias;
                    } else {
                        $msg = sprintf(
                            'Found more than one admin aliases for field id: %s: %s',
                            $id, implode(', ', [$existingAdminAlias, $alias])
                        );
                        throw new \RuntimeException($msg);
                    }
                } else {
                    $existingAliases[] = $alias;
                }
            }

            $post['alias'] = empty($newAdminAlias) ? $existingAliases : array_merge([$newAdminAlias], $existingAliases);
        }

        $container = $this->getContainer();
        $helper = new Form\FormHelper($container->getEm(), $container->getFormFactory());
        $helper->saveFormToField($field, $post);

        if ($id) {
            return $this->createSuccessResponse([
                'field_id' => $field->id,
            ]);
        }

        return $this->createSuccessResponse([
            'field_id' => $field->id,
            $this->generateUrl('api_ticket_fields_get', ['id' => $field->id]),
        ]);
    }

    //###################################################################################################################
    // delete-custom-field
    //###################################################################################################################

    /**
     * @param $id
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return JsonResponse
     *
     *
     * SWG\Api(
     * 	path="/ticket_fields/{id}",
     * 	SWG\Operation(
     * 		method="DELETE",
     * 		summary="Delete custom field by ID",
     * 		notes="",
     *		type="array",
     *      SWG\Parameters (
     *          SWG\Parameter(
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

    //###################################################################################################################
    // toggleField
    //###################################################################################################################

    /**
     * @param $field_id
     * @param $is_enabled
     *
     * @return JsonResponse
     *
     * SWG\Api(
     * 	path="/ticket_fields/set-enabled/{field_id}/{is_enabled}",
     * 	SWG\Operation(
     * 		method="POST",
     * 		summary="Set custom field enabled/disabled",
     * 		notes="",
     *		type="array",
     *      SWG\Parameters (
     *          SWG\Parameter(
     *				name="id",
     *				description="Custom field ID",
     *				paramType="path",
     *				required=true,
     *				type="integer",
     *			),
     *          SWG\Parameter(
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

    //###################################################################################################################
    // list-categories
    //###################################################################################################################

    public function listCategoriesAction()
    {
        $data = [];

        /** @var \Application\DeskPRO\CustomFields\TicketFieldManager $field_manager */
        $field_manager = $this->container->getSystemService('ticket_fields_manager');

        $ticket_cats = $this->container->getSystemService('ticket_categories');
        $flat_array  = $ticket_cats->getFlatArray();

        $cats = [];
        foreach ($flat_array as $row) {
            $cats[] = $row['object'];
        }

        $data['categories']     = $this->getApiData($cats, false);
        $data['default_id']     = $ticket_cats->getDefaultCategory() ? $ticket_cats->getDefaultCategory()->getId() : 0;
        $data['user_required']  = $this->settings->get('core_tickets.field_validation_ticket_cat_user_required') ? true : false;
        $data['agent_required'] = $this->settings->get('core_tickets.field_validation_ticket_cat_agent_required') ? true : false;
        $data['enabled']        = $field_manager->isCategoryEnabled();

        return $this->createApiResponse($data);
    }

    //###################################################################################################################
    // save-categories
    //###################################################################################################################

    public function saveCategoriesAction()
    {
        $structure = $this->in->getArrayValue('categories');

        //------------------------------
        // Save structure
        //------------------------------

        $proc = new HierarchyStructureProcessor($this->em, 'DeskPRO:TicketCategory');
        $recs = $proc->getRecords($structure);
        $recs = $proc->saveRecords($recs, true);

        // Save status
        if (count($recs)) {
            $this->settings->setSetting('core.use_ticket_category', $this->in->getBoolInt('enabled'));
        } else {
            $this->settings->setSetting('core.use_ticket_category', '0');
        }

        //------------------------------
        // Save default
        //------------------------------

        $default_id = $this->in->getString('default_id');

        if (isset($recs[$default_id])) {
            // Get id from $recs since the id might've been one
            // generated on the client
            $id = $recs[$default_id]->id;
        } else {
            $id = '0';
        }

        $this->settings->setSetting('core.default_ticket_cat', $id);

        //------------------------------
        // Save validation settings
        //------------------------------

        $this->settings->setSetting('core_tickets.field_validation_ticket_cat_user_required', $this->in->getBoolInt('user_required'));
        $this->settings->setSetting('core_tickets.field_validation_ticket_cat_agent_required', $this->in->getBoolInt('agent_required'));

        return $this->createSuccessResponse();
    }

    //###################################################################################################################
    // list-products
    //###################################################################################################################

    public function listProductsAction()
    {
        $data = [];

        /** @var \Application\DeskPRO\CustomFields\TicketFieldManager $field_manager */
        $field_manager = $this->container->getSystemService('ticket_fields_manager');

        $ticket_prods = $this->container->getSystemService('products');
        $flat_array   = $ticket_prods->getFlatArray();

        $cats = [];
        foreach ($flat_array as $row) {
            $cats[] = $row['object'];
        }

        $data['products']       = $this->getApiData($cats, false);
        $data['default_id']     = $ticket_prods->getDefaultProduct() ? $ticket_prods->getDefaultProduct()->getId() : 0;
        $data['user_required']  = $this->settings->get('core_tickets.field_validation_ticket_prod_user_required') ? true : false;
        $data['agent_required'] = $this->settings->get('core_tickets.field_validation_ticket_prod_agent_required') ? true : false;
        $data['enabled']        = $field_manager->isProductEnabled();

        return $this->createApiResponse($data);
    }

    //###################################################################################################################
    // save-products
    //###################################################################################################################

    public function saveProductsAction()
    {
        $structure = $this->in->getArrayValue('products');

        //------------------------------
        // Save structure
        //------------------------------

        $proc = new HierarchyStructureProcessor($this->em, 'DeskPRO:Product');
        $recs = $proc->getRecords($structure);
        $recs = $proc->saveRecords($recs, true);

        // Save status
        if (count($recs)) {
            $this->settings->setSetting('core.use_product', $this->in->getBoolInt('enabled'));
        } else {
            $this->settings->setSetting('core.use_product', '0');
        }

        //------------------------------
        // Save default
        //------------------------------

        $default_id = $this->in->getString('default_id');

        if (isset($recs[$default_id])) {
            // Get id from $recs since the id might've been one
            // generated on the client
            $id = $recs[$default_id]->id;
        } else {
            $id = '0';
        }

        $this->settings->setSetting('core.default_prod_id', $id);

        //------------------------------
        // Save validation settings
        //------------------------------

        $this->settings->setSetting('core_tickets.field_validation_ticket_prod_user_required', $this->in->getBoolInt('user_required'));
        $this->settings->setSetting('core_tickets.field_validation_ticket_prod_agent_required', $this->in->getBoolInt('agent_required'));

        return $this->createSuccessResponse();
    }

    //###################################################################################################################
    // list-workflows
    //###################################################################################################################

    public function listWorkflowsAction()
    {
        $data = [];

        /** @var \Application\DeskPRO\CustomFields\TicketFieldManager $field_manager */
        $field_manager = $this->container->getSystemService('ticket_fields_manager');

        $ticket_works = $this->container->getSystemService('ticket_workflows');

        $data['workflows']      = $this->getApiData($ticket_works->getAll(), false);
        $data['default_id']     = $ticket_works->getDefaultWorkflow() ? $ticket_works->getDefaultWorkflow()->getId() : 0;
        $data['user_required']  = $this->settings->get('core_tickets.field_validation_ticket_work_user_required') ? true : false;
        $data['agent_required'] = $this->settings->get('core_tickets.field_validation_ticket_work_agent_required') ? true : false;
        $data['enabled']        = $field_manager->isWorkflowEnabled();

        return $this->createApiResponse($data);
    }

    //###################################################################################################################
    // save-workflows
    //###################################################################################################################

    public function saveWorkflowsAction()
    {
        $structure = $this->in->getArrayValue('workflows');

        //------------------------------
        // Save structure
        //------------------------------

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

        //------------------------------
        // Save default
        //------------------------------

        $default_id = $this->in->getString('default_id');

        if (isset($recs[$default_id])) {
            // Get id from $recs since the id might've been one
            // generated on the client
            $id = $recs[$default_id]->id;
        } else {
            $id = '0';
        }

        $this->settings->setSetting('core.default_ticket_work', $id);

        //------------------------------
        // Save validation settings
        //------------------------------

        $this->settings->setSetting('core_tickets.field_validation_ticket_work_user_required', $this->in->getBoolInt('user_required'));
        $this->settings->setSetting('core_tickets.field_validation_ticket_work_agent_required', $this->in->getBoolInt('agent_required'));

        return $this->createSuccessResponse();
    }

    //###################################################################################################################
    // list-priorities
    //###################################################################################################################

    public function listPrioritiesAction()
    {
        $data = [];

        /** @var \Application\DeskPRO\CustomFields\TicketFieldManager $field_manager */
        $field_manager = $this->container->getSystemService('ticket_fields_manager');

        $ticket_pris = $this->container->getSystemService('ticket_priorities');

        $data['priorities']     = $this->getApiData($ticket_pris->getAll(), false);
        $data['default_id']     = $ticket_pris->getDefaultPriority() ? $ticket_pris->getDefaultPriority()->getId() : 0;
        $data['user_required']  = $this->settings->get('core_tickets.field_validation_ticket_pri_user_required') ? true : false;
        $data['agent_required'] = $this->settings->get('core_tickets.field_validation_ticket_pri_agent_required') ? true : false;
        $data['enabled']        = $field_manager->isPriorityEnabled();

        return $this->createApiResponse($data);
    }

    //###################################################################################################################
    // save-priorities
    //###################################################################################################################

    public function savePrioritiesAction()
    {
        $structure = $this->in->getArrayValue('priorities');

        //------------------------------
        // Save structure
        //------------------------------

        $proc = new HierarchyStructureProcessor($this->em, 'DeskPRO:TicketPriority', ['priority']);
        $proc->disableHierarchy();

        $recs = $proc->getRecords($structure);
        $recs = $proc->saveRecords($recs, true);

        // Save status
        if (count($recs)) {
            $this->settings->setSetting('core.use_ticket_priority', $this->in->getBoolInt('enabled'));
        } else {
            $this->settings->setSetting('core.use_ticket_priority', '0');
        }

        //------------------------------
        // Save default
        //------------------------------

        $default_id = $this->in->getString('default_id');

        if (isset($recs[$default_id])) {
            // Get id from $recs since the id might've been one
            // generated on the client
            $id = $recs[$default_id]->id;
        } else {
            $id = '0';
        }

        $this->settings->setSetting('core.default_ticket_pri', $id);

        //------------------------------
        // Save validation settings
        //------------------------------

        $this->settings->setSetting('core_tickets.field_validation_ticket_pri_user_required', $this->in->getBoolInt('user_required'));
        $this->settings->setSetting('core_tickets.field_validation_ticket_pri_agent_required', $this->in->getBoolInt('agent_required'));

        return $this->createSuccessResponse();
    }

    public function convertAction($type)
    {
        $rep_layouts = $this->em->getRepository('DeskPRO:TicketLayout');
        $conn        = $this->em->getConnection();

        switch ($type) {
            case 'category':
            case 'categories':
                $service = $this->container->getSystemService('ticket_categories');
                break;

            case 'priority':
            case 'priorities':
                $service = $this->container->getSystemService('ticket_priorities');
                break;

            case 'workflow':
            case 'workflows':
                $service = $this->container->getSystemService('ticket_workflows');
                break;

            case 'product':
            case 'products':
                $service = $this->container->getSystemService('products');
                break;

            default:
                throw new NotFoundHttpException();
        }

        $short = [
            'category' => 'cat',
            'priority' => 'pri',
            'product'  => 'prod',
            'workflow' => 'work',
        ];
        $singular = 'ies' === substr($type, -3) ? (substr($type, 0, -3).'y') : substr($type, 0, -1);
        $short    = $short[$singular];
        $default  = 'prod' === $short
            ? $this->settings->get('core.default_product_id')
            : $this->settings->get('core.default_ticket_'.$short);

        $data = [
            'title'             => ucfirst($singular),
            'description'       => '',
            'is_enabled'        => true,
            'handler_class'     => 'Application\DeskPRO\CustomFields\Handler\Choice',
            'field_type'        => 'select',
            'default_value'     => $default ? "cb_$default" : null,
            'choices_structure' => [],
        ];

        if ($this->settings->get('core_tickets.field_validation_ticket_'.$short.'_user_required')) {
            $data['min_length']      = 1;
            $data['validation_type'] = 'required';
        }
        if ($this->settings->get('core_tickets.field_validation_ticket_'.$short.'_agent_required')) {
            $data['agent_min_length']      = 1;
            $data['agent_validation_type'] = 'required';
        }

        /*
         * copy children
         */
        foreach ($service->getFlatArray() as $entry) {
            $tree                        = $entry instanceof TicketCategory || $entry instanceof Product;
            $data['choices_structure'][] = [
                'id'            => 'cb_'.$entry['object']['id'],
                '@is_new'       => true,
                'title'         => $entry['object']['title'],
                'parent_id'     => $tree && $entry['object']['parent'] ? ('cb_'.$entry['object']['parent']['id']) : null,
                'display_order' => $entry instanceof TicketPriority ? 0 : $entry['object']['display_order'],
            ];
        }

        $field                = $this->container->getTicketFieldManager()->createNewDefEntity();
        $field->handler_class = $data['handler_class'];

        $container = $this->getContainer();
        $helper = new Form\FormHelper($container->getEm(), $container->getFormFactory());
        $helper->saveFormToField($field, $data);

        /*
         * Layouts
         */
        foreach ($rep_layouts->findAll() as $ticket_layout) {
            /* @var $layout TicketLayout */
            foreach (['user', 'agent'] as $type) {
                $layout = clone $ticket_layout->{$type.'_layout'};
                if ($layout->has($singular)) {
                    $old_layout_field = $layout->get($singular)->exportToArray();
                    $new_layout_field = new LayoutField('ticket_field', $field['id']);
                    $new_layout_field->setOptionsFromArray($old_layout_field['options']);
                    $layout->remove($singular);
                    $layout->add($new_layout_field);
                    $ticket_layout->{$type.'_layout'} = $layout;
                }
            }
        }
        $this->em->flush();

        /*
         * Migrate values
         */
        $cb_map = [];
        foreach ($field->children as $child) {
            if (!$cb = $child->getOption('cb')) {
                continue;
            }
            $cb_map[$cb] = $child['id'];

            /*
             * default value
             */
            if ('cb_'.$cb === $field['default_value']) {
                $field['default_value'] = $child['id'];
                $this->em->flush($field);
            }

            /*
             * set ticket field values
             */
            $offset = 0;
            $limit  = 100;
            $q      = 'select id from tickets where '.$singular.'_id = :cb limit %d, %d';
            $stmt   = $conn->prepare('
                insert into custom_data_ticket (ticket_id, field_id, root_field_id, value, input)
                values (:tid, '.$child['id'].', '.$field['id'].', 1, "")
            ');

            while ($rows = $conn->fetchAll(sprintf($q, $offset, $limit), ['cb' => $cb])) {
                foreach ($rows as $row) {
                    $stmt->execute(['tid' => $row['id']]);
                }
                $offset += $limit;
            }
        }

        /*
         * update ticket filters
         */
        $offset = 0;
        $limit  = 100;
        $like   = '%\"'.$singular.'\"%';
        $q      = 'select id, terms from ticket_filters where terms like "%s" limit %d, %d';
        while ($rows = $conn->fetchAll(sprintf($q, $like, $offset, $limit))) {
            foreach ($rows as $row) {
                if (!$terms = json_decode($row['terms'], 1)) {
                    continue;
                }
                foreach ($terms as &$term) {
                    if ($singular !== $term['type']) {
                        continue;
                    }
                    if (!$options = @$term['options'][$singular]) {
                        continue;
                    }
                    $term['type']    = 'ticket_field['.$field['id'].']';
                    $term['options'] = [];
                    foreach ($options as $val) {
                        if ($val = @$cb_map[$val]) {
                            $term['options']['custom_fields']['field_'.$field['id']][] = $val;
                        }
                    }
                }
                $conn->executeUpdate('update ticket_filters set terms = :terms where id = :id', [
                    'terms' => json_encode($terms),
                    'id'    => $row['id'],
                ]);
            }
            $offset += $limit;
        }

        /*
         * update ticket triggers
         */
        $offset = 0;
        $limit  = 100;
        $check  = '%\"Check'.ucfirst($singular).'\"%';
        $set    = '%\"Set'.ucfirst($singular).'\"%';
        $q      = 'select id, terms, actions from ticket_triggers where terms like "%s" or actions like "%s" limit %d, %d';
        while ($rows = $conn->fetchAll(sprintf($q, $check, $set, $offset, $limit))) {
            foreach ($rows as $row) {
                if ($terms = json_decode($row['terms'], 1)) {
                    foreach ($terms['@DATA']['terms'] as &$set_terms) {
                        foreach ($set_terms['set_terms'] as &$term) {
                            if ('Check'.ucfirst($singular) !== $term['type']) {
                                continue;
                            }
                            if (!$vals = @$term['options'][$singular.'_ids']) {
                                continue;
                            }
                            $term['type']    = 'CheckTicketField'.$field['id'];
                            $term['options'] = ['field_id' => $field['id']];
                            foreach ($vals as $val) {
                                if ($val = @$cb_map[$val]) {
                                    $term['options']['value'][] = $val;
                                }
                            }
                        }
                    }
                    $conn->executeUpdate('update ticket_triggers set terms = :terms where id = :id', [
                        'terms' => json_encode($terms),
                        'id'    => $row['id'],
                    ]);
                }

                if ($actions = json_decode($row['actions'], 1)) {
                    foreach ($actions['@DATA']['actions'] as &$action) {
                        if ('Set'.ucfirst($singular) !== $action['type']) {
                            continue;
                        }
                        if (!$val = @$cb_map[$action['options'][$singular.'_id']]) {
                            continue;
                        }
                        $action['type']    = 'SetTicketField'.$field['id'];
                        $action['options'] = ['value' => $val, 'field_id' => $field['id']];
                    }
                    $conn->executeUpdate('update ticket_triggers set actions = :actions where id = :id', [
                        'actions' => json_encode($actions),
                        'id'      => $row['id'],
                    ]);
                }
            }
            $offset += $limit;
        }

        /*
         * cleanup tickets and disable built in field
         */
//        $conn->executeQuery(sprintf('update tickets set %1$s = null', $singular . '_id'));
//        $this->settings->setSetting('core.use_ticket_' . $singular, false);

        return $this->getCustomFieldAction($field['id']);
    }
}
