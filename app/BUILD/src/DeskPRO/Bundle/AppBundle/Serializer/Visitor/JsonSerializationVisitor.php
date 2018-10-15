<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Visitor;

use DpSys\LowError\SystemErrorHandler;
use JMS\Serializer\JsonSerializationVisitor as BaseVisitor;

/**
 * Class JsonSerializationVisitor.
 */
class JsonSerializationVisitor extends BaseVisitor
{
    private $options;

    /**
     * {@inheritdoc}
     */
    public function getResult()
    {
        $result = @json_encode($this->getRoot(), $this->options);

        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                return $result;

            case JSON_ERROR_UTF8:
                $iterator = function ($value) use (&$iterator) {
                    if (is_array($value) || $value instanceof \Traversable) {
                        foreach ($value as $key => $item) {
                            $value[$key] = $iterator($item);
                        }
                    } elseif (is_string($value)) {
                        // fix german umlauts, keep this for umlauts like ä ö ü
                        $encoding = mb_detect_encoding($value, mb_detect_order(), false);
                        $value    = mb_convert_encoding($value, 'UTF-8', $encoding);

                        // fix encoding
                        // I know it looks weird but sometimes we have ASCII encoding here, and not UTF-8
                        // although we were converting it to UTF-8 explicitly
                        // e.g. detected an incomplete multibyte character in input string
                        $encoding = mb_detect_encoding($value, mb_detect_order(), false);
                        if ($encoding === 'UTF-8') {
                            $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                        }

                        $value = iconv(mb_detect_encoding($value, mb_detect_order(), false), 'UTF-8//IGNORE', $value);
                    }

                    return $value;
                };

                $converted = $iterator($this->getRoot());
                $result    = @json_encode($converted, $this->options);

                if (json_last_error() === JSON_ERROR_UTF8) {
                    trigger_error('Failed to serialize value: '.SystemErrorHandler::varToString($this->getRoot()), E_USER_NOTICE);

                    return '';
                } else {
                    return $result;
                }
            default:
                throw new \RuntimeException(sprintf('An error occurred while encoding your data (error code %d).', json_last_error()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function setOptions($options)
    {
        $this->options = (int) $options;
    }
}
