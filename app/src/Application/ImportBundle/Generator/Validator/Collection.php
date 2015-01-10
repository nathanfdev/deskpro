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

use Application\ImportBundle\AbstractCollection;

/**
 * Validators collection
 * Uses to check exporting collection of entities
 *
 * Class Collection
 * @package Application\ImportBundle\Generator\Validator
 */
class Collection extends AbstractCollection
{
    /**
     * Add a validator
     *
     * @param ValidatorInterface $validator
     * @return $this
     */
    public function attach(ValidatorInterface $validator)
    {
        $this->collection[] = $validator;
        return $this;
    }

    /**
     * Returns a new collection contains validators of current type
     *
     * @param string $type
     * @return Collection
     */
    public function getByRecordType($type)
    {
        $collection = new Collection();
        foreach ($this->collection as $validator) {
            /** @var ValidatorInterface $validator */
            if ($validator->getRecordType() === $type) {
                $collection->attach($validator);
            }
        }

        return $collection;
    }
}
