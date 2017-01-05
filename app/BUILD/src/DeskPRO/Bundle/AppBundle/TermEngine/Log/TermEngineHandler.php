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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Log;

use Application\DeskPRO\NewSettings\SettingsResolver;
use Monolog\Handler\AbstractProcessingHandler;

/**
 * Class TermEngineHandler.
 */
class TermEngineHandler extends AbstractProcessingHandler
{
    /**
     * @var string
     */
    private $enabled;

    /**
     * @var string
     */
    private $kernel_log_dir;

    /**
     * @var string
     */
    private $file_name;

    /**
     * @var resource
     */
    private $stream;

    /**
     * @param int              $kernel_log_dir
     * @param bool             $file_name
     * @param SettingsResolver $resolver
     */
    public function __construct($kernel_log_dir, $file_name, SettingsResolver $resolver)
    {
        $this->enabled = (bool) $resolver->getGlobalSettings()->get('enable_termengine_log', false);

        $this->kernel_log_dir = $kernel_log_dir;
        $this->file_name      = $file_name;
    }

    /**
     * @param array $record
     *
     * @return bool
     */
    public function isHandling(array $record)
    {
        return true; // already filtered by channel for us
    }

    /**
     * @param array $record
     */
    public function write(array $record)
    {
        if (!$this->enabled) {
            return; // no logging not explicitely enabled
        }

        if (!is_resource($this->stream)) {
            $filename = $this->kernel_log_dir.'/'.$this->file_name;
            if (!file_exists($filename)) {
                touch($filename);
            }
            $this->stream = fopen($filename, 'a');
        }

        // We could, instead of writing here, buffer into an array and later use the ->close() method to do the write
        // I left it the same as the default monolog stream handler because it lets us see what happened before an error
        // If performance is a more pressing issue, move to close();

        flock($this->stream, LOCK_EX);
        fwrite($this->stream, (string) $record['formatted']);
        flock($this->stream, LOCK_UN);
    }
}
