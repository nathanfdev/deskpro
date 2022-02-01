<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\CustomField;

use Application\DeskPRO\Entity\CustomDataAbstract;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Custom field unique key validator
 */
class UniqueKeyValidator extends AbstractSingleValueValidator
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
    protected function getValidators($data, AbstractCustomDefConstraint $constraint)
    {
        $validators[] = new Assert\NotBlank();

        // Length validator
        $minLength = (int) $constraint->getCustomDefOption('min_length', true);

        $lengthOptions = [];
        if ($minLength) {
            $lengthOptions['min'] = $minLength;
        }

        if (!empty($lengthOptions)) {
            $validators[] = new Assert\Length($lengthOptions);
        }

        return $validators;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof ArrayCollection) {
            $collection = new ArrayCollection();
            $collection->add($value);
        } else {
            $collection = $value;
        }

        /* @var CustomDataAbstract $value */
        parent::validate($collection, $constraint);

        $valueToCheck = $collection->first();

        if ($valueToCheck) { // there's no value to check, means no unique key is set
            if (!$valueToCheck instanceof CustomDataAbstract) {
                $this->createViolation($valueToCheck, $constraint);
            }

            $customPersonData = $this->em->getRepository(get_class($valueToCheck))->findOneBy([
                'input'      => $valueToCheck->getInput(),
                'root_field' => $valueToCheck->getRootField(),
            ]);
            // that validator is also used in legacy bundles, and valueToCheck is created on the fly, s
            if ($customPersonData && ($customPersonData->getId() !== $valueToCheck->getId())) {
                $this->createViolation($valueToCheck, $constraint);
            }
        }
    }

    protected function createViolation($value, Constraint $constraint)
    {
        /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
        $context = $this->context;
        $context
            ->buildViolation($constraint->message)
            ->setParameter('key', $value->getInput())
            ->setCode($constraint->getErrorCode())
            ->atPath('['.$value->getRootField()->getId().']')
            ->addViolation()
        ;
    }
}
