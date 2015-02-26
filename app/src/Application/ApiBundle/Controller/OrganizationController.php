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

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Searcher\OrganizationSearch;
use Orb\Util\Numbers;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
* @SWG\Resource(
* 	resourcePath="/organization",
* 	description="Operations about Organization",
* 	basePath="/api"
* )
*/
class OrganizationController extends AbstractController
{
    /**
     * @SWG\Api(
     * 	path="/organization",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Search for organizations matching criteria",
     * 		notes="Returns list of organizations that matched.",
     *		type="array",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="address[]",
     *				description="Requires an organization's address to contain this value.",
     *				paramType="query",
     *				required=false,
     *				type="string"
     *			),
     *			@SWG\Parameter(
     *				name="field[#][]",
     *				description="Requires organization custom field to have the specified value in the listed field.",
     *				paramType="query",
     *				required=false,
     *				type="string"
     *			),
     *			@SWG\Parameter(
     *				name="im[]",
     *				description="Requires an organization to have an instant messenger contact that contains this value.",
     *				paramType="query",
     *				required=false,
     *				type="string"
     *			),
     *			@SWG\Parameter(
     *				name="label[]",
     *				description="Requires organization to be have the specified label.",
     *				paramType="query",
     *				required=false,
     *				type="string"
     *			),
     *			@SWG\Parameter(
     *				name="name[]",
     *				description="Requires an organization to have a name that contains this value.",
     *				paramType="query",
     *				required=false,
     *				type="string"
     *			),
     *			@SWG\Parameter(
     *				name="phone[]",
     *				description="Requires organization to have a phone number that contains this value.",
     *				paramType="query",
     *				required=false,
     *				type="string"
     *			),
     *			@SWG\Parameter(
     *				name="order",
     *				description="Order of the results. Defaults to accessing organization's preference or organizations.id:asc.",
     *				paramType="query",
     *				required=false,
     *				type="string"
     *			),
     *			@SWG\Parameter(
     *				name="cache_id",
     *				description="If provided, cached results from this result set are used. If it cannot be found or used, the other constraints provided will be used to create a new result set.",
     *				paramType="query",
     *				required=false,
     *				type="string"
     *			),
     *			@SWG\Parameter(
     *				name="page",
     *				description="The page number of the results to fetch.",
     *				paramType="query",
     *				required=false,
     *				type="string"
     *			)
     *		)
     * 	)
     * )
     */
    public function searchAction()
    {
        $search_map = array(
            'address' => OrganizationSearch::TERM_CONTACT_ADDRESS,
            'im' => OrganizationSearch::TERM_CONTACT_IM,
            'label' => OrganizationSearch::TERM_LABEL,
            'name' => OrganizationSearch::TERM_NAME,
            'phone' => OrganizationSearch::TERM_CONTACT_PHONE,
        );

        $terms = array();

        foreach ($search_map AS $input => $search_key) {
            $value = $this->in->getCleanValueArray($input, 'raw', 'discard');
            if ($value) {
                $terms[] = array('type' => $search_key, 'op' => 'contains', 'options' => $value);
            }
        }

        foreach ($this->container->getSystemService('org_fields_manager')->getFields() as $field) {
            if ($this->in->checkIsset("field." . $field->getId())) {
                $in_val = $this->in->getString('field.'.$field->getId());
                if ($in_val) {
                    $terms[] = array('type' => 'org_field[' . $field->getId() . ']', 'op' => 'is', 'options' => array('value' => $in_val));
                }
            }
        }

        if ($this->in->checkIsset('order')) {
            $order_by = $this->in->getString('order');
        } else {
            $order_by = $this->person->getPref('agent.ui.org-filter-order-by.0');
            if (!$order_by) {
                $order_by = 'organization.name:asc';
            }
        }

        $extra = array();
        if ($order_by !== null) {
            $extra['order_by'] = $order_by;
        }

        $result_cache = $this->getApiSearchResult('organization', $terms, $extra, $this->in->getUint('cache_id'), new OrganizationSearch());

        $page = $this->in->getUint('page');
        if (!$page) $page = 1;

        $per_page = Numbers::bound($this->in->getUint('per_page') ?: 25, 1, 250);

        $person_ids = $result_cache->results;

        $page_ids = \Orb\Util\Arrays::getPageChunk($person_ids, $page, $per_page);
        $orgs = App::getEntityRepository('DeskPRO:Organization')->getByIds($page_ids, true);

        return $this->createApiResponse(array(
            'page' => $page,
            'per_page' => $per_page,
            'total' => count($person_ids),
            'cache_id' => $result_cache->id,
            'organizations' => $this->getApiData($orgs)
        ));
    }

