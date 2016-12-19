<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
