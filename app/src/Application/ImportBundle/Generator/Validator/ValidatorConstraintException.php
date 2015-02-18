<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

namespace Application\ImportBundle\Generator\Validator;

use Application\ImportBundle\Entity;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * Exporter validator exception
 *
 * Class ValidatorException
 * @package Application\ImportBundle\Generator\Validator
 */
final class ValidatorConstraintException extends \Exception implements ValidatorExceptionInterface
{
    /**
     * @var Entity\EntityInterface
     */
    private $entity;

    /**
     * @var ConstraintViolationList
     */
    private $errors;

    /**
     * Constructor
     *
     * @param Entity\EntityInterface  $entity
     * @param ConstraintViolationList $errors
     */
    public function __construct(Entity\EntityInterface $entity, ConstraintViolationList $errors)
    {
        $this->entity = $entity;
        $this->errors = $errors;
    }

    /**
     * Returns the fail entity
     *
     * @return Entity\EntityInterface
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Returns a collection of the errors
     *
     * @return ConstraintViolationList
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Parse to string
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf("%s: %s", $this->entity->getDestination(), $this->errors);
    }
}
