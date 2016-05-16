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
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class FreeEmailValidator.
 */
class FreeEmailValidator extends ConstraintValidator
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
        if (!$constraint instanceof FreeEmail) {
            throw new UnexpectedTypeException($constraint, FreeEmail::class);
        }

        if ($value instanceof Entity\Person) {
            $this->validatePerson($value, $constraint);
        } elseif ($value instanceof Entity\PersonEmail) {
            $this->validatePersonEmail($value, $constraint);
        } else {
            throw new UnexpectedTypeException($value, Entity\Person::class.' or '.Entity\PersonEmail::class);
        }
    }

    /**
     * @param Entity\Person $value
     * @param FreeEmail     $constraint
     */
    protected function validatePerson(Entity\Person $value, FreeEmail $constraint)
    {
        /** @var \Application\DeskPRO\EntityRepository\Person $repository */
        $repository  = $this->em->getRepository(Entity\Person::class);
        $emailsInUse = [];

        foreach ($repository->findByEmails($value->getEmailAddresses()) as $existPerson) {
            if ($existPerson->getId() === $value->getId()) {
                continue;
            }

            $emailsInUse = array_merge($emailsInUse, $existPerson->getEmailAddresses());
        }

        /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
        $context = $this->context;

        $emailsInUse = array_unique($emailsInUse);
        foreach ($emailsInUse as $email) {
            $context
                ->buildViolation($constraint->message)
                ->setParameter('email', $email)
                ->setCode(FreeEmail::DUPE_EMAIL)
                ->atPath($constraint->property)
                ->addViolation()
            ;
        }
    }

    /**
     * @param Entity\PersonEmail $value
     * @param FreeEmail          $constraint
     */
    protected function validatePersonEmail(Entity\PersonEmail $value, FreeEmail $constraint)
    {
        $existEmail = $this->em->getRepository(Entity\PersonEmail::class)->findOneBy([
            'email' => $value->getEmail(),
        ]);

        if ($existEmail && $existEmail->getId() !== $value->getId()) {
            /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
            $context = $this->context;
            $context
                ->buildViolation($constraint->message)
                ->setParameter('email', $value->getEmail())
                ->setCode(FreeEmail::DUPE_EMAIL)
                ->atPath($constraint->property)
                ->addViolation()
            ;
        }
    }
}
