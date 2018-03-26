<?php

/*
 * Deskpro (r) has been developed by Deskpro Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, Deskpro Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that Deskpro is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing Deskpro since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team Deskpro
 */

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints;

use DeskPRO\Bundle\AppBundle\Entity\ObjectAlias\AbstractAlias;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class ObjectAliasValidator.
 */
class ObjectAliasValidator extends ConstraintValidator
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
        if (!$constraint instanceof ObjectAlias) {
            throw new UnexpectedTypeException($constraint, CountryCode::class);
        }
        if (!is_object($constraint->owner)) {
            throw new \InvalidArgumentException('Alias owner is not defined');
        }

        if (!$value) {
            return;
        }

        if (is_array($value)) {
            foreach ($value as $alias) {
                $this->validateAlias($constraint, $alias);
            }
        } else {
            $this->validateAlias($constraint, $value);
        }
    }

    /**
     * @param Constraint $constraint
     * @param string     $alias
     */
    private function validateAlias(Constraint $constraint, $alias)
    {
        if (!preg_match('/^[A-Za-z0-9\.\-_:]+$/', $alias)) {
            /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
            $context = $this->context;
            $context
                ->buildViolation($constraint->invalidFormatMessage)
                ->setCode(ObjectAlias::INVALID_ALIAS_FORMAT)
                ->addViolation()
            ;
        }

        $existAlias = $this->em->getRepository(AbstractAlias::class)->findOneBy([
            'alias' => $alias,
        ]);

        if ($existAlias && $existAlias->getObject() !== $constraint->owner) {
            /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
            $context = $this->context;
            $context
                ->buildViolation($constraint->notUniqueMessage)
                ->setCode(ObjectAlias::NOT_UNIQUE_ALIAS)
                ->setParameter('alias', $alias)
                ->addViolation()
            ;
        }
    }
}
