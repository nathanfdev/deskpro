<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

use Application\DeskPRO\CustomFields\CustomDataPersister;
use Application\DeskPRO\CustomFields\FieldDisplayArray;
use Application\DeskPRO\CustomFields\FieldManager;
use Application\DeskPRO\CustomFields\Handler\Choice;
use Application\DeskPRO\CustomFields\Handler\HandlerAbstract;
use Application\DeskPRO\Entity\CustomFieldDefinition;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\EntityRepository\CustomDefAbstract;
use Application\DeskPRO\Form\Type\CustomFields\Definitions\SimpleDefinitionType;
use Application\LegacyApiBundle\HttpFoundation\JsonResponse;
use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use Application\LegacyApiBundle\PermissionStrategy\MultiPermissions;
use Application\LegacyApiBundle\PermissionStrategy\PassPermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @ApiModes("all")
 */
class CustomFieldsController extends AbstractController implements ProtectedControllerInterface
{
    protected $allowed = array(
        'owner'   => array('ticket', 'person'),
        'context' => array('person', 'organization'),
    );

    public static $allowed_common = array(
        'person'  => 'Person',
        'org'     => 'Organization',
        'ticket'  => 'Ticket',
        'billing' => 'TicketCharge',
    );

    /**
     * {@inheritdoc}
     */
    public function getPermissionStrategy()
    {
        $multi = new MultiPermissions();
        $multi->addPermissionStrategy(new AdminManagePermission());
        $multi->addPermissionStrategy(new PassPermission(), 'listAction');
        $multi->addPermissionStrategy(new PassPermission(), 'getCommonFieldsAction');
        $multi->addPermissionStrategy(new PassPermission(), 'setCommonFieldsAction');

        return $multi;
    }

    protected function filterContextClass($context)
    {
        $context = strtolower($context);
        if (!in_array($context, $this->allowed['context'])) {
            throw new NotFoundHttpException();
        }

        return 'Application\DeskPRO\Entity\\'.Container::camelize($context);
    }

    protected function filterOwnerClass($owner)
    {
        $owner = strtolower($owner);
        if (!in_array($owner, $this->allowed['owner'])) {
            throw new NotFoundHttpException();
        }

        return 'Application\DeskPRO\Entity\\'.Container::camelize($owner);
    }

    ####################################################################################################################
    # list
    ####################################################################################################################

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listAction(Request $request)
    {
        $criteria = array('parent' => null);

        if ($owner = $this->in->getString('owner')) {
            $criteria['owner_class'] = $this->filterOwnerClass($owner);
        }

        if ($context = $this->in->getString('context')) {
            $criteria['context_class'] = $this->filterContextClass($context);
        }

        $definitions = $this->em->getRepository('DeskPRO:CustomFieldDefinition')->findBy(
            $criteria,
            array('display_order' => 'ASC')
        );

        return $this->createApiResponse($this->getApiData($definitions, false));
    }

    /**
     * get children (choices) of custom field.
     *
     * @param Request $request
     * @param $id
     *
     * @return JsonResponse
     */
    public function childrenAction(Request $request, $id)
    {
        $criteria = array('parent' => $id);

        if ($context = $this->in->getString('context')) {
            $criteria['context_class'] = $this->filterContextClass($context);
            if ($cid = $this->in->getString('context_id')) {
                $criteria['context_id'] = $cid;
            }
        }

        $definitions = $this->em->getRepository('DeskPRO:CustomFieldDefinition')->findBy(
            $criteria,
            array('display_order' => 'ASC')
        );

        return $this->createApiResponse($this->getApiData($definitions, false));
    }

    /**
     * add child (choice) to custom field.
     *
     * @param Request $request
     * @param $id
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return JsonResponse
     */
    public function addChildAction(Request $request, $id)
    {
        /** @var $definition CustomFieldDefinition */
        if (!$definition = $this->em->find('DeskPRO:CustomFieldDefinition', $id)) {
            throw new NotFoundHttpException();
        }

        $context = $this->filterContextClass($this->in->getString('context'));
        if (!$context = $this->em->find($context, $this->in->getInt('context_id'))) {
            throw new NotFoundHttpException();
        }

        $form = $this->createForm(new SimpleDefinitionType(), null, array(
            'context' => $context,
            'parent'  => $definition,
        ));
        $form->submit($request->request->all());

        if ($form->isValid()) {
            $this->em->persist($form->getData());
            $this->em->flush();
        } else {
            $this->createApiErrorInfoResponse('form_error', 'Validation error.', array());
        }

        return $this->createApiResponse($this->getApiData($form->getData(), false));
    }

    ####################################################################################################################
    # get-custom-field
    ####################################################################################################################

    public function getAction($id)
    {
        return $this->createApiResponse($this->getDefinition($id)->toApiData());
    }

    ####################################################################################################################
    # save-custom-field
    ####################################################################################################################