    /**
     * @SWG\Api(
     * 	path="/organization",
     * 	@SWG\Operation(
     * 		method="POST",
     * 		summary="Creates a new organization.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="name",
     *				description="Name of the organization.",
     *				paramType="query",
     *				required=true,
     *				type="string"
     *			),
     *			@SWG\Parameter(
     *				name="contact_data[#]",
     *				description="Components of a contact detail to add. See the Setting Contact Data organization for more information.",
     *				paramType="query",
     *				required=true,
     *				type="string"
     *			),
     *			@SWG\Parameter(
     *				name="field[#]",
     *				description="Value for the specified custom organization field.",
     *				paramType="query",
     *				required=true,
     *				type="string"
     *			),
     *			@SWG\Parameter(
     *				name="group_id[]",
     *				description="ID of a usergroup to add this organization to.",
     *				paramType="query",
     *				required=true,
     *				type="string"
     *			),
     *			@SWG\Parameter(
     *				name="label[]",
     *				description="Label to apply to the ticket.",
     *				paramType="query",
     *				required=true,
     *				type="string"
     *			),
     *			@SWG\Parameter(
     *				name="summary",
     *				description="Summary of the organization's details.",
     *				paramType="query",
     *				required=true,
     *				type="string"
     *			)
     *		)
     * 	)
     * )
     */
    public function newOrganizationAction()
    {
        if (!$this->person->hasPerm('agent_org.create')) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        $org = new Organization();
        $errors = array();

        $name = $this->in->getString('name');
        if (!$name) {
            $errors['name'] = array('required_field.name', 'name is empty or missing');
        }

        $org->name = $name;

        $bulk_set = array(
            'summary' => 'String',
        );
        foreach ($bulk_set AS $input => $type) {
            if ($this->in->checkIsset($input)) {
                $org->$input = $this->in->{'get' . $type}($input);
            }
        }

        if ($errors) {
            return $this->createApiMultipleErrorResponse($errors);
        }

        foreach ($this->in->getArrayValue('contact_data') AS $contact) {
            $contact_type = isset($contact['type']) ? $contact['type'] : false;
            $data = (isset($contact['data']) && is_array($contact['data'])) ? $contact['data'] : false;

            if (!$contact_type || !$data) {
                continue;
            }

            $data['comment'] = isset($contact['comment']) ? $contact['comment'] : '';

            $contact_data = new \Application\DeskPRO\Entity\OrganizationContactData();
            $contact_data->contact_type = $contact_type;
            try {
                $contact_data->applyFormData($data);
            } catch (\InvalidArgumentException $e) {
                // invalid type
                continue;
            }

            $all_empty = true;
            for ($i = 1; $i <= 10; $i++) {
                if ($contact_data->{'field_' . $i}) {
                    $all_empty = false;
                    break;
                }
            }

            if (!$all_empty) {
                $org->addContactData($contact_data);
            }
        }

        $this->db->beginTransaction();

        try {
            foreach ($this->in->getCleanValueArray('group_id', 'int') as $ug_id) {
                $ug = $this->em->find('DeskPRO:Usergroup', $ug_id);
                if ($ug && !$ug->is_agent_group && !$ug->sys_name) {
                    $org->usergroups->add($ug);
                }
            }

            $this->em->persist($org);

            $field_manager = $this->container->getSystemService('org_fields_manager');
            $post_custom_fields = $this->getCustomFieldInput();
            if (!empty($post_custom_fields)) {
                $field_manager->saveFormToObject($post_custom_fields, $org, true);
            }
            $this->em->flush();

            $labels = $this->in->getCleanValueArray('label', 'string', 'discard');
            if ($labels) {
                $org->getLabelManager()->setLabelsArray($labels, $this->em);
                $this->em->flush();
            }

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }

        return $this->createApiCreateResponse(
            array('id' => $org->id),
            $this->generateUrl('api_organizations_organization', array('organization_id' => $org->id), true)
        );
    }

    /**
     * @SWG\Api(
     * 	path="/organization/{organization_id}",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Gets a organization by organization ID.",
     * 		notes="Information about the organization by organization ID.",
     *		type="Organization",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="organization_id",
     *				description="ID of the organization that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Organization not found")
     * 	)
     * )
     */
    public function getOrganizationAction($organization_id)
    {
        $org = $this->_getOrganizationOr404($organization_id);

        return $this->createApiResponse(array('organization' => $org->toApiData()));
    }

    /**
     * @SWG\Api(
     * 	path="/organization/{organization_id}",
     * 	@SWG\Operation(
     * 		method="POST",
     * 		summary="Updates a organization.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="organization_id",
     *				description="ID of the organization that needs to be updated.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="name",
     *				description="Name of the organization.",
     *				paramType="query",
     *				required=true,
     *				type="string"
     *			),
     *			@SWG\Parameter(
     *				name="field[#]",
     *				description="Value for the specified custom organization field.",
     *				paramType="query",
     *				required=true,
     *				type="string"
     *			),
     *			@SWG\Parameter(
     *				name="summary",
     *				description="Summary of the organization's details.",
     *				paramType="query",
     *				required=true,
     *				type="string"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Organization not found")
     * 	)
     * )
     */
    public function postOrganizationAction($organization_id)
    {
        $org = $this->_getOrganizationOr404($organization_id, 'edit');

        $name = $this->in->getString('name');
        if ($name) {
            $org->name = $name;
        }

        $bulk_set = array(
            'summary' => 'String',
        );
        foreach ($bulk_set AS $input => $type) {
            if ($this->in->checkIsset($input)) {
                $org->$input = $this->in->{'get' . $type}($input);
            }
        }

        $this->db->beginTransaction();

        try {
            $this->em->persist($org);

            $field_manager = $this->container->getSystemService('org_fields_manager');
            $post_custom_fields = $this->getCustomFieldInput();
            if (!empty($post_custom_fields)) {
                $field_manager->saveFormToObject($post_custom_fields, $org, true);
            }
            $this->em->flush();

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }

        return $this->createSuccessResponse();
    }

    /**
     * @SWG\Api(
     * 	path="/organization/{organization_id}",
     * 	@SWG\Operation(
     * 		method="DELETE",
     * 		summary="DELETEs a organization.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="organization_id",
     *				description="ID of the organization that needs to be deleted.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Organization not found")
     * 	)
     * )
     */
    public function deleteOrganizationAction($organization_id)
    {
        $org = $this->_getOrganizationOr404($organization_id, 'delete');

        $edit_manager = $this->container->getSystemService('org_edit_manager');
        $edit_manager->deleteOrganization($org);

        return $this->createSuccessResponse();
    }

