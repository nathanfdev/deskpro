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

use Application\ApiBundle\PermissionStrategy\AdminManagePermission;
use Application\ApiBundle\PermissionStrategy\MultiPermissions;
use Application\ApiBundle\PermissionStrategy\PassPermission;
use Application\DeskPRO\CustomFields\CustomDataPersister;
use Application\DeskPRO\Entity\CustomFieldDefinition;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Form\Type\CustomFields\Definitions\SimpleDefinitionType;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CustomFieldsController extends AbstractController implements ProtectedControllerInterface
{
    protected $allowed = array(
        'owner' => array('ticket', 'person'),
        'context' => array('person', 'organization'),
    );

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

    protected function filterContextClass($context)
    {
        $context = strtolower($context);
        if (!in_array($context, $this->allowed['context'])) {
            throw new NotFoundHttpException;
        }

        return 'Application\DeskPRO\Entity\\' . Container::camelize($context);
    }

    protected function filterOwnerClass($owner)
    {
        $owner = strtolower($owner);
        if (!in_array($owner, $this->allowed['owner'])) {
            throw new NotFoundHttpException;
        }

        return 'Application\DeskPRO\Entity\\' . Container::camelize($owner);
    }

    ####################################################################################################################
    # list
    ####################################################################################################################

    /**
     * @param  Request  $request
     * @return Response
     */
    public function listAction(Request $request)
    {
        $criteria = array('parent' => null);

        if ($owner = $request->get('owner')) {
            $criteria['owner_class'] = $this->filterOwnerClass($owner);
        }

        if ($context = $request->get('context')) {
            $criteria['context_class'] = $this->filterContextClass($context);
        }

        $definitions = $this->em->getRepository('DeskPRO:CustomFieldDefinition')->findBy(
            $criteria,
            array('display_order' => 'ASC')
        );

        return $this->createApiResponse($this->getApiData($definitions, false));
    }

    /**
     * get children (choices) of custom field
     *
     * @param  Request  $request
     * @param $id
     * @return Response
     */
    public function childrenAction(Request $request, $id)
    {
        $criteria = array('parent' => $id);

        if ($context = $request->get('context')) {
            $criteria['context_class'] = $this->filterContextClass($context);
            if ($cid = $request->get('context_id')) {
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
     * add child (choice) to custom field
     * @param  Request                                                       $request
     * @param $id
     * @return Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function addChildAction(Request $request, $id)
    {
        /** @var $definition CustomFieldDefinition */
        if (!$definition = $this->em->find('DeskPRO:CustomFieldDefinition', $id)) {
            throw new NotFoundHttpException;
        }

        $context = $this->filterContextClass($request->get('context'));
        if (!$context = $this->em->find($context, $request->get('context_id'))) {
            throw new NotFoundHttpException;
        }

        $form = $this->createForm(new SimpleDefinitionType(), null, array(
            'context' => $context,
            'parent' => $definition,
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
                throw new NotFoundHttpException;
            }
            $definition = new CustomFieldDefinition();

            // todo quite dirty
            $formType = Container::camelize($post['form_type']);
            $contextClass = Container::camelize($post['context_class']);
            $definition['form_type'] = 'Application\DeskPRO\Form\Type\CustomFields\\' . $formType . 'Type';
            $definition['context_class'] = 'Application\DeskPRO\Entity\\' . $contextClass;
            $definition['owner_class'] = 'Application\DeskPRO\Entity\Ticket';

            $this->em->persist($definition);
        }

        $form = $this->createForm($definition->createDefinitionType(), $definition, array(
            'context' => new Ticket(),
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
