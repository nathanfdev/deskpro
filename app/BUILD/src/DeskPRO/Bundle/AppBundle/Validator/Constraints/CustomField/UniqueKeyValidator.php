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
        $collection = new ArrayCollection();
        $collection->add($value);
        /* @var CustomDataAbstract $value */
        parent::validate($collection, $constraint);

        if (!$value instanceof CustomDataAbstract) {
            $this->createViolation($value, $constraint);
        }

        if ($this->em->getRepository(get_class($value))->findOneBy([
            'input' => $value->getInput(),
            'root_field' => $value->getRootField(),
        ])) {
            $this->createViolation($value, $constraint);
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
