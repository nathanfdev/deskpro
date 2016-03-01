<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets;

use Application\DeskPRO\Entity\Department;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\DepartmentType;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TicketDepartmentsController.
 *
 * @ApiModes("all")
 * @Route("/ticket_departments")
 */
class TicketDepartmentsController extends CrudController
{
    public static $entity    = Department::class;
    public static $type      = DepartmentType::class;
    public static $listOrder = 'asc';

    /**
     * @ApiDoc(
     *      description="Get agents belongs to department",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the department",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Success",
     *          404="Not Found"
     *      },
     *      output="Application\DeskPRO\Entity\Department"
     * )
     * @Get("/{id}/agents")
     *
     * @param int $id
     *
     * @return View
     */
    public function getAgentsAction($id)
    {
        $department = $this->findEntity($id);

        return View::create($this->dataSerialize($department->getPersonList()));
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $qb->andWhere('e.is_tickets_enabled = true');

        if ($request->query->getBoolean('my', false)) {
            $permission_bag         = $this->get('permissions_manager')->getPortalPermissionsBag($this->getUser());
            $allowed_department_ids = $permission_bag->getAllowedTicketDepartmentIds();

            $qb
                ->andWhere('e.id IN (:allowed_department_ids)')
                ->setParameter('allowed_department_ids', $allowed_department_ids)
            ;
        }

        if ($request->query->get('ids')) {
            $qb
                ->andWhere('e.id IN (:ids)')
                ->setParameter('ids', $request->query->get('ids'))
            ;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $options = array_merge($options, [
            'type' => 'tickets',
        ]);

        return parent::handleForm($model, $request, $options);
    }

    /**
     * @param int $id
     *
     * @return object
     */
    protected function findEntity($id)
    {
        /** @var Department $entity */
        $entity = parent::findEntity($id);
        if (!$entity->is_tickets_enabled) {
            throw $this->createNotFoundException();
        }

        return $entity;
    }
}
