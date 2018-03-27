<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Departments;

use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Exception\ValidationException;
use Doctrine\ORM\EntityManager;

class ChatDepartmentEditor
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
     * @param $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->db = $em->getConnection();
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

            $dep = $deps[$dep_id];

            $dep->display_order = $x;
            $this->em->persist($dep);

            $x += 10;
        }

        $this->em->flush();
    }

    /**
     * Deletes the department.
     */
    public function removeDepartment(Department $dep, Department $move_to_dep)
    {
        if ($move_to_dep->id == $dep->id) {
            throw ValidationException::create('department.move_chat.deps_are_same');
        }

        if (count($move_to_dep->getChildren())) {
            throw ValidationException::create('department.move_chat.dep_is_parent');
        }

        $old_id = $dep->id;
        $new_id = $move_to_dep->id;

        $this->db->beginTransaction();

        try {
            $this->db->executeUpdate(
                'UPDATE chat_conversations SET department_id = ? WHERE department_id = ?',
                [$new_id, $old_id]
            );

            $this->em->remove($dep);
            $this->em->flush();

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }

        return $old_id;
    }
}