    /**
     * @SWG\Api(
     * 	path="/organization/{organization_id}/picture",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Gets a link to an organization's picture.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="organization_id",
     *				description="ID of the organization that needs to be checked.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="size",
     *				description="The maximum size (in pixels) that the picture should be. Defaults to 80",
     *				paramType="query",
     *				required=false,
     *				type="integer"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Organization not found")
     * 	)
     * )
     */
    public function getOrganizationPictureAction($organization_id)
    {
        $org = $this->_getOrganizationOr404($organization_id);

        $size = $this->in->getUint('size');
        if (!$size) {
            $size = 80;
        }

        return $this->createApiResponse(array(
            'has_picture' => $org->hasPicture(),
            'picture_url' => $org->getPictureUrl($size),
            'size' => $size
        ));
    }

    /**
     * @SWG\Api(
     * 	path="/organization/{organization_id}/picture",
     * 	@SWG\Operation(
     * 		method="POST",
     * 		summary="Updates an organization's picture.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="organization_id",
     *				description="ID of the organization that needs to be checked.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="file",
     *				description="An uploaded image. Required if no blob_id is provided.",
     *				paramType="query",
     *				required=false,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="blob_id",
     *				description="ID of a blob record that holds the picture already. Required if no file is provided.",
     *				paramType="query",
     *				required=false,
     *				type="integer"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Organization not found")
     * 	)
     * )
     */
    public function postOrganizationPictureAction($organization_id)
    {
        $org = $this->_getOrganizationOr404($organization_id, 'edit');

        $file = $this->request->files->get('file');
        $accept = $this->container->getAttachmentAccepter();

        if ($file) {
            $error = $accept->getError($file, 'agent');
            if (!$error) {
                $set = new \Application\DeskPRO\Attachments\RestrictionSet();
                $set->setAllowedExts(array('gif', 'png', 'jpg', 'jpeg'));
                $accept->addRestrictionSet('only_images', $set);
                $error = $accept->getError($file, 'only_images');
            }
            if ($error) {
                $message = $this->container->getTranslator()->phrase('agent.general.attach_error_' . $error['error_code'], $error);

                return $this->createApiErrorResponse($error['error_code'], $message);
            }

            $blob = $accept->accept($file);
        } else {
            $blob_id = $this->in->getUint('blob_id');
            $blob = $this->em->find('DeskPRO:Blob', $blob_id);
            if (!$blob) {
                return $this->createApiErrorResponse('invalid_argument.blob_id', 'blob_id not found');
            }
        }

        $org->picture_blob = $blob;
        $this->em->persist($org);
        $this->em->flush();

        return $this->createSuccessResponse();
    }

    /**
     * @SWG\Api(
     * 	path="/organization/{organization_id}/picture",
     * 	@SWG\Operation(
     * 		method="DELETE",
     * 		summary="DELETEs an organization's picture.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="organization_id",
     *				description="ID of the organization that needs to be checked.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Organization not found")
     * 	)
     * )
     */
    public function deleteOrganizationPictureAction($organization_id)
    {
        $org = $this->_getOrganizationOr404($organization_id, 'edit');

        $org->picture_blob = null;
        $this->em->persist($org);
        $this->em->flush();

        return $this->createSuccessResponse();
    }

    /**
     * @SWG\Api(
     * 	path="/organization/{organization_id}/activity-stream",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Gets activity stream for an organization.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="organization_id",
     *				description="ID of the organization that needs to be checked.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="page",
     *				description="The page number of the results to fetch.",
     *				paramType="query",
     *				required=false,
     *				type="integer"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Organization not found")
     * 	)
     * )
     */
    public function getOrganizationActivityStreamAction($organization_id)
    {
        $org = $this->_getOrganizationOr404($organization_id);

        $page = $this->in->getUint('page');
        if (!$page) $page = 1;

        $per_page = Numbers::bound($this->in->getUint('per_page') ?: 25, 1, 250);
        $offset = $per_page * ($page - 1);

        $activity = $this->em->getRepository('DeskPRO:PersonActivity')->getForOrganization($org, $per_page, $offset);
        $total = $this->em->getRepository('DeskPRO:PersonActivity')->countForOrganization($org);

        return $this->createApiResponse(array(
            'page' => $page,
            'per_page' => $per_page,
            'total' => $total,
            'activity' => $this->getApiData($activity)
        ));
    }

