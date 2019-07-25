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
        self::writeHeader($audioFile, $handle);
        self::writeFormatSection($audioFile, $handle);
        self::writeDataSection($audioFile, $handle);
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
        self::writeHeader($audioFile, $handle);
        self::writeFormatSection($audioFile, $handle);
        self::writeDataSection($audioFile, $handle);
        rewind($handle);

        $content = stream_get_contents($handle);
        fclose($handle);

        return $content;
    }

    /**
     * @param resource $handle
     */
    protected static function writeHeader(AudioFile $audioFile, $handle)
    {
        self::writeString($handle, $audioFile->getHeader()->getId());
        self::writeLong($handle, $audioFile->getHeader()->getSize());
        self::writeString($handle, $audioFile->getHeader()->getFormat());
    }
    /**
     * @param resource $handle
     */
    protected static function writeFormatSection(AudioFile $audioFile, $handle)
    {
        self::writeString($handle, $audioFile->getFormat()->getId());
        self::writeLong($handle,   $audioFile->getFormat()->getSize());
        self::writeWord($handle,   $audioFile->getFormat()->getAudioFormat());
        self::writeWord($handle,   $audioFile->getFormat()->getNumberOfChannels());
        self::writeLong($handle,   $audioFile->getFormat()->getSampleRate());
        self::writeLong($handle,   $audioFile->getFormat()->getByteRate());
        self::writeWord($handle,   $audioFile->getFormat()->getBlockAlign());
        self::writeWord($handle,   $audioFile->getFormat()->getBitsPerSample());
    }
    /**
     * @param resource $handle
     */
    protected static function writeDataSection(AudioFile $audioFile, $handle)
    {
        self::writeString($handle, $audioFile->getData()->getId());
        self::writeLong($handle, $audioFile->getData()->getSize());
        self::writeString($handle, $audioFile->getData()->getRaw());
    }
}
