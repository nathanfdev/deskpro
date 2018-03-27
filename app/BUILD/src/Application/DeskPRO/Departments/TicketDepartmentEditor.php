<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Departments;

use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Exception\ValidationException;
use Doctrine\ORM\EntityManager;

class TicketDepartmentEditor
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    protected $db;

    /**
     * @var \Application\DeskPRO\Entity\Department
     */
    protected $dep;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->db = $em->getConnection();
    }

    /**
     * Deletes the department.
     *
     * @param Department $dep
     * @param Department $move_to
     *
     * @throws ValidationException
     *
     * @return int
     */
    public function removeDepartment(Department $dep, Department $move_to)
    {
        if ($move_to->id == $dep->id) {
            throw ValidationException::create('department.remove.move_tickets', 'You must choose a different department');
        }

        if (count($move_to->getChildren())) {
            throw ValidationException::create('department.remove.move_tickets', 'Department cannot be a parent');
        }

        $old_id = $dep->id;

        $this->db->executeUpdate('UPDATE tickets SET department_id = ? WHERE department_id = ?', [$move_to['id'], $old_id]);
        $this->db->executeUpdate('UPDATE tickets_search_active SET department_id = ? WHERE department_id = ?', [$move_to['id'], $old_id]);

        $this->em->remove($dep);
        $this->em->flush();

        return $old_id;
    }

    /**
     * @param array $orders
     */
    public function updateDisplayOrders($orders)
    {
        $x    = 10;
        $deps = $this->em->getRepository('DeskPRO:Department')->getByIds($orders);

        foreach ($orders as $dep_id) {
            if (!isset($deps[$dep_id])) {
                continue;
            }

            $dep                = $deps[$dep_id];
            $dep->display_order = $x;
            $this->em->persist($dep);

            $x += 10;
        }

        $this->em->flush();
    }
}