    /**
     * @SWG\Api(
     * 	path="/organization/{organization_id}/members",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Gets members of an organization.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="organization_id",
     *				description="ID of the organization that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="page",
     *				description="The page number of the results to fetch.",
     *				paramType="query",
     *				required=false,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="order",
     *				description="Order of the results. Defaults to person.name:asc.",
     *				paramType="query",
     *				required=false,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="cache",
     *				description="The number of seconds to cache the result for. Defaults to 3600. Use 0 to disable the cache",
     *				paramType="query",
     *				required=false,
     *				type="integer"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Organization not found")
     * 	)
     * )
     */
    public function getOrganizationMembersAction($organization_id)
    {
        $org = $this->_getOrganizationOr404($organization_id);

        $terms = array(
            array(
                'type' => \Application\DeskPRO\Searcher\PersonSearch::TERM_ORGANIZATION,
                'op' => 'contains',
                'options' => array($org->id)
            )
        );

        if ($this->in->checkIsset('order')) {
            $order_by = $this->in->getString('order');
        } else {
            $order_by = 'person.name:asc';
        }

        $extra = array();
        if ($order_by !== null) {
            $extra['order_by'] = $order_by;
        }

        $result_cache = $this->getApiSearchResult('person', $terms, $extra, $this->in->getUint('cache_id'), new \Application\DeskPRO\Searcher\PersonSearch());

        $page = $this->in->getUint('page');
        if (!$page) $page = 1;

        $per_page = Numbers::bound($this->in->getUint('per_page') ?: 25, 1, 250);

        $person_ids = $result_cache->results;

        $page_ids = \Orb\Util\Arrays::getPageChunk($person_ids, $page, $per_page);
        $people = App::getEntityRepository('DeskPRO:Person')->getByIds($page_ids, true);

        return $this->createApiResponse(array(
            'page' => $page,
            'per_page' => $per_page,
            'total' => count($person_ids),
            'cache_id' => $result_cache->id,
            'people' => $this->getApiData($people)
        ));
    }

    /**
     * @SWG\Api(
     * 	path="/organization/{organization_id}/tickets",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Gets tickets by an organization.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="organization_id",
     *				description="ID of the organization that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="page",
     *				description="The page number of the results to fetch.",
     *				paramType="query",
     *				required=false,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="order",
     *				description="Order of the results. Defaults to person.name:asc.",
     *				paramType="query",
     *				required=false,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="cache_id",
     *				description="If provided, cached results from this result set are used. If it cannot be found or used, the other constraints provided will be used to create a new result set.",
     *				paramType="query",
     *				required=false,
     *				type="integer"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Organization not found")
     * 	)
     * )
     */
    public function getOrganizationTicketsAction($organization_id)
    {
        $org = $this->_getOrganizationOr404($organization_id);

        $terms = array(
            array(
                'type' => \Application\DeskPRO\Searcher\TicketSearch::TERM_ORGANIZATION,
                'op' => 'contains',
                'options' => array($org->id)
            )
        );

        if ($this->in->checkIsset('order')) {
            $order_by = $this->in->getString('order');
        } else {
            $order_by = 'ticket.date_created:desc';
        }

        $extra = array();
        if ($order_by !== null) {
            $extra['order_by'] = $order_by;
        }

        $result_cache = $this->getApiSearchResult('ticket', $terms, $extra, $this->in->getUint('cache_id'), new \Application\DeskPRO\Searcher\TicketSearch());

        $page = $this->in->getUint('page');
        if (!$page) $page = 1;

        $per_page = Numbers::bound($this->in->getUint('per_page') ?: 25, 1, 250);

        $person_ids = $result_cache->results;

        $page_ids = \Orb\Util\Arrays::getPageChunk($person_ids, $page, $per_page);
        $tickets = App::getEntityRepository('DeskPRO:Ticket')->getByIds($page_ids, true);

        return $this->createApiResponse(array(
            'page' => $page,
            'per_page' => $per_page,
            'total' => count($person_ids),
            'cache_id' => $result_cache->id,
            'tickets' => $this->getApiData($tickets)
        ));
    }

    /**
     * @SWG\Api(
     * 	path="/organization/{organization_id}/chats",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Gets chats by a organization.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="organization_id",
     *				description="ID of the organization that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="page",
     *				description="The page number of the results to fetch.",
     *				paramType="query",
     *				required=false,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="cache_id",
     *				description="If provided, cached results from this result set are used. If it cannot be found or used, the other constraints provided will be used to create a new result set.",
     *				paramType="query",
     *				required=false,
     *				type="integer"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Organization not found")
     * 	)
     * )
     */
    public function getOrganizationChatsAction($organization_id)
    {
        $org = $this->_getOrganizationOr404($organization_id);

        $member_ids = App::getEntityRepository('DeskPRO:Person')->getOrganizationMemberIds($org);
        if ($member_ids) {
            $terms = array(
                array(
                    'type' => \Application\DeskPRO\Searcher\ChatConversationSearch::TERM_PERSON_ID,
                    'op' => 'contains',
                    'options' => $member_ids
                )
            );

            $order_by = 'chat_conversations.id:desc';

            $extra = array();
            if ($order_by !== null) {
                $extra['order_by'] = $order_by;
            }

            $result_cache = $this->getApiSearchResult('chat', $terms, $extra, $this->in->getUint('cache_id'), new \Application\DeskPRO\Searcher\ChatConversationSearch());

            $ids = $result_cache->results;
            $cache_id = $result_cache->id;
        } else {
            $ids = array();
            $cache_id = 0;
        }

        $page = $this->in->getUint('page');
        if (!$page) $page = 1;

        $per_page = Numbers::bound($this->in->getUint('per_page') ?: 25, 1, 250);

        $page_ids = \Orb\Util\Arrays::getPageChunk($ids, $page, $per_page);
        $chats = App::getEntityRepository('DeskPRO:ChatConversation')->getByIds($page_ids, true);

        return $this->createApiResponse(array(
            'page' => $page,
            'per_page' => $per_page,
            'total' => count($ids),
            'cache_id' => $cache_id,
            'chats' => $this->getApiData($chats)
        ));
    }

    /**
     * @SWG\Api(
     * 	path="/organization/{organization_id}/notes",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Gets a list of Organization Notes for an organization.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="organization_id",
     *				description="ID of the organization that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Organization not found")
     * 	)
     * )
     */
    public function getOrganizationNotesAction($organization_id)
    {
        $org = $this->_getOrganizationOr404($organization_id);

        $notes = $this->em->getRepository('DeskPRO:OrganizationNote')->getNotesForOrganization($org);

        return $this->createApiResponse(array('notes' => $this->getApiData($notes)));
    }

