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

use Application\DeskPRO\Entity;
use Application\DeskPRO\EntityRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class FreeEmailValidator.
 */
class FreeEmailValidator extends ConstraintValidator
{
    /**
     * @var EntityRepository\Person
     */
    private $person_repository;

    /**
     * Constructor.
     *
     * @param EntityRepository\Person $person_repository
     */
    public function __construct(EntityRepository\Person $person_repository)
    {
        $this->person_repository = $person_repository;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof FreeEmail) {
            throw new UnexpectedTypeException($constraint, FreeEmail::class);
        }

        if (!$value instanceof Entity\Person) {
            throw new UnexpectedTypeException($value, Entity\Person::class);
        }

        $exist_persons = $this->person_repository->findByEmails($value->getEmailAddresses());
        $exist_emails  = [];
        foreach ($exist_persons as $exist_person) {
            if ($exist_person->getId() === $value->getId()) {
                continue;
            }

            $exist_emails = array_merge($exist_emails, $exist_person->getEmailAddresses());
        }

        /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
        $context = $this->context;

        $exist_emails = array_unique($exist_emails);
        foreach ($exist_emails as $email) {
            $context
                ->buildViolation($constraint->message)
                ->setParameter('email', $email)
                ->setCode(FreeEmail::DUPE_EMAIL)
                ->atPath($constraint->property)
                ->addViolation()
            ;
        }
    }
}
