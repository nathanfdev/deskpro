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
namespace DeskPRO\Bundle\ApiBundle\Controller;

use Application\DeskPRO\Entity\Department;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\DepartmentType;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class DepartmentsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/departments")
 * @ApiDoc(target="all", section="Departments", output="DeskPRO\Bundle\AppBundle\Serializer\Model\Department")
 */
class DepartmentsController extends CrudController
{
    public static $entity    = Department::class;
    public static $type      = DepartmentType::class;
    public static $listOrder = 'asc';

    /**
     * @ApiDoc(
     *     section="Departments",
     *     description="Get agents belongs to department",
     *     requirements={
     *         {
     *             "name"="id",
     *             "requirement"="\d+",
     *             "description"="the id of the department",
     *             "dataType"="integer"
     *         }
     *     },
     *     statusCodes={
     *         200="Returned if everything is OK",
     *         404="Returned if department wasn't found"
     *     },
     *     output="array<DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person>"
     * )
     * @Rest\Get("/{id}/agents")
     *
     * @param int     $id
     * @param Request $request
     *
     * @return View
     */
    public function getAgentsAction($id, Request $request)
    {
        $department = $this->findEntity($id, $request);

        return View::create($this->wrap($department->getPersonList()));
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $departmentType = $request->get('type');

        if ($departmentType && !in_array($departmentType, ['tickets', 'chat'])) {
            throw new BadRequestHttpException('Department type should be "tickets", "chat" or just omit it to fetch all');
        }

        if ($departmentType === 'tickets') {
            $qb->andWhere('e.is_tickets_enabled = true');
            $method = 'getAllowedTicketDepartmentIds';
        } elseif ($departmentType === 'chat') {
            $qb->andWhere('e.is_chat_enabled = true');
            $method = 'getAllowedChatDepartmentIds';
        } else {
            $method = 'getAllowedDepartmentIds';
        }

        if ($request->query->getBoolean('my', false)) {
            $permissionBag = $this->get('permissions_manager')->getPortalPermissionsBag($this->getUser());

            $allowedDepartmentIds = $permissionBag->$method();
            $qb->andWhere('e.id IN (:allowed_department_ids)');
            $qb->setParameter('allowed_department_ids', $allowedDepartmentIds);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $options = array_merge($options, [
            'type' => $request->get('type', 'tickets'),
        ]);

        return parent::handleForm($model, $request, $options);
    }

    /**
     * {@inheritdoc}
     */
    protected function findEntity($id, Request $request)
    {
        /** @var Department $entity */
        $entity         = parent::findEntity($id, $request);
        $departmentType = $request->get('type', 'tickets');
        if (!in_array($departmentType, ['tickets', 'chat'])) {
            throw new BadRequestHttpException('Department type should be either "tickets" or "chat"');
        }
        $property = sprintf('is_%s_enabled', $departmentType);
        if (!$entity->$property) {
            throw $this->createNotFoundException();
        }

        return $entity;
    }
}
