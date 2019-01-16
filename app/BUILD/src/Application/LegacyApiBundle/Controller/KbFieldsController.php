<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\CustomFields\Form;
use Application\DeskPRO\Entity\CustomDefArticle;
use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;

/**
 * Class KbFieldsController.
 *
 * @ApiModes("all")
 */
class KbFieldsController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPermissionStrategy()
    {
        return new AdminManagePermission();
    }

    //###################################################################################################################
    // list
    //###################################################################################################################

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        $data = [];

        /** @var \Application\DeskPRO\CustomFields\KbFieldManager $fieldManager */
        $fieldManager = $this->container->getSystemService('kb_fields_manager');

        $custom_fields         = $fieldManager->getDefinedFields();
        $data['custom_fields'] = $this->getApiData($custom_fields, false);

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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getCustomFieldAction($id)
    {
        $field = $this->em->find(CustomDefArticle::class, $id);
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

    /**
     * @param $id
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Exception
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function saveCustomFieldAction($id)
    {
        /** @var \Application\DeskPRO\CustomFields\KbFieldManager $fieldManager */
        $fieldManager = $this->container->getSystemService('kb_fields_manager');

        if ($id) {
            $field = $this->em->find(CustomDefArticle::class, $id);
            if (!$field || $field->parent) {
                throw $this->createNotFoundException();
            }
        } else {
            $field                = $fieldManager->createNewDefEntity();
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
                    $this->generateUrl('api_kb_fields_get', ['id' => $field->id]),
                ]
            );
        }
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteCustomFieldAction($id)
    {
        $field = $this->em->find(CustomDefArticle::class, $id);
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toggleFieldAction($field_id, $is_enabled)
    {
        /** @var \Application\DeskPRO\CustomFields\KbFieldManager $fieldManager */
        $fieldManager = $this->container->getSystemService('kb_fields_manager');
        $fieldManager->setFieldEnabledById($field_id, $is_enabled);

        return $this->createSuccessResponse();
    }

    //###################################################################################################################
    // save-display-order
    //###################################################################################################################

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function saveDisplayOrderAction()
    {
        $displayOrders = $this->in->getCleanValueArray('display_orders', 'uint', 'discard');
        $this->em->getRepository(CustomDefArticle::class)->updateDisplayOrders($displayOrders);

        return $this->createSuccessResponse();
    }

    //###################################################################################################################
    // save batch
    //###################################################################################################################

    public function saveBatchCustomFieldAction()
    {
        $fields = $this->in->getCleanValueArray('custom_fields');

        $container = $this->getContainer();
        $helper    = new Form\FormHelper($container->getEm(), $container->getFormFactory());

        foreach ($fields as $fieldData) {
            $field = $this->em->find(CustomDefArticle::class, $fieldData['id']);
            if (!$field || $field->parent) {
                throw $this->createNotFoundException();
            }
            try {
                $helper->saveFormToField($field, $fieldData);
            } catch (\RuntimeException $e) {
                return $this->createApiErrorResponse('validation_error', $e->getMessage());
            }
        }

        return $this->createSuccessResponse();
    }
}
