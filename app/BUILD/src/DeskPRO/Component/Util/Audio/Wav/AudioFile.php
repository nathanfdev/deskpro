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

    private $multi = false;

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
        $this->convertMonoToStereo();
    }

    private function convertMonoToStereo()
    {
        if ($this->format->getNumberOfChannels() == 1) {
            $data = $this->getData()->getRaw();

            $monoHandler   = fopen('php://memory', 'rb+');
            $stereoHandler = fopen('php://memory', 'wb+');

            fwrite($monoHandler, $data);
            rewind($monoHandler);

            while ($monoData = unpack('a*', fread($monoHandler, $this->format->getBlockAlign()))) {
                $d = array_pop($monoData);
                if (!$d) {
                    break;
                }
                fwrite($stereoHandler, pack('a*', $d));
                fwrite($stereoHandler, pack('a*', $d));
            }
            fclose($monoHandler);
            rewind($stereoHandler);
            $newData = stream_get_contents($stereoHandler);
            fclose($stereoHandler);

            $this->data = new DataSection(
                $this->data->getId(),
                $this->data->getSize() * 2,
                $newData
            );

            $this->format = new FormatSection(
                $this->format->getId(),
                $this->format->getSize(),
                $this->format->getAudioFormat(),
                2,
                $this->format->getSampleRate(),
                $this->format->getByteRate() * 2,
                $this->format->getBlockAlign() * 2,
                $this->format->getBitsPerSample()
            );

            $this->header = new Header(
                $this->header->getId(),
                $this->header->getSize() + $this->data->getSize() / 2,
                $this->header->getFormat()
            );
        }
    }

    public function append(AudioFile $audioFile)
    {
        $this->header = new Header(
            $this->getHeader()->getId(),
            $this->getHeader()->getSize() + $audioFile->getData()->getSize(),
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
     * @return bool
     */
    public function isMulti()
    {
        return $this->multi;
    }

    /**
     * @param bool $multi
     */
    public function setMulti($multi)
    {
        $this->multi = $multi;
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
