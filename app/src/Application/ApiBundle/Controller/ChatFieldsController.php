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

class ChatFieldsController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritDoc}
     */
    public function getPermissionStrategy()
    {
        return new AdminManagePermission();
    }

    ####################################################################################################################
    # list
    ####################################################################################################################

    public function listAction()
    {
        $data = array();

        /** @var \Application\DeskPRO\CustomFields\ChatFieldManager $field_manager */
        $field_manager = $this->container->getSystemService('chat_fields_manager');

        $custom_fields = $field_manager->getDefinedFields();
        $data['custom_fields'] = $this->getApiData($custom_fields, false);

        return $this->createApiResponse($data);
    }

    ####################################################################################################################
    # get-custom-field
    ####################################################################################################################

    public function getCustomFieldAction($id)
    {
        $field = $this->em->find('DeskPRO:CustomDefChat', $id);
        if (!$field || $field->parent) {
            throw $this->createNotFoundException();
        }

        $data = array();
        $data['field'] = $field->toApiData();

        return $this->createApiResponse($data);
    }

    ####################################################################################################################
    # save-custom-field
    ####################################################################################################################

    public function saveCustomFieldAction($id)
    {
        /** @var \Application\DeskPRO\CustomFields\ChatFieldManager $field_manager */
        $field_manager = $this->container->getSystemService('chat_fields_manager');

        if ($id) {
            $field = $this->em->find('DeskPRO:CustomDefChat', $id);
            if (!$field || $field->parent) {
                throw $this->createNotFoundException();
            }
        } else {
            $field = $field_manager->createNewDefEntity();
            $field->handler_class = $this->in->getString('handler_class');
        }

        $post = $this->in->getAll('req');

        $helper = new CustomFieldHelper($this);
        $helper->saveFormToField($field, $post);

        if ($id) {
            return $this->createSuccessResponse(
                array(
                     'field_id' => $field->id
                )
            );
        } else {
            return $this->createSuccessResponse(
                array(
                     'field_id' => $field->id,
                     $this->generateUrl('api_chat_fields_get', array('id' => $field->id))
                )
            );
        }
    }

    ####################################################################################################################
    # delete-custom-field
    ####################################################################################################################

    public function deleteCustomFieldAction($id)
    {
        $field = $this->em->find('DeskPRO:CustomDefChat', $id);
        if (!$field || $field->parent) {
            throw $this->createNotFoundException();
        }

        $this->em->remove($field);
        $this->em->flush();

        return $this->createApiDeleteResponse();
    }

    ####################################################################################################################
    # toggleField
    ####################################################################################################################

    public function toggleFieldAction($field_id, $is_enabled)
    {
        /** @var \Application\DeskPRO\CustomFields\ChatFieldManager $field_manager */
        $field_manager = $this->container->getSystemService('chat_fields_manager');
        $field_manager->setFieldEnabledById($field_id, $is_enabled);

        return $this->createSuccessResponse();
    }

    ####################################################################################################################
    # save-display-order
    ####################################################################################################################

    public function saveDisplayOrderAction()
    {
        $display_orders = $this->in->getCleanValueArray('display_orders', 'uint', 'discard');
        $this->em->getRepository('DeskPRO:CustomDefChat')->updateDisplayOrders($display_orders);

        return $this->createSuccessResponse();
    }
}
