<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Orb\Util\Arrays;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class PrimaryKeyExistsValidator.
 */
class PrimaryKeyExistsValidator extends ConstraintValidator
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * Constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof PrimaryKeyExists) {
            throw new UnexpectedTypeException($constraint, PrimaryKeyExists::class);
        }

        if (null === $value) {
            return; // don't validate nulls
        }
        if (!is_array($value)) {
            $value = [$value]; // force array
        }

        if (!$constraint->table) {
            throw new \InvalidArgumentException('PrimaryKeyExists must have a "table" defined');
        }

        $exclude = $constraint->excluded_values;
        if (!is_array($exclude)) {
            $exclude = [$exclude];
        }

        $check_ids = [];

        foreach ($value as $id) {
            if (!in_array($id, $exclude)) {
                $check_ids[] = $id;
            }
        }

        $check_ids = Arrays::flatten($check_ids);

        try {
            $query = $this->connection->createQueryBuilder()
                ->select('COUNT(alias.id) as cc')
                ->from($constraint->table, 'alias')
                ->where('alias.id IN (:ids)')
                ->setParameter('ids', $check_ids, Connection::PARAM_INT_ARRAY)
            ;

            $count = $query->execute()->fetchColumn(0);
            if ((int) $count === count($check_ids)) {
                return; // valid
            }
        } catch (DBALException $e) {
            // catch any possible DB related errors so we can continue, this will lead to validation error
        }

        /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
        $context = $this->context;
        $context
            ->buildViolation($constraint->message)
            ->setCode(PrimaryKeyExists::RESOURCE_NOT_FOUND)
            ->addViolation()
        ;
    }
}
