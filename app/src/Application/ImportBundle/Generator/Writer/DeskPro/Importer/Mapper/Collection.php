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

namespace Application\ImportBundle\Generator\Writer\DeskPro\Importer\Mapper;

use Application\ImportBundle\AbstractCollection;

/**
 * Generator collection of mappers
 *
 * Class Collection
 * @package Application\ImportBundle\Generator\Writer\DeskPro\Importer\Mapper
 */
class Collection extends AbstractCollection
{
    /**
     * Add a record mapper
     *
     * @param MapperInterface $mapper
     * @return $this
     */
    public function attach(MapperInterface $mapper)
    {
        $this->collection[] = $mapper;
        return $this;
    }

    /**
     * Returns mapper by type
     *
     * @param string $type
     *
     * @return MapperInterface
     * @throws \Exception
     */
    public function getMapperByType($type)
    {
        foreach ($this->collection as $mapper) {
            /** @var MapperInterface $mapper */
            if ($mapper->getType() === $type) {
                return $mapper;
            }
        }

        throw new \Exception(sprintf('Mapper `%s` not found', $type));
    }
}
