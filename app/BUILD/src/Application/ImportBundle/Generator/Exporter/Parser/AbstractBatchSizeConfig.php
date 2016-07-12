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

namespace Application\ImportBundle\Generator\Exporter\Parser;

/**
 * Common properties of batch config with batch size.
 *
 * Class AbstractBatchSizeConfig
 */
abstract class AbstractBatchSizeConfig extends AbstractBatchConfig implements BatchSizeConfigInterface
{
    /**
     * @var int
     */
    protected $batch_size = self::BATCH_SIZE_DEFAULT;

    /**
     * {@inheritdoc}
     */
    public function getBatchSize()
    {
        return $this->batch_size;
    }

    /**
     * {@inheritdoc}
     */
    public function setBatchSize($batch_size)
    {
        if ($batch_size > self::BATCH_SIZE_MAX) {
            $batch_size = self::BATCH_SIZE_MAX;
        }
        if ($batch_size < 1) {
            $batch_size = self::BATCH_SIZE_DEFAULT;
        }

        $this->batch_size = (int) $batch_size;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return array_merge(parent::toArray(), [
            'batch_size' => $this->batch_size,
        ]);
    }
}