    /**
     * @SWG\Api(
     * 	path="/organization/{organization_id}/notes",
     * 	@SWG\Operation(
     * 		method="POST",
     * 		summary="Adds a new Organization Notes to an organization.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="organization_id",
     *				description="ID of the organization that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="note",
     *				description="The Note content",
     *				paramType="query",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Organization not found")
     * 	)
     * )
     */
    public function postOrganizationNotesAction($organization_id)
    {
        $org = $this->_getOrganizationOr404($organization_id, 'note');

        $note_text = $this->in->getString('note');
        if (!$note_text) {
            return $this->createApiErrorResponse('required_field', 'note field is empty or missing');
        }

        $note = new \Application\DeskPRO\Entity\OrganizationNote();
        $note['agent'] = $this->person;
        $note['organization'] = $org;
        $note['note'] = $note_text;

        $this->em->persist($note);
        $this->em->flush();

        return $this->createApiCreateResponse(
            array('id' => $note->id),
            $this->generateUrl('api_organizations_organization_notes_note', array('organization_id' => $org->id, 'note_id' => $note->id), true)
        );
    }

    /**
     * @SWG\Api(
     * 	path="/organization/{organization_id}/notes/{note_id}",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Gets an Organization Note for an organization by Organization ID and Note ID.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="organization_id",
     *				description="ID of the organization that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="note_id",
     *				description="ID of the organization note that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Organization not found")
     * 	)
     * )
     */
    public function getOrganizationNoteAction($organization_id, $note_id)
    {
        $org = $this->_getOrganizationOr404($organization_id);

        $note = $this->em->getRepository('DeskPRO:OrganizationNote')->find($note_id);
        if (!$note || $note->organization->id != $org->id) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        return $this->createApiResponse(array('note' => $note->toApiData()));
    }

    /**
     * @SWG\Api(
     * 	path="/organization/{organization_id}/billing-charges",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Gets billing charges for an organization.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="organization_id",
     *				description="ID of the organization that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="page",
     *				description="Page number to retrieve.",
     *				paramType="query",
     *				required=false,
     *				type="integer"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Organization not found")
     * 	)
     * )
     */
    public function getOrganizationBillingChargesAction($organization_id)
    {
        $organization = $this->_getOrganizationOr404($organization_id);

        $per_page = Numbers::bound($this->in->getUint('per_page') ?: 25, 1, 250);

        $page = $this->in->getUint('page');
        if (!$page) $page = 1;

        $offset = ($page - 1) * $per_page;

        $charges = $this->em->getRepository('DeskPRO:TicketCharge')->getChargesForOrganization($organization, $per_page, $offset);
        $charge_totals = $this->em->getRepository('DeskPRO:TicketCharge')->getTotalChargesForOrganization($organization);

        return $this->createApiResponse(array(
            'total_charge_time' => $charge_totals['charge_time'],
            'total_charge_amount' => $charge_totals['charge'],
            'total' => $charge_totals['count'],
            'per_page' => $per_page,
            'page' => $page,
            'charges' => $this->getApiData($charges)
        ));
    }

    /**
     * @SWG\Api(
     * 	path="/organization/{organization_id}/email-domains",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Gets associated email domains for an organization.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="organization_id",
     *				description="ID of the organization that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Organization not found")
     * 	)
     * )
     */
    public function getOrganizationEmailDomainsAction($organization_id)
    {
        $org = $this->_getOrganizationOr404($organization_id);

        $org_email_domains = $this->em->getRepository('DeskPRO:OrganizationEmailDomain')->getDomainsForOrganization($org);

        $org_count_domain_nonmembers   = $this->em->getRepository('DeskPRO:PersonEmail')->countDomainsWithNoCompany($org_email_domains, $org);
        $org_count_domain_takenmembers = $this->em->getRepository('DeskPRO:PersonEmail')->countDomainsWithOtherCompany($org_email_domains, $org);
        $org_count_domain_members      = $this->em->getRepository('DeskPRO:OrganizationEmailDomain')->countMembersAtDomains($org, $org_email_domains);

        $domains = array();
        foreach ($org_email_domains AS $domain) {
            $domains[] = array(
                'domain' => $domain,
                'members' => isset($org_count_domain_members[$domain]) ? $org_count_domain_members[$domain] : 0,
                'nonmembers' => isset($org_count_domain_nonmembers[$domain]) ? $org_count_domain_nonmembers[$domain] : 0,
                'taken_members' => isset($org_count_domain_takenmembers[$domain]) ? $org_count_domain_takenmembers[$domain] : 0,
            );
        }

        return $this->createApiResponse(array('domains' => $domains));
    }

    /**
     * @SWG\Api(
     * 	path="/organization/{organization_id}/email-domains",
     * 	@SWG\Operation(
     * 		method="POST",
     * 		summary="Adds an email domain for an organization.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="organization_id",
     *				description="ID of the organization that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="domain",
     *				description="Domain name to associate. May not already be in use.",
     *				paramType="path",
     *				required=true,
     *				type="string"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Organization not found")
     * 	)
     * )
     */
    public function postOrganizationEmailDomainsAction($organization_id)
    {
        $org = $this->_getOrganizationOr404($organization_id, 'edit');

        $domain = $this->in->getString('domain');
        if (!$domain) {
            return $this->createApiErrorResponse('required_field.domain', 'domain is missing');
        }

        $org_domain_manager = $this->container->getSystemService('org_email_domain_manager');

        if ($org_domain_manager->isInUse($domain)) {
            return $this->createApiErrorResponse('invalid_argument.domain', 'domain is in use');
        }

        $domain_rec = $org_domain_manager->assignDomain($domain, $org);

        return $this->createApiCreateResponse(
            array('domain' => $domain_rec->domain),
            $this->generateUrl('api_organizations_organization_email_domain', array('organization_id' => $org->id, 'domain' => $domain_rec->domain), true)
        );
    }

