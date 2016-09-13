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

namespace DeskPRO\Bundle\ApiBundle\Log\Writer;

use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\ApiBundle\Log\Serializer\SerializerInterface;
use DeskPRO\Bundle\AppBundle\Entity\ApiLog;

/**
 * Class WriterInterface.
 *
 * @todo log rotate
 */
class FileWriter implements WriterInterface
{
    /**
     * @var string
     */
    protected $log_name = 'api_log.log';
    /**
     * @var int
     */
    protected $log_max_size = 5242880; //5 * 1024 * 1024; // in bytes
    /**
     * @var int
     */
    protected $log_max_files = 5;

    /**
     * @var \SplFileObject
     */
    protected $file;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @param $logs_dir
     * @param SettingsResolver $settings_resolver
     * @param  $serializer
     */
    public function __construct($logs_dir, SettingsResolver $settings_resolver, SerializerInterface $serializer)
    {
        $this->setup($settings_resolver->getGlobalSettings()->getSerializedArray('api_log.writer.file'));
        $this->file       = new \SplFileObject($logs_dir.DIRECTORY_SEPARATOR.$this->log_name, 'a');
        $this->serializer = $serializer;
    }

    /**
     * @param ApiLog $log
     */
    public function write(ApiLog $log)
    {
        if (false === $this->file->fwrite($this->serializer->serialize($log))) {
            throw new \RuntimeException('Couldn\'t write api_log');
        }
    }

    public function __destruct()
    {
        unset($this->file);
    }

    protected function setup(array $options)
    {
        $options = array_merge($this->getDefaults(), $options);
        $options = array_intersect_key($options, $this->getDefaults());
        foreach ($options as $key => $value) {
            $this->$key = $value;
        }
    }

    protected function getDefaults()
    {
        return [
            'log_max_size'  => $this->log_max_size,
            'log_max_files' => $this->log_max_files,
            'log_name'      => $this->log_name,
        ];
    }
}
