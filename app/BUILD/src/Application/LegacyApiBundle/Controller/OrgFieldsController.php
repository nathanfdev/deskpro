<?php

/*
 * Deskpro (r) has been developed by Deskpro Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, Deskpro Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that Deskpro is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing Deskpro since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team Deskpro
 */

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\CustomFields\Form;
use Application\DeskPRO\CustomFields\Form\AliasListHelper;
use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use Application\LegacyApiBundle\PermissionStrategy\MultiPermissions;
use Application\LegacyApiBundle\PermissionStrategy\PassPermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;

/**
 * @ApiModes("all")
 */
class OrgFieldsController extends AbstractController implements ProtectedControllerInterface
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

    public function listAction()
    {
        $data = [];

        /** @var \Application\DeskPRO\CustomFields\OrganizationFieldManager $field_manager */
        $field_manager = $this->container->getSystemService('org_fields_manager');

        $custom_fields         = $field_manager->getDefinedFields();
        $data['custom_fields'] = $this->getApiData($custom_fields, false);

        return $this->createApiResponse($data);
    }

    //###################################################################################################################
    // get-custom-field
    //###################################################################################################################

    public function getCustomFieldAction($id)
    {
        $field = $this->em->find('DeskPRO:CustomDefOrganization', $id);
        if (!$field || $field->parent) {
            throw $this->createNotFoundException();
        }

        $aliasListHelper = new AliasListHelper();
        $alias           = $aliasListHelper->findAdminAlias($field);

        $data          = [];
        $data['field'] = array_merge($field->toApiData(), ['alias' => (string) $alias]);

        return $this->createApiResponse($data);
    }

    //###################################################################################################################
    // save-custom-field
    //###################################################################################################################

    public function saveCustomFieldAction($id)
    {
        /** @var \Application\DeskPRO\CustomFields\OrganizationFieldManager $field_manager */
        $field_manager = $this->container->getSystemService('org_fields_manager');

        if ($id) {
            $field = $this->em->find('DeskPRO:CustomDefOrganization', $id);
            if (!$field || $field->parent) {
                throw $this->createNotFoundException();
            }
        } else {
            $field                = $field_manager->createNewDefEntity();
            $field->handler_class = $this->in->getString('handler_class');
        }

        $post = $this->in->getAll('req');
        if (empty($post['title'])) {
            return $this->createApiErrorResponse('validation_error', 'Empty title');
        }

        // there is no separate api to change a single alias, must change all aliases
        // so we either add or remove the new alias to/from the list of existing aliases
        if ($id && array_key_exists('alias', $post)) {
            $aliasListHelper = new AliasListHelper();
            $post['alias']   = $aliasListHelper->changeAdminAlias($field, $post['alias']);
        }

        $helper = $this->get(Form\FormHelper::class);
        try {
            $helper->saveFormToField($field, $post);
        } catch (\RuntimeException $e) {
            return $this->createApiErrorResponse('validation_error', $e->getMessage());
        }

        if ($id) {
            return $this->createSuccessResponse(
                [
                     'field_id' => $field->id,
                ]
            );
        } else {
            return $this->createSuccessResponse(
                [
                     'field_id' => $field->id,
                     $this->generateUrl('api_org_fields_get', ['id' => $field->id]),
                ]
            );
        }
    }

    //###################################################################################################################
    // delete-custom-field
    //###################################################################################################################

    public function deleteCustomFieldAction($id)
    {
        $field = $this->em->find('DeskPRO:CustomDefOrganization', $id);
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

    public function toggleFieldAction($field_id, $is_enabled)
    {
        /** @var \Application\DeskPRO\CustomFields\OrganizationFieldManager $field_manager */
        $field_manager = $this->container->getSystemService('org_fields_manager');
        $field_manager->setFieldEnabledById($field_id, $is_enabled);

        return $this->createSuccessResponse();
    }

    //###################################################################################################################
    // save-display-order
    //###################################################################################################################

    public function saveDisplayOrderAction()
    {
        $display_orders = $this->in->getCleanValueArray('display_orders', 'uint', 'discard');
        $this->em->getRepository('DeskPRO:CustomDefOrganization')->updateDisplayOrders($display_orders);

        return $this->createSuccessResponse();
    }
}