    /**
     * @SWG\Api(
     * 	path="/organization/{organization_id}/email-domains/{domain}",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Determines if a domain is associated with an organization.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="organization_id",
     *				description="ID of the organization that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="domain",
     *				description="Domain that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="string"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Organization not found")
     * 	)
     * )
     */
    public function getOrganizationEmailDomainAction($organization_id, $domain)
    {
        $org = $this->_getOrganizationOr404($organization_id);

        $exists = false;
        foreach ($org->email_domains AS $email_domain) {
            if ($email_domain->domain == $domain) {
                $exists = true;
                break;
            }
        }

        return $this->createApiResponse(array('exists' => $exists));
    }

    /**
     * @SWG\Api(
     * 	path="/organization/{organization_id}/email-domains/{domain}/move-users",
     * 	@SWG\Operation(
     * 		method="POST",
     * 		summary="Moves users to an organization (if they have no organization).",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="organization_id",
     *				description="ID of the organization that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="domain",
     *				description="Domain where the users need to be moved",
     *				paramType="path",
     *				required=true,
     *				type="string"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Organization not found")
     * 	)
     * )
     */
    public function postOrganizationEmailDomainMoveUsersAction($organization_id, $domain)
    {
        $org = $this->_getOrganizationOr404($organization_id, 'edit');
        $orgdomain = $this->em->getRepository('DeskPRO:OrganizationEmailDomain')->find(array('organization' => $org, 'domain' => $domain));

        if ($orgdomain) {
            $org_domain_manager = $this->container->getSystemService('org_email_domain_manager');
            $org_domain_manager->moveNonCompanyUsers($orgdomain);
        }

        return $this->createSuccessResponse();
    }

    /**
     * @SWG\Api(
     * 	path="/organization/{organization_id}/email-domains/{domain}/move-taken-users",
     * 	@SWG\Operation(
     * 		method="POST",
     * 		summary="Moves users to an organization (if they have another organization).",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="organization_id",
     *				description="ID of the organization that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="domain",
     *				description="Domain where the users need to be moved",
     *				paramType="path",
     *				required=true,
     *				type="string"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Organization not found")
     * 	)
     * )
     */
    public function postOrganizationEmailDomainMoveTakenUsersAction($organization_id, $domain)
    {
        $org = $this->_getOrganizationOr404($organization_id, 'edit');
        $orgdomain = $this->em->getRepository('DeskPRO:OrganizationEmailDomain')->find(array('organization' => $org, 'domain' => $domain));

        if ($orgdomain) {
            $org_domain_manager = $this->container->getSystemService('org_email_domain_manager');
            $org_domain_manager->moveOtherCompanyUsers($orgdomain);
        }

        return $this->createSuccessResponse();
    }

    /**
     * @SWG\Api(
     * 	path="/organization/{organization_id}/email-domains/{domain}",
     * 	@SWG\Operation(
     * 		method="DELETE",
     * 		summary="Deletes a domain association for an organization",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="organization_id",
     *				description="ID of the organization that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="domain",
     *				description="Domain that needs to be deleted.",
     *				paramType="path",
     *				required=true,
     *				type="string"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Organization not found")
     * 	)
     * )
     */
    public function deleteOrganizationEmailDomainAction($organization_id, $domain)
    {
        $org = $this->_getOrganizationOr404($organization_id, 'edit');
        $orgdomain = $this->em->getRepository('DeskPRO:OrganizationEmailDomain')->find(array('organization' => $org, 'domain' => $domain));

        if ($orgdomain) {
            $org_domain_manager = $this->container->getSystemService('org_email_domain_manager');
            $org_domain_manager->unassignDomain($orgdomain, $this->in->getBool('remove_users'));
        }

        return $this->createSuccessResponse();
    }

    /**
     * @SWG\Api(
     * 	path="/organization/{organization_id}/contact-details",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Gets contact details for an organization.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="organization_id",
     *				description="ID of the organization that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Organization not found")
     * 	)
     * )
     */
    public function getOrganizationContactDetailsAction($organization_id)
    {
        $org = $this->_getOrganizationOr404($organization_id);

        return $this->createApiResponse(array('details' => $this->getApiData($org->contact_data)));
    }

