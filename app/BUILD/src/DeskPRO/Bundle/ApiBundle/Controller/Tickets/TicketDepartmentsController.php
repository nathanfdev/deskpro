<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets;

use Application\DeskPRO\Entity\Department;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\AbstractDepartmentsController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\DepartmentType;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class TicketDepartmentsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/ticket_departments")
 * @ApiDoc(target="all", section="Departments", output="DeskPRO\Bundle\AppBundle\Serializer\Model\Department")
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\DepartmentType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\Department",
 *          "type"="tickets"
 *      }
 *     }
 * )
 */
class TicketDepartmentsController extends AbstractDepartmentsController
{
    public static $entity    = Department::class;
    public static $type      = DepartmentType::class;
    public static $listOrder = 'asc';

    protected static $property       = 'is_tickets_enabled';
    protected static $departmentType = 'tickets';
    public static $listPaginate      = false;

    /**
     * {@inheritdoc}
     */
    protected function getAllowedDepartments()
    {
        return $this->get('data.departments')->getTicketDepartmentsForPerson($this->getUser());
    }
}
