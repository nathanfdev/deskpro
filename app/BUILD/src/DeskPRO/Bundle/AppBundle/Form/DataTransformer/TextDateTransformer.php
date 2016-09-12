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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class TextDateTransformer.
 */
class TextDateTransformer implements DataTransformerInterface
{
    /**
     * @var string
     */
    private $format;

    /**
     * Constructor.
     *
     * @param string $format
     */
    public function __construct($format = \DateTime::ISO8601)
    {
        $this->format = $format;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (!$value instanceof \DateTime) {
            return;
        }

        return $value->format(\DateTime::ISO8601);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if ($value === null || empty($value)) {
            return;
        } elseif ($value instanceof \DateTime) {
            return $value;
        }

        $date = \DateTime::createFromFormat(\DateTime::ISO8601, $value);

        if (!$date) {
            throw new TransformationFailedException('incorrect date format, use ISO 8601');
        }

        return $date;
    }
}
