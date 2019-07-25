<?php

namespace DeskPRO\Component\Util\Audio\Wav;

/**
 * Class AudioFile.
 */
class AudioFile
{
    /**
     * @var Header
     */
    private $header;

    /**
     * @var FormatSection
     */
    private $format;

    /**
     * @var DataSection
     */
    private $data;

    /**
     * AudioFile constructor.
     *
     * @param Header        $header
     * @param FormatSection $formatSection
     * @param DataSection   $data
     */
    public function __construct(Header $header, FormatSection $formatSection, DataSection $data)
    {
        $this->header = $header;
        $this->format = $formatSection;
        $this->data   = $data;
    }

    public function append(AudioFile $audioFile)
    {
        $this->header = new Header(
            $this->getHeader()->getId(),
            $this->getHeader()->getSize() + $audioFile->getHeader()->getSize(),
            $this->getHeader()->getFormat()
        );
        $this->data = new DataSection(
            $this->getData()->getId(),
            $this->getData()->getSize() + $audioFile->getData()->getSize(),
            $this->getData()->getRaw().$audioFile->getData()->getRaw()
        );

        return $this;
    }

    /**
     * @return Header
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @return FormatSection
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @return DataSection
     */
    public function getData()
    {
        return $this->data;
    }
}
