<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\CustomField;

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
        parent::validate($value, $constraint);
    }
}
