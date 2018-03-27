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
                        $value = iconv('UTF-8', 'UTF-8//IGNORE', $value);
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