    /**
     * @SWG\Api(
     * 	path="/organization/{organization_id}/contact-details",
     * 	@SWG\Operation(
     * 		method="POST",
     * 		summary="Creates a contact detail for an organization.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="organization_id",
     *				description="ID of the organization that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="type",
     *				description="Type of contact detail to add.",
     *				paramType="query",
     *				required=true,
     *				type="string"
     *			),
     *			@SWG\Parameter(
     *				name="data",
     *				description="Contact detail-specific data.",
     *				paramType="query",
     *				required=true,
     *				type="string"
     *			),
     *			@SWG\Parameter(
     *				name="comment",
     *				description="Comment or label for the contact detail.",
     *				paramType="query",
     *				required=true,
     *				type="string"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Organization not found")
     * 	)
     * )
     */
    public function postOrganizationContactDetailsAction($organization_id)
    {
        $org = $this->_getOrganizationOr404($organization_id, 'edit');

        $type = $this->in->getString('type');
        $data = $this->in->getArrayValue('data');
        $comment = $this->in->getString('comment');

        if (!$type) {
            return $this->createApiErrorResponse('required_field.type', 'type is empty or missing');
        }
        if (!$data) {
            return $this->createApiErrorResponse('required_field.data', 'data is empty or missing');
        }

        $data['comment'] = $comment;

        $contact_data = new \Application\DeskPRO\Entity\OrganizationContactData();
        $contact_data->contact_type = $type;
        try {
            $contact_data->applyFormData($data);
        } catch (\InvalidArgumentException $e) {
            return $this->createApiErrorResponse('invalid_argument.type', 'type is invalid');
        }

        $all_empty = true;
        for ($i = 1; $i <= 10; $i++) {
            if ($contact_data->{'field_' . $i}) {
                $all_empty = false;
                break;
            }
        }

        if ($all_empty) {
            return $this->createApiErrorResponse('invalid_argument.data', 'data contains invalid data');
        }

        $contact_data->organization = $org;

        $this->em->persist($contact_data);
        $this->em->flush();

        return $this->createApiCreateResponse(
            array('id' => $contact_data->id),
            $this->generateUrl('api_organizations_organization_contact_detail', array('organization_id' => $org->id, 'contact_id' => $contact_data->id), true)
        );
    }

    /**
     * @SWG\Api(
     * 	path="/organization/{organization_id}/contact-details/{contact_id}",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Determines if contact ID exists for organization.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="organization_id",
     *				description="ID of the organization that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="contact_id",
     *				description="ID of the contact that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Organization not found")
     * 	)
     * )
     */
    public function getOrganizationContactDetailAction($organization_id, $contact_id)
    {
        $org = $this->_getOrganizationOr404($organization_id);

        foreach ($org->contact_data AS $contact) {
            if ($contact->id == $contact_id) {
                return $this->createApiResponse(array('exists' => true));
            }
        }

        return $this->createApiResponse(array('exists' => false));
    }

    /**
     * @SWG\Api(
     * 	path="/organization/{organization_id}/contact-details/{contact_id}",
     * 	@SWG\Operation(
     * 		method="DELETE",
     * 		summary="Deletes a contact for an organization.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="organization_id",
     *				description="ID of the organization that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="contact_id",
     *				description="ID of the contact that needs to be deleted.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Organization not found")
     * 	)
     * )
     */
    public function deleteOrganizationContactDetailAction($organization_id, $contact_id)
    {
        $org = $this->_getOrganizationOr404($organization_id, 'edit');

        foreach ($org->contact_data AS $key => $contact) {
            if ($contact->id == $contact_id) {
                unset($org->contact_data[$key]);
                $this->em->persist($org);
                $this->em->flush();
                break;
            }
        }

        return $this->createSuccessResponse();
    }

    /**
     * @SWG\Api(
     * 	path="/organization/{organization_id}/groups",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Gets the groups for an organization.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="organization_id",
     *				description="ID of the organization that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Organization not found")
     * 	)
     * )
     */
    public function getOrganizationGroupsAction($organization_id)
    {
        $org = $this->_getOrganizationOr404($organization_id);

        return $this->createApiResponse(array('groups' => $this->getApiData($org->usergroups)));
    }

