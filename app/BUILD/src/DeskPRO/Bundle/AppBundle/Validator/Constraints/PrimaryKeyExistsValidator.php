<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\Validator\Constraints;

use DeskPRO\Bundle\AppBundle\Form\Error\ApiErrors;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Orb\Util\Arrays;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PrimaryKeyExistsValidator extends ConstraintValidator
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof PrimaryKeyExists) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\PrimaryKeyExists');
        }

        if (null === $value) {
            return; // don't validate nulls
        }

        if (!is_array($value)) {
            $value = array($value); // force array
        }

        if (!$constraint->table) {
            throw new \InvalidArgumentException('PrimaryKeyExists must have a "table" defined');
        }

        $exclude = $constraint->excluded_values;
        if (!is_array($exclude)) {
            $exclude = array($exclude);
        }

        $check_ids = array();

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
                ->setParameter('ids', $check_ids, Connection::PARAM_INT_ARRAY);
            $count = $query->execute()->fetchColumn(0);

            if ((int) $count === count($check_ids)) {
                return; // valid
            }
        } catch (DBALException $e) {
            // catch any possible DB related errors so we can continue, this will lead to validation error
        }

        $this->buildViolation(ApiErrors::RESOURCE_NOT_FOUND)->addViolation();
    }
}
