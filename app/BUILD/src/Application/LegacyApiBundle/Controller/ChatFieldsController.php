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

use Application\DeskPRO\Entity\CustomDefChat;
use Application\DeskPRO\CustomFields\Form;
use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;

/**
 * Class ChatFieldsController.
 *
 * @ApiModes("all")
 */
class ChatFieldsController extends AbstractController implements ProtectedControllerInterface
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

        /** @var \Application\DeskPRO\CustomFields\ChatFieldManager $fieldManager */
        $fieldManager = $this->container->getSystemService('chat_fields_manager');

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
        $field = $this->em->find(CustomDefChat::class, $id);
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
        /** @var \Application\DeskPRO\CustomFields\ChatFieldManager $fieldManager */
        $fieldManager = $this->container->getSystemService('chat_fields_manager');

        if ($id) {
            $field = $this->em->find(CustomDefChat::class, $id);
            if (!$field || $field->parent) {
                throw $this->createNotFoundException();
            }
        } else {
            $field                = $fieldManager->createNewDefEntity();
            $field->handler_class = $this->in->getString('handler_class');
        }

        $post = $this->in->getAll('req');

        $container = $this->getContainer();
        $helper = new Form\FormHelper($container->getEm(), $container->getFormFactory());
        $helper->saveFormToField($field, $post);

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
                    $this->generateUrl('api_chat_fields_get', ['id' => $field->id]),
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
        $field = $this->em->find(CustomDefChat::class, $id);
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
        /** @var \Application\DeskPRO\CustomFields\ChatFieldManager $fieldManager */
        $fieldManager = $this->container->getSystemService('chat_fields_manager');
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
        $this->em->getRepository(CustomDefChat::class)->updateDisplayOrders($displayOrders);

        return $this->createSuccessResponse();
    }

    //###################################################################################################################
    // save batch
    //###################################################################################################################

    public function saveBatchCustomFieldAction()
    {
        $fields = $this->in->getCleanValueArray('custom_fields');

        $container = $this->getContainer();
        $helper = new Form\FormHelper($container->getEm(), $container->getFormFactory());

        foreach ($fields as $fieldData) {
            $field = $this->em->find(CustomDefChat::class, $fieldData['id']);
            if (!$field || $field->parent) {
                throw $this->createNotFoundException();
            }
            $helper->saveFormToField($field, $fieldData);
        }

        return $this->createSuccessResponse();
    }
}
