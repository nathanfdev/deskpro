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

namespace Application\ImportBundle\Generator\Writer;

use Application\ImportBundle\Entity\EntityInterface;
use Application\ImportBundle\Generator\Exporter\Parser\BatchConfigInterface;
use Application\ImportBundle\Generator\GeneratorConfigAwareInterface;

/**
 * Generator writer interface.
 *
 * Interface WriterInterface
 */
interface WriterInterface extends GeneratorConfigAwareInterface
{
    const TYPE_JSON     = 'json';
    const TYPE_DESK_PRO = 'deskpro';

    const OUTPUT_BATCH_FILE = 'output.batch.json';
    const INPUT_BATCH_FILE  = 'input.batch.json';

    /**
     * Returns the writer type.
     *
     * @return string
     */
    public function getType();

    /**
     * Set batch configuration.
     *
     * @param BatchConfigInterface $config
     *
     * @return $this
     */
    public function setBatchConfig(BatchConfigInterface $config);

    /**
     * Set list of writing entity types.
     *
     * @param array $types
     *
     * @return mixed
     */
    public function setWritingEntityTypes(array $types);

    /**
     * Prepares a writer to store data.
     *
     * @return bool
     */
    public function prepare();

    /**
     * Writes an entity to the storage.
     *
     * @param EntityInterface $entity
     *
     * @return bool
     */
    public function writeData(EntityInterface $entity);

    /**
     * Writes updated batch config.
     *
     * @return bool
     */
    public function writeBatchConfig();
}
