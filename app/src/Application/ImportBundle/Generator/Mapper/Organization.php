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

namespace Application\ImportBundle\Generator\Mapper;

use Application\DeskPRO\EntityRepository;
use Application\ImportBundle\RecordMapper\RecordMapperInterface;

/**
 * Organization record mapper
 *
 * Class Organization
 * @package Application\ImportBundle\Generator\Mapper
 */
class Organization implements MapperInterface
{
    /**
     * @var EntityRepository\Organization
     */
    private $organization_repository;

    /**
     * Constructor
     *
     * @param EntityRepository\Organization $organization_repository
     */
    public function __construct(EntityRepository\Organization $organization_repository)
    {
        $this->organization_repository = $organization_repository;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return RecordMapperInterface::TYPE_ORGANIZATION;
    }

    /**
     * {@inheritdoc}
     */
    public function findIdByValue($value)
    {
        $organization = $this->organization_repository->findOneByName($value);
        if (!$organization) {
            throw new MapperException(sprintf('Organization `%s` not found', $value));
        }

        return $organization->getId();
    }
}
