<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Person;

use Application\DeskPRO\Entity\Person;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class PersonTypeValidator.
 */
class PersonTypeValidator extends ConstraintValidator
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
        if (!$constraint instanceof PersonType) {
            throw new UnexpectedTypeException($constraint, PersonType::class);
        }

        if (!$value) {
            return;
        }
        if (!$value instanceof Person && !is_scalar($value)) {
            throw new UnexpectedTypeException($value, Person::class.' or scalar');
        }

        if (!$value instanceof Person) {
            $qb = $this->em->createQueryBuilder();
            $qb
                ->select('p')
                ->from(Person::class, 'p')
                ->setParameter('value', $value)
            ;

            if (is_numeric($value)) {
                $qb->where('p.id = :value');
            } else {
                $qb->join('p.emails', 'e');
                $qb->where('e.email = :value');
            }

            $value = $qb->getQuery()->getOneOrNullResult();
            if (!$value) {
                return;
            }
        }

        /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
        $context = $this->context;

        if ($constraint->type === 'agent') {
            if (!$value->isAgent()) {
                $context
                    ->buildViolation($constraint->notAgentMessage)
                    ->setParameter('{{ value }}', $this->formatValue($value->getEmailAddress()))
                    ->setCode(PersonType::PERSON_NOT_AGENT)
                    ->addViolation()
                ;
            }
        } elseif ($constraint->type === 'user') {
            if ($value->isAgent()) {
                $context
                    ->buildViolation($constraint->notUserMessage)
                    ->setParameter('{{ value }}', $this->formatValue($value->getEmailAddress()))
                    ->setCode(PersonType::PERSON_NOT_USER)
                    ->addViolation()
                ;
            }
        }
    }
}
