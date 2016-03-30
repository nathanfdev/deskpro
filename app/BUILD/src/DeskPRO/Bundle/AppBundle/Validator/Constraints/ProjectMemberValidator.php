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

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints;

use DeskPRO\Bundle\AppBundle\Entity\ProjectMember as ProjectMemberEntity;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class ProjectMemberValidator.
 */
class ProjectMemberValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($object, Constraint $constraint)
    {
        if (!$constraint instanceof ProjectMember) {
            throw new UnexpectedTypeException($constraint, ProjectMember::class);
        }
        if (!$object instanceof ProjectMemberEntity) {
            throw new UnexpectedTypeException($object, ProjectMemberEntity::class);
        }

        $fields = [
            'team'       => $object->getTeam() ? 1 : 0,
            'department' => $object->getDepartment() ? 1 : 0,
            'person'     => $object->getPerson() ? 1 : 0,
        ];

        if (array_sum($fields) !== 1) {
            /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
            $context = $this->context;
            $context
                ->buildViolation($constraint->message)
                ->setParameter('{{ values }}', $this->formatValue(implode(', ', array_keys($fields))))
                ->setCode(ProjectMember::EXACTLY_ONE_SHOULD_BE_SET)
                ->addViolation()
            ;
        }
    }
}
