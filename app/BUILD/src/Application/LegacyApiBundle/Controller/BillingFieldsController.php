<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\CustomFields\Form;
use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use Application\LegacyApiBundle\PermissionStrategy\MultiPermissions;
use Application\LegacyApiBundle\PermissionStrategy\PassPermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;

/**
 * Class BillingFieldsController.
 *
 * @ApiModes("all")
 */
class BillingFieldsController extends AbstractController implements ProtectedControllerInterface
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

        /** @var \Application\DeskPRO\CustomFields\BillingFieldManager $field_manager */
        $field_manager = $this->container->getBillingFieldManager();

        $custom_fields         = $field_manager->getDefinedFields();
        $data['custom_fields'] = $this->getApiData($custom_fields, false);

        return $this->createApiResponse($data);
    }

    //###################################################################################################################
    // get-custom-field
    //###################################################################################################################

    public function getCustomFieldAction($id)
    {
        $field = $this->em->find('DeskPRO:CustomDefBilling', $id);
        if (!$field || $field->parent) {
            throw $this->createNotFoundException();
        }

        $data          = [];
        $data['field'] = $field->toApiData();

        return $this->createApiResponse($data);
    }

    //###################################################################################################################
    // save-custom-field
    //###################################################################################################################

    public function saveCustomFieldAction($id)
    {
        /** @var \Application\DeskPRO\CustomFields\BillingFieldManager $field_manager */
        $field_manager = $this->container->getBillingFieldManager();

        if ($id) {
            $field = $this->em->find('DeskPRO:CustomDefBilling', $id);
            if (!$field || $field->parent) {
                throw $this->createNotFoundException();
            }
        } else {
            $field                = $field_manager->createNewDefEntity();
            $field->handler_class = $this->in->getString('handler_class');
        }

        $post = $this->in->getAll('req');

        $container = $this->getContainer();
        $helper    = new Form\FormHelper($container->getEm(), $container->getFormFactory());
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
                     $this->generateUrl('api_user_fields_get', ['id' => $field->id]),
                ]
            );
        }
    }

    //###################################################################################################################
    // delete-custom-field
    //###################################################################################################################

    public function deleteCustomFieldAction($id)
    {
        $field = $this->em->find('DeskPRO:CustomDefBilling', $id);
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
        /** @var \Application\DeskPRO\CustomFields\BillingFieldManager $field_manager */
        $field_manager = $this->container->getBillingFieldManager();
        $field_manager->setFieldEnabledById($field_id, $is_enabled);

        return $this->createSuccessResponse();
    }

    //###################################################################################################################
    // save-display-order
    //###################################################################################################################

    public function saveDisplayOrderAction()
    {
        $display_orders = $this->in->getCleanValueArray('display_orders', 'uint', 'discard');
        $this->em->getRepository('DeskPRO:CustomDefBilling')->updateDisplayOrders($display_orders);

        return $this->createSuccessResponse();
    }
}
