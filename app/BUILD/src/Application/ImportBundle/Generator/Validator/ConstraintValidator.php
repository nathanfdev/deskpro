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

namespace Application\ImportBundle\Generator\Validator;

use Application\ImportBundle\Entity;
use Symfony\Component\Validator\ValidatorInterface as SymfonyValidator;

/**
 * Symfony constraint validator.
 *
 * Class AbstractConstraintValidator
 */
final class ConstraintValidator implements ValidatorInterface
{
    /**
     * @var SymfonyValidator
     */
    private $validator;

    /**
     * @var string
     */
    private $entity_type;

    /**
     * Constructor.
     *
     * @param SymfonyValidator $validator
     * @param string           $entity_type
     */
    public function __construct(SymfonyValidator $validator, $entity_type)
    {
        $this->validator   = $validator;
        $this->entity_type = $entity_type;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return $this->entity_type;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(Entity\EntityInterface $entity)
    {
        $errors = $this->validator->validate($entity);
        if (count($errors) > 0) {
            throw new ValidatorConstraintException($entity, $errors);
        }
    }
}
