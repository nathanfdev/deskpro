<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
