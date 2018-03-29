<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Departments;

use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Validator\HasValidationMetadataInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\ExecutionContextInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata as ValidatorClassMetadata;

class ChatDepartmentEdit implements HasValidationMetadataInterface
{
    /**
     * @var \Application\DeskPRO\Entity\Department
     */
    public $department;

    /**
     * @var \Application\DeskPRO\Entity\Department
     */
    public $move_department;

    /**
     * @var array
     */
    public $permissions;

    /**
     * @var \Application\DeskPRO\Entity\Department|null
     */
    private $old_parent;

    public function __construct(Department $department)
    {
        $this->department  = $department;
        $this->permissions = new ArrayCollection(); // this is needed for proper validation of 'permissions'

        if ($department->parent) {
            $this->old_parent = $department->parent;
        }
    }

    /**
     * @param EntityManager $em
     */
    public function save(EntityManager $em)
    {
        $em->persist($this->department);
        $em->flush();
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

    //###########################################################################
    // Validation Metadata
    //###########################################################################

    /**
     * @param ExecutionContextInterface $context
     */
    public function validateChangingOfParent(ExecutionContextInterface $context)
    {
        if (!$this->old_parent) {
            if (sizeof($this->department->getChildren()) > 0 && $this->department->getParentId() != 0) {
                $context->addViolationAt(
                    'edit_department',
                    'department.edit_chat.changing_parent_when_have_children'
                );
            }
        }
    }

    /**
     * @param ValidatorClassMetadata $metadata
     */
    public static function loadValidatorMetadata(ValidatorClassMetadata $metadata)
    {
        $metadata->addConstraint(
            new Callback(
                [
                     'methods' => ['validateChangingOfParent'],
                ]
            )
        );
    }
}
