<?php

namespace DeskPRO\Component\Util\Audio\Wav;

/**
 * Class Parser.
 */
class Parser
{
    /**
     * @param $handle
     *
     * @return Header
     */
    protected function parseHeader($handle)
    {
        return new Header(
            IO::readString($handle, '4'),
            IO::readLong($handle),
            IO::readString($handle, '4')
        );
    }

    /**
     * @param $handle
     *
     * @return FormatSection
     */
    protected function parseFormat($handle)
    {
        return new FormatSection(
            IO::readString($handle, 4),
            IO::readLong($handle),
            IO::readWord($handle),
            IO::readWord($handle),
            IO::readLong($handle),
            IO::readLong($handle),
            IO::readWord($handle),
            IO::readWord($handle)
        );
    }

    /**
     * @param $handle
     *
     * @return DataSection
     */
    protected function parseData($handle)
    {
        $id   = IO::readString($handle, 4);
        $size = IO::readLong($handle);

        return new DataSection(
            $id,
            $size,
            $size > 0 ? fread($handle, $size) : ''
        );
    }

    /**
     * @param $path
     *
     * @return AudioFile
     */
    public function parseFile($path)
    {
        $handle = fopen($path, 'rb');

        return new AudioFile(
            $this->parseHeader($handle),
            $this->parseFormat($handle),
            $this->parseData($handle)
        );
    }

    /**
     * @param $string
     *
     * @return AudioFile
     */
    public function parseString($string)
    {
        $stream = fopen('php://memory', 'rb+');
        fwrite($stream, $string);
        rewind($stream);

        return new AudioFile(
            $this->parseHeader($stream),
            $this->parseFormat($stream),
            $this->parseData($stream)
        );
    }
}
