<?php

namespace DeskPRO\Component\Filesystem;

class DataUri
{
    /**
     * @var string|null
     */
    public $mediaType;

    /**
     * @var string
     */
    public $data;

    /**
     * @param string $data
     * @param string|null $mediaType
     */
    public function __construct($data, $mediaType = null)
    {
        $this->mediaType = $mediaType;
        $this->data      = $data;
    }

    /**
     * @param string $str
     * @return boolean
     */
    public static function isDataUri($str)
    {
        if (!is_string($str)) {
            return false;
        }

        return (bool)preg_match('/^data:.*?,.?/', $str);
    }

    /**
     * @param string $str
     * @return DataUri
     */
    public static function decode($str)
    {
        if (!self::isDataUri($str)) {
            throw new \InvalidArgumentException('Not a data uri', 1);
        }

        $commaPos = strpos($str, ',');
        $preData = substr($str, 5, $commaPos-5);
        $data = substr($str, $commaPos+1);
        $mediaType = null;
        $isBase64 = false;

        if ($preData) {
            $preDataParts = explode(';', $preData, 2);
            $mediaType = $preDataParts[0];

            if (count($preDataParts) === 2) {
                if ($preDataParts[1] !== 'base64') {
                    throw new \InvalidArgumentException('Expected base64', 100);
                }

                $isBase64 = true;
            }
        }

        if ($mediaType && !preg_match('/^\w+\/[-+.\w]+?$/', $mediaType)) {
            throw new \InvalidArgumentException('Invalid media type ' . $mediaType, 101);
        }

        if ($data) {
            if ($isBase64) {
                $data = base64_decode($data, true);
                if (!$data) {
                    throw new \InvalidArgumentException('Could not decode data', 200);
                }
            } else {
                $data = urldecode($data);
            }
        }

        return new self(
            $data,
            $mediaType
        );
    }
}
