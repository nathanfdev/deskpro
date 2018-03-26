<?php

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
