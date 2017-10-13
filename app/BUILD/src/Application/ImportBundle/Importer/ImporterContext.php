<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace Application\ImportBundle\Importer;

use Application\ImportBundle\Model\BatchConfig;

/**
 * Configuration of generator importer service.
 *
 * Class GeneratorConfig
 */
class ImporterContext
{
    /**
     * @var BatchConfig
     */
    private $batchConfig;

    /**
     * @var string
     */
    private $inputPath;

    /**
     * @var string
     */
    private $brand;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->batchConfig = new BatchConfig();
    }

    /**
     * Input path of exporting data.
     *
     * @return string
     */
    public function getInputPath()
    {
        return $this->inputPath;
    }

    /**
     * Set an input path.
     *
     * @param string $inputPath
     *
     * @return $this
     */
    public function setInputPath($inputPath)
    {
        $this->inputPath = $inputPath ? (rtrim($inputPath, '/').'/') : null;

        return $this;
    }

    /**
     * Returns batch file path location.
     *
     * @return null|string
     */
    public function getBatchFilePath()
    {
        return $this->inputPath.'batch.json';
    }

    /**
     * @return BatchConfig
     */
    public function getBatchConfig()
    {
        return $this->batchConfig;
    }

    /**
     * @param BatchConfig $batchConfig
     */
    public function setBatchConfig(BatchConfig $batchConfig = null)
    {
        $this->batchConfig = $batchConfig ?: new BatchConfig();
    }

    /**
     * @return string
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @param string $brand
     *
     * @return $this
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;

        return $this;
    }
}
