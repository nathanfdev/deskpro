<?php

namespace DeskPRO\Component\Util\Audio\Wav;

/**
 * Class FormatSection.
 */
class FormatSection
{
    const FMT = 'fmt ';

    const SECTION_SIZE = 16;
    /**
     * @var string
     */
    protected $id;
    /**
     * @var int
     */
    protected $size;
    /**
     * @var int
     */
    protected $audioFormat;
    /**
     * @var int
     */
    protected $numberOfChannels;
    /**
     * @var int
     */
    protected $sampleRate;
    /**
     * @var int
     */
    protected $byteRate;
    /**
     * @var int
     */
    protected $blockAlign;
    /**
     * @var int
     */
    protected $bitsPerSample;

    /**
     * FormatSection constructor.
     *
     * @param string $id
     * @param int    $size
     * @param int    $audioFormat
     * @param int    $numberOfChannels
     * @param int    $sampleRate
     * @param int    $byteRate
     * @param int    $blockAlign
     * @param int    $bitsPerSample
     */
    public function __construct(
        $id,
        $size,
        $audioFormat,
        $numberOfChannels,
        $sampleRate,
        $byteRate,
        $blockAlign,
        $bitsPerSample
    ) {
        $this->id               = $id;
        $this->size             = $size;
        $this->audioFormat      = $audioFormat;
        $this->numberOfChannels = $numberOfChannels;
        $this->sampleRate       = $sampleRate;
        $this->byteRate         = $byteRate;
        $this->blockAlign       = $blockAlign;
        $this->bitsPerSample    = $bitsPerSample;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }
    /**
     * @return int
     */
    public function getAudioFormat()
    {
        return $this->audioFormat;
    }
    /**
     * @return int
     */
    public function getNumberOfChannels()
    {
        return $this->numberOfChannels;
    }
    /**
     * @return int
     */
    public function getSampleRate()
    {
        return $this->sampleRate;
    }
    /**
     * @return int
     */
    public function getByteRate()
    {
        return $this->byteRate;
    }
    /**
     * @return int
     */
    public function getBlockAlign()
    {
        return $this->blockAlign;
    }
    /**
     * @return int
     */
    public function getBitsPerSample()
    {
        return $this->bitsPerSample;
    }
}
