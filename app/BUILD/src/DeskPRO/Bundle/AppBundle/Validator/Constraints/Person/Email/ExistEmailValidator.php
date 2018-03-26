<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Person\Email;

use Application\DeskPRO\Entity\PersonEmail;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class ExistEmailValidator.
 */
class ExistEmailValidator extends ConstraintValidator
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
        if (!$constraint instanceof ExistEmail) {
            throw new UnexpectedTypeException($constraint, ExistEmail::class);
        }

        if (!$value) {
            return;
        }

        $personEmail = $this->em->getRepository(PersonEmail::class)->findOneBy([
            'email' => $value,
        ]);

        if (!$personEmail instanceof PersonEmail) {
            /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
            $context = $this->context;
            $context
                ->buildViolation($constraint->notFoundMessage)
                ->setParameter('value', $value)
                ->setCode(ExistEmail::NON_EXISTING_EMAIL)
                ->addViolation()
            ;
        }
    }
}
