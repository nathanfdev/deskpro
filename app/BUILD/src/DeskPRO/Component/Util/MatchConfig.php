<?php

namespace DeskPRO\Component\Util;

/**
 * Class MatchConfig.
 */
class MatchConfig
{
    /**
     * @var string
     */
    private $pattern;

    /**
     * @var string
     */
    private $blobAuthIdStub;

    /**
     * @var string
     */
    private $filenameStub;

    /**
     * MatchConfig constructor.
     *
     * @param string $pattern
     * @param string $blobAuthIdStub
     * @param string $filenameStub
     */
    public function __construct($pattern, $blobAuthIdStub, $filenameStub)
    {
        $this->pattern        = $pattern;
        $this->blobAuthIdStub = $blobAuthIdStub;
        $this->filenameStub   = $filenameStub;
    }

    /**
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * @return string
     */
    public function getBlobAuthIdStub()
    {
        return $this->blobAuthIdStub;
    }

    /**
     * @return string
     */
    public function getFilenameStub()
    {
        return $this->filenameStub;
    }
}