    public function saveAction($id)
    {
        $post = $this->in->getAll('req');
        if (!$id || !($definition = $this->getDefinition($id))) {
            if (empty($post['form_type']) || empty($post['context_class'])) {
                throw new NotFoundHttpException();
            }
            $definition = new CustomFieldDefinition();

            // todo quite dirty
            $formType                    = Container::camelize($post['form_type']);
            $contextClass                = Container::camelize($post['context_class']);
            $definition['form_type']     = 'Application\DeskPRO\Form\Type\CustomFields\\'.$formType.'Type';
            $definition['context_class'] = 'Application\DeskPRO\Entity\\'.$contextClass;
            $definition['owner_class']   = 'Application\DeskPRO\Entity\Ticket';

            $this->em->persist($definition);
        }

        $form = $this->createForm($definition->createDefinitionType(), $definition, array(
            'context'   => new Ticket(),
            'persister' => new CustomDataPersister(),
        ))->submit($post);

        if (!$form->isValid()) {
            return $this->createApiErrorInfoResponse('form_error', 'Validation error', array());
        }

        $this->em->flush();

        return $this->getAction($definition['id']);
    }

    ####################################################################################################################
    # delete-custom-field
    ####################################################################################################################

    public function deleteAction($id)
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
        $definition               = $this->getDefinition($field_id);
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
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return null|object
     */
    protected function getDefinition($id)
    {
        $definition = $this->em->find('DeskPRO:CustomFieldDefinition', $id);
        if (!$definition || $definition->parent) {
            throw $this->createNotFoundException();
        }

        return $definition;
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteOptionAction(Request $request)
    {
        $types = array(
            'tickets'       => 'CustomDefTicket',
            'organizations' => 'CustomDefOrganization',
            'people'        => 'CustomDefPerson',
            'chats'         => 'CustomDefChat',
        );

        if (!$repClass = @$types[$this->in->getString('type')]) {
            throw new BadRequestHttpException();
        }
        if (!$ids = $this->in->getArrayValue('ids')) {
            throw new BadRequestHttpException();
        }

        if (!$ids = array_filter($ids, function ($id) {return 'cb_' !== substr($id, 0, 3);})) {
            throw new BadRequestHttpException();
        }
        /** @var CustomDefAbstract $rep */
        $rep  = $this->em->getRepository('DeskPRO:'.$repClass);
        $step = (int) $this->in->getInt('step');
        switch ($step) {

            case 1:
                $response = array('success' => $rep->hasData($ids));
                $field    = $rep->getByOptions($ids);
                $root     = (int) reset($ids);
                $options  = array();
                $map      = array();
                foreach ($field->children as $child) {
                    if ($pid = $child->getOption('parent_id')) {
                        if ($root !== $child['id']) {
                            $map[$pid] = 1;
                        }
                    }
                }

                foreach ($field->children as $child) {
                    $id = $child['id'];
                    if (!@$map[$id] && !in_array($id, $ids)) {
                        $options[$id] = $child['title'];
                    }
                }

                $response['options'] = $options ?: null;
                $response['default'] = $options ? key($options) : null;

                return $this->createJsonResponse($response);
                break;

            case 2:
                if (!$to = $this->in->getInt('update_to')) {
                    throw new BadRequestHttpException();
                }
                $rep->updateTo($ids, $to);

                return $this->createSuccessResponse();
                break;
        }

        throw new BadRequestHttpException();
    }

    /**
     * @param $objectType
     * @param $objectId
     * @param Request $request
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getCommonFieldsAction($objectType, $objectId, Request $request)
    {
        if (!isset(self::$allowed_common[$objectType])) {
            throw new BadRequestHttpException();
        }

        if (!$object = $this->em->find('DeskPRO:'.self::$allowed_common[$objectType], $objectId)) {
            throw new NotFoundHttpException();
        }

        /** @var FieldManager $manager */
        $manager = $this->container->getSystemService($objectType.'_fields_manager');
        $array   = $manager->getDisplayArrayForObject($object);
        $ret     = array();
        foreach ($array as $display_array) {
            /* @var $field FieldDisplayArray */
            if ($display_array instanceof FormView) {
                continue;
            }

            /** @var HandlerAbstract $handler */
            $handler = $display_array['handler'];

            if (is_object($display_array)) {
                $display_array = $display_array->toArray();
            }

            $data = array(
                'id'    => $display_array['id'],
                'title' => $display_array['title'],
                'type'  => $display_array['field_handler'],
                'value' => trim($handler->renderText($display_array['value'], $display_array)),
            );

            if ($handler instanceof Choice && @$display_array['value']['children']) {
                $data['children'] = $display_array['value']['children'];
            }

            $ret[] = $data;
        }

        return $this->createApiResponse($ret, 200);
    }

    /**
     * @param $objectType
     * @param $objectId
     * @param Request $request
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function setCommonFieldAction($objectType, $objectId, Request $request)
    {
        if (!$data = json_decode($request->getContent(), 1)) {
            throw new BadRequestHttpException();
        }

        if (!isset(self::$allowed_common[$objectType])) {
            throw new BadRequestHttpException(sprintf('Invalid object type "%s"', $objectType));
        }

        if (!$id = @$data['id']) {
            throw new BadRequestHttpException('No field id provided');
        }

        if (!$object = $this->em->find('DeskPRO:'.self::$allowed_common[$objectType], $objectId)) {
            throw new NotFoundHttpException();
        }

        /** @var FieldManager $manager */
        $manager = $this->container->getSystemService($objectType.'_fields_manager');
        $submit  = array(
            'field_'.$id => @$data['value'],
        );

        $manager->saveFormToObject($submit, $object);

        return $this->createSuccessResponse();
    }
}
