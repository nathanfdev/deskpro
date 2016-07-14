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

namespace Application\ImportBundle\Importer;

use Application\ImportBundle\Model;
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
    private $logPath = '/import.log';

    /**
     * @var bool
     */
    private $verbose = false;

    /**
     * @var bool
     */
    private $dryRun = false;

    /**
     * @var bool
     */
    private $silent = false;

    /**
     * @return array
     */
    public static function getOrderedTypes()
    {
        return [
            Model\OrganizationCustomDef::class,
            Model\TicketCustomDef::class,
            Model\PersonCustomDef::class,
            Model\ArticleCustomDef::class,
            Model\FeedbackCustomDef::class,
            Model\Organization::class,
            Model\Person::class,
            Model\Ticket::class,
            Model\ArticleCategory::class,
            Model\Article::class,
            Model\Download::class,
            Model\Feedback::class,
            Model\News::class,
        ];
    }

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
     * Returns a log path.
     *
     * @return string
     */
    public function getLogPath()
    {
        return $this->logPath;
    }

    /**
     * Set a log path.
     *
     * @param string $logPath
     *
     * @return $this
     */
    public function setLogPath($logPath)
    {
        $this->logPath = $logPath;

        return $this;
    }

    /**
     * Is verbose mode enabled.
     *
     * @return bool
     */
    public function isVerbose()
    {
        return $this->verbose;
    }

    /**
     * Set verbose mode
     * All output messages are shown in console.
     *
     * @param bool $verbose
     *
     * @return $this
     */
    public function setVerbose($verbose)
    {
        $this->verbose = (bool) $verbose;

        return $this;
    }

    /**
     * Returns true if a writer does not flush data.
     *
     * @return bool
     */
    public function isDryRun()
    {
        return $this->dryRun;
    }

    /**
     * Set a writer not to flush data.
     *
     * @param bool $dryRun
     *
     * @return $this
     */
    public function setDryRun($dryRun)
    {
        $this->dryRun = (bool) $dryRun;

        return $this;
    }

    /**
     * Is silent mode.
     *
     * @return bool
     */
    public function isSilent()
    {
        return $this->silent;
    }

    /**
     * Set silent mode
     * No progressbar or output messages.
     *
     * @param bool $silent
     *
     * @return $this
     */
    public function setSilent($silent)
    {
        $this->silent = (bool) $silent;

        return $this;
    }

    /**
     * Shows progressbar.
     *
     * @return bool
     */
    public function isProgressbarEnabled()
    {
        return !$this->verbose && !$this->silent;
    }

    /**
     * Shows output.
     *
     * @return bool
     */
    public function isConsoleOutputEnabled()
    {
        return $this->verbose && !$this->silent;
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
}
