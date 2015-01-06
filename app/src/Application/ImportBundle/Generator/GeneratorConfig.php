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

/**
 * @package Importer
 */

namespace Application\ImportBundle\Generator;

/**
 * Configuration of generator importer service
 *
 * Class GeneratorConfig
 * @package Application\ImportBundle\Generator
 */
class GeneratorConfig
{
    const MODE_TEST = 'test';
    const MODE_LIVE = 'live';

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $input_path;

    /**
     * @var string
     */
    private $output_path;

    /**
     * @var string
     */
    private $log_path;

    /**
     * 'test' or 'live'
     *
     * @var string
     */
    private $mode = self::MODE_TEST;

    /**
     * Mark files as done when they are finished importing?
     *
     * @var bool
     */
    private $mark_done = true;

    /**
     * @var int
     */
    private $batch_size = 10;

    /**
     * Not used yet
     *
     * @var int|null
     */
//    private $ticket_offset;

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getInputPath()
    {
        return $this->input_path;
    }

    /**
     * @param string $input_path
     * @return $this
     */
    public function setInputPath($input_path)
    {
        $this->input_path = $input_path;
        return $this;
    }

    /**
     * @return string
     */
    public function getOutputPath()
    {
        return $this->output_path;
    }

    /**
     * @param string $output_path
     * @return $this
     */
    public function setOutputPath($output_path)
    {
        $this->output_path = $output_path;
        return $this;
    }

    /**
     * @return string
     */
    public function getLogPath()
    {
        return $this->log_path;
    }

    /**
     * @param string $log_path
     * @return $this
     */
    public function setLogPath($log_path)
    {
        $this->log_path = $log_path;
        return $this;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param string $mode
     * @return $this
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
        return $this;
    }

    /**
     * Is live mode
     *
     * @return bool
     */
    public function isLive()
    {
        return $this->mode === self::MODE_LIVE;
    }

    /**
     * @return boolean
     */
    public function isMarkDone()
    {
        return $this->mark_done;
    }

    /**
     * @param boolean $mark_done
     * @return $this
     */
    public function setMarkDone($mark_done)
    {
        $this->mark_done = (bool)$mark_done;
        return $this;
    }

    /**
     * @return int
     */
    public function getBatchSize()
    {
        return $this->batch_size;
    }

    /**
     * @param int $batch_size
     * @return $this
     */
    public function setBatchSize($batch_size)
    {
        $this->batch_size = (int)$batch_size;
        return $this;
    }
}
