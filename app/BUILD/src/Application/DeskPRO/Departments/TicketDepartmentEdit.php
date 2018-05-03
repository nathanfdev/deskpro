<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Departments;

use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Validator\HasValidationMetadataInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\ExecutionContextInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata as ValidatorClassMetadata;

class TicketDepartmentEdit implements HasValidationMetadataInterface
{
    /**
     * @var Department
     */
    public $department;

    /**
     * @var Department
     */
    public $move_department;

    /**
     * @var array
     */
    public $permissions;

    /**
     * @var Department|null
     */
    private $old_parent;

    public function __construct(Department $department)
    {
        $this->department = $department;

        if ($department->parent) {
            $this->old_parent = $department->parent;
        }
    }

    /**
     * @return bool
     */
    private function doesNeedMove()
    {
        $old = $this->old_parent;
        $new = $this->department->parent;

        // No parent, nothing to verify
        if (!$new) {
            return false;
            // Not changed, nothing to verify
        } elseif (($old && $new && $old == $new) || (!$old && !$new)) {
            return false;
            // New enabled
        } elseif (!$old && $new) {
            return true;

            // Changed
        } elseif ($old != $new) {
            return true;
        }

        return false;
    }

    /**
     * @param EntityManager $em
     */
    public function save(EntityManager $em)
    {
        $is_new = false;

        // New, we should set a proper display order
        if (!$this->department->id) {
            $is_new = true;
            $do     = $em->getConnection()->fetchColumn('
                SELECT display_order
                FROM departments
                WHERE is_tickets_enabled = 1
                ORDER BY display_order DESC
            ');
            $do += 10;
            $this->department->display_order = $do;
        }

        $em->persist($this->department);
        $em->flush();

        // Make sure parent doesnt have a trigger
        // and doesnt that the parent doesnt contain tickets
        if ($this->department->parent) {
            $em->getConnection()->delete('ticket_triggers', ['department_id' => $this->department->parent->id]);

            if ($is_new) {
                $em->getConnection()->update(
                    'tickets',
                    ['department_id' => $this->department->id],
                    ['department_id' => $this->department->parent->id]
                );
                $em->getConnection()->update(
                    'tickets_search_active',
                    ['department_id' => $this->department->id],
                    ['department_id' => $this->department->parent->id]
                );
            }
        }
    }

    /**
     * @param EntityManager                           $em
     * @param \Application\DeskPRO\Entity\Person[]    $agents
     * @param \Application\DeskPRO\Entity\Usergroup[] $groups
     */
    public function savePermissions(EntityManager $em, array $agents, array $groups)
    {
        $matrix = new DepartmentPermissionMatrix($agents, $groups);
        $matrix->setPermArray($this->permissions);
        $matrix->save($this->department, $em);
    }

    /**
     * @param EntityManager $em
     */
    public function clearTrigger(EntityManager $em)
    {
        $triggers = $em->createQuery('
            SELECT trigger
            FROM DeskPRO:TicketTrigger trigger
            WHERE trigger.department = ?0
        ')->setParameters([$this->department])->execute();

        foreach ($triggers as $t) {
            $em->remove($t);
        }
        $em->flush();
    }

    //###########################################################################
    // Validation Metadata
    //###########################################################################

    public function validateParent(ExecutionContextInterface $context)
    {
        if ($this->doesNeedMove()) {
            if (!$this->old_parent) {
                $context->addViolationAt('move_department', 'Setting a new parent, must specify new department to move existing tickets to');
            } elseif (count($this->old_parent->children)) {
                $context->addViolationAt('move_department', 'New department must not be a parent itself');
            }
        }
    }

    public static function loadValidatorMetadata(ValidatorClassMetadata $metadata)
    {
        // Symfony\Component\Validator\Exception\ConstraintDefinitionException:
        // The constraint Symfony\Component\Validator\Constraints\Callback cannot be put on properties or getters

        $metadata->addConstraint(new Callback([
            'methods' => ['validateParent'],
        ]));
    }
}
