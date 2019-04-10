<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Person\Email;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class NotAgentEmailValidator.
 */
class NotAgentEmailValidator extends ConstraintValidator
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value || !trim($value)) {
            return;
        }

        $value = trim($value);

        $cnt = $this->em->createQuery('
            SELECT COUNT(e.id)
            FROM DeskPRO:PersonEmail e
            JOIN e.person p
            WHERE p.is_agent = true
            AND e.email LIKE ?1
        ')->setParameter(1, $value)->getSingleScalarResult();

        if ($cnt) {
            $violation = $this->context
                ->buildViolation($constraint->isAgentMessage)
                ->setParameter('email', $value)
                ->setCode(NotAgentEmail::IS_AGENT_EMAIL);
            if ($constraint->property) {
                $violation->atPath($constraint->property);
            }
            $violation->addViolation();
        }
    }
}
