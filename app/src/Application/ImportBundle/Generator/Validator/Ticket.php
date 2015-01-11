<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
use Application\ImportBundle\Generator\GeneratorInterface;
use Exception;
use Symfony\Component\Validator\Validator;

/**
 * Tickets entities validator
 *
 * Class Ticket
 * @package Application\ImportBundle\Generator\Validator
 */
final class Ticket implements ValidatorInterface
{
    /**
     * @var \Symfony\Component\Validator\Validator
     */
    private $validator;

    /**
     * Constructor
     *
     * @param Validator $validator
     */
    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function getRecordType()
    {
        return GeneratorInterface::TYPE_TICKETS;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(Entity\EntityInterface $entity)
    {
        if (!$entity instanceof Entity\Ticket) {
            throw new Exception(sprintf(
                'Entity `%s` is not supported by validator `%s`',
                get_class($entity), get_class($this)
            ));
        }

        $errors = $this->validator->validate($entity);
        if (count($errors) > 0) {
            throw new Exception(sprintf('Entity `%s` is not valid', get_class($entity)));
        }
    }
}
