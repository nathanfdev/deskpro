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

namespace Application\ImportBundle\Generator\Writer\DeskPRO\Importer;

use DeskPRO\Component\Util\AbstractCollection;

/**
 * Collection of DeskPRO importers.
 *
 * Class Collection
 */
final class Collection extends AbstractCollection
{
    /**
     * Add an importer.
     *
     * @param ImporterInterface $importer
     *
     * @return $this
     */
    public function attach(ImporterInterface $importer)
    {
        $this->collection[] = $importer;

        return $this;
    }

    /**
     * Returns an importer by entity type.
     *
     * @param string $type
     *
     * @throws \Exception
     *
     * @return Collection
     */
    public function getByEntityType($type)
    {
        $collection = new self();

        foreach ($this->collection as $importer) {
            /** @var ImporterInterface $importer */
            if ($importer->getEntityType() === $type) {
                $collection->attach($importer);
            }
        }

        if ($collection->count() === 0) {
            throw new \Exception(sprintf('Entity `%s` not supported', $type));
        }

        return $collection;
    }
}
