<?php

namespace DeskPRO\Component\Util\Audio\Wav;

/**
 * Class IO.
 */
class IO
{
    /**
     * @param $handle
     * @param $length
     *
     * @return mixed
     */
    public static function readString($handle, $length)
    {
        return self::read($handle, 'a*', $length);
    }

    /**
     * @param $handle
     *
     * @return mixed
     */
    public static function readLong($handle)
    {
        return self::read($handle, 'V', 4);
    }

    /**
     * @param $handle
     *
     * @return mixed
     */
    public static function readWord($handle)
    {
        return self::read($handle, 'v', 2);
    }

    /**
     * @param $handle
     * @param $type
     * @param $length
     *
     * @return mixed
     */
    public static function read($handle, $type, $length)
    {
        $data = unpack($type, fread($handle, $length));

        return array_pop($data);
    }

    /**
     * @param $handle
     * @param $data
     *
     * @return bool|int
     */
    public static function writeString($handle, $data)
    {
        return self::writeUnpacked($handle, 'a*', $data);
    }

    /**
     * @param $handle
     * @param $data
     *
     * @return bool|int
     */
    public static function writeLong($handle, $data)
    {
        return self::writeUnpacked($handle, 'V', $data);
    }

    /**
     * @param $handle
     * @param $data
     *
     * @return bool|int
     */
    public static function writeWord($handle, $data)
    {
        return self::writeUnpacked($handle, 'v', $data);
    }

    /**
     * @param $handle
     * @param $type
     * @param $data
     *
     * @return bool|int
     */
    protected static function writeUnpacked($handle, $type, $data)
    {
        return fwrite($handle, pack($type, $data));
    }

    /**
     * @param AudioFile $audioFile
     * @param string    $path
     */
    public static function saveAudioToFile(AudioFile $audioFile, $path)
    {
        $handle = fopen($path, 'wb');
        self::writeHeader($audioFile->getHeader(), $handle);
        self::writeFormatSection($audioFile->getFormat(), $handle);
        self::writeDataSection($audioFile->getData(), $handle);
        fclose($handle);
    }

    /**
     * @param AudioFile $audioFile
     *
     * @return bool|string
     */
    public static function saveAudioToMemory(AudioFile $audioFile)
    {
        $handle = fopen('php://memory', 'wb');
        self::writeHeader($audioFile->getHeader(), $handle);
        self::writeFormatSection($audioFile->getFormat(), $handle);
        self::writeDataSection($audioFile->getData(), $handle);
        rewind($handle);

        $content = stream_get_contents($handle);
        fclose($handle);

        return $content;
    }

    /**
     * @param Header   $header
     * @param resource $handle
     */
    protected static function writeHeader(Header $header, $handle)
    {
        self::writeString($handle, $header->getId());
        self::writeLong($handle, $header->getSize());
        self::writeString($handle, $header->getFormat());
    }
    /**
     * @param FormatSection $section
     * @param resource      $handle
     */
    protected static function writeFormatSection(FormatSection $section, $handle)
    {
        self::writeString($handle, $section->getId());
        self::writeLong($handle,   $section->getSize());
        self::writeWord($handle,   $section->getAudioFormat());
        self::writeWord($handle,   $section->getNumberOfChannels());
        self::writeLong($handle,   $section->getSampleRate());
        self::writeLong($handle,   $section->getByteRate());
        self::writeWord($handle,   $section->getBlockAlign());
        self::writeWord($handle,   $section->getBitsPerSample());
    }
    /**
     * @param DataSection $data
     * @param resource    $handle
     */
    protected static function writeDataSection(DataSection $data, $handle)
    {
        self::writeString($handle, $data->getId());
        self::writeLong($handle, $data->getSize());
        self::writeString($handle, $data->getRaw());
    }
}