    /**
     * @SWG\Api(
     * 	path="/organization/{organization_id}/groups",
     * 	@SWG\Operation(
     * 		method="POST",
     * 		summary="Adds an organization to a group.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="organization_id",
     *				description="ID of the organization that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="id",
     *				description="ID of the group to add this organization to.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Organization not found")
     * 	)
     * )
     */
    public function postOrganizationGroupsAction($organization_id)
    {
        $org = $this->_getOrganizationOr404($organization_id, 'edit');

        $group_id = $this->in->getUint('id');

        $match = $this->db->fetchColumn('
            SELECT id
            FROM usergroups
            WHERE id = ?
                AND sys_name IS NULL
                AND is_agent_group = 0
        ', array($group_id));
        if (!$match) {
            return $this->createApiErrorResponse('required_field', 'id must be specified as a non-system group');
        }

        $exists = false;
        foreach ($org->usergroups AS $group) {
            if ($group->id == $group_id) {
                $exists = true;
            }
        }

        if (!$exists) {
            $this->db->insert('organization2usergroups', array(
                'organization_id' => $org->id,
                'usergroup_id' => $group_id
            ));
        }

        return $this->createApiCreateResponse(
            array('id' => $group_id),
            $this->generateUrl('api_organizations_organization_group', array('organization_id' => $org->id, 'usergroup_id' => $group_id), true)
        );
    }

    /**
     * @SWG\Api(
     * 	path="/organization/{organization_id}/groups/{usergroup_id}",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Determines if an organization is a member of a group.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="organization_id",
     *				description="ID of the organization that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="usergroup_id",
     *				description="ID of the usergroup that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Organization not found")
     * 	)
     * )
     */
    public function getOrganizationGroupAction($organization_id, $usergroup_id)
    {
        $org = $this->_getOrganizationOr404($organization_id);

        foreach ($org->usergroups AS $group) {
            if ($group->id == $usergroup_id) {
                return $this->createApiResponse(array('exists' => true));
            }
        }

        return $this->createApiResponse(array('exists' => false));
    }

    /**
     * @SWG\Api(
     * 	path="/organization/{organization_id}/groups/{usergroup_id}",
     * 	@SWG\Operation(
     * 		method="DELETE",
     * 		summary="Removes an organization from a group.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="organization_id",
     *				description="ID of the organization that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="usergroup_id",
     *				description="ID of the usergroup that needs to be removed.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Organization not found")
     * 	)
     * )
     */
    public function deleteOrganizationGroupAction($organization_id, $usergroup_id)
    {
        $org = $this->_getOrganizationOr404($organization_id, 'edit');

        foreach ($org->usergroups AS $key => $group) {
            if ($group->id == $usergroup_id) {
                if ($group->is_agent_group) {
                    return $this->createApiErrorResponse('invalid_group', 'Group is an agent group');
                }
                unset($org->usergroups[$key]);
                $this->em->persist($org);
                $this->em->flush();
                break;
            }
        }

        return $this->createSuccessResponse();
    }

    /**
     * @SWG\Api(
     * 	path="/organization/{organization_id}/labels",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Gets the labels for an organization.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="organization_id",
     *				description="ID of the organization that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Organization not found")
     * 	)
     * )
     */
    public function getOrganizationLabelsAction($organization_id)
    {
        $org = $this->_getOrganizationOr404($organization_id);

        return $this->createApiResponse(array('labels' => $this->getApiData($org->labels)));
    }

    /**
     * @SWG\Api(
     * 	path="/organization/{organization_id}/labels",
     * 	@SWG\Operation(
     * 		method="POST",
     * 		summary="Add a label for an organization.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="organization_id",
     *				description="ID of the organization that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="label",
     *				description="Label to add",
     *				paramType="query",
     *				required=true,
     *				type="string"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Organization not found")
     * 	)
     * )
     */
    public function postOrganizationLabelsAction($organization_id)
    {
        $org = $this->_getOrganizationOr404($organization_id, 'edit');
        $label = $this->in->getString('label');

        if ($label === '') {
            return $this->createApiErrorResponse('required_field.label', "Field 'label' missing or empty");
        }

        $org->getLabelManager()->addLabel($label);
        $this->em->persist($org);
        $this->em->flush();

        return $this->createApiCreateResponse(
            array('label' => $label),
            $this->generateUrl('api_organizations_organization_label', array('organization_id' => $org->id, 'label' => $label), true)
        );
    }

    /**
     * @SWG\Api(
     * 	path="/organization/{organization_id}/labels/{label}",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Determines if an organization has a label.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="organization_id",
     *				description="ID of the organization that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="label",
     *				description="label that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="string"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Organization not found")
     * 	)
     * )
     */
    public function getOrganizationLabelAction($organization_id, $label)
    {
        $org = $this->_getOrganizationOr404($organization_id);

        if ($org->getLabelManager()->hasLabel($label)) {
            return $this->createApiResponse(array('exists' => true));
        } else {
            return $this->createApiResponse(array('exists' => false));
        }
    }

    /**
     * @SWG\Api(
     * 	path="/organization/{organization_id}/labels/{label}",
     * 	@SWG\Operation(
     * 		method="DELETE",
     * 		summary="Determines if an organization has a label.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="organization_id",
     *				description="ID of the organization that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="label",
     *				description="label that needs to be deleted.",
     *				paramType="path",
     *				required=true,
     *				type="string"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Organization not found")
     * 	)
     * )
     */
    public function deleteOrganizationLabelAction($organization_id, $label)
    {
        $org = $this->_getOrganizationOr404($organization_id, 'edit');

        $org->getLabelManager()->removeLabel($label);
        $this->em->persist($org);
        $this->em->flush();

        return $this->createSuccessResponse();
    }

    /**
     * @SWG\Api(
     * 	path="/organization/fields",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Gets available custom organization fields."
     * 	)
     * )
     */
    public function getFieldsAction()
    {
        $field_manager = $this->container->getSystemService('org_fields_manager');
        $fields = $field_manager->getFields();

        return $this->createApiResponse(array('fields' => $this->getApiData($fields)));
    }

    /**
     * @SWG\Api(
     * 	path="/organization/fields",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Gets available usergroups."
     * 	)
     * )
     */
    public function getGroupsAction()
    {
        $groups = $this->em->createQuery('
            SELECT g
            FROM DeskPRO:Usergroup g INDEX BY g.id
            WHERE g.is_agent_group = false AND g.sys_name IS NULL
            ORDER BY g.id
        ')->execute();

        return $this->createApiResponse(array('groups' => $this->getApiData($groups)));
    }

    /**
     * @param  integer                                                       $id
     * @param  string                                                        $check_perm
     * @return \Application\DeskPRO\Entity\Organization
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function _getOrganizationOr404($id, $check_perm = '')
    {
        $org = $this->em->getRepository('DeskPRO:Organization')->findOneById($id);

        if (!$org) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no organization with ID $id");
        }

        if ($check_perm) {
            switch ($check_perm) {
                case 'edit':
                case 'delete':
                case 'create':
                case 'note':
                    if (!$this->person->hasPerm('agent_org.' . $check_perm)) {
                        throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
                    }
                    break;

                default:
                    throw new \Exception("Uknown perm type $check_perm");
            }
        }

        return $org;
    }

    /**
     * @param  Request  $request
     * @return Response
     */
    public function quickSearchAction(Request $request)
    {
        /** @var \Application\DeskPRO\EntityRepository\Organization $rep */
        $rep = $this->em->getRepository('DeskPRO:Organization');
        $res = $rep->search($request->get('query'), $request->get('limit'), false);

        return $this->createApiResponse($res);
    }
}
