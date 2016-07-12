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

namespace Application\ImportBundle\Generator\Exporter\Formatter\Transformer;

/**
 * Formats destination path.
 *
 * Class DestinationTransformer
 */
final class DestinationTransformer implements TransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE_DESTINATION;
    }

    /**
     * {@inheritdoc}
     */
    public function transform(array $transformed, array $original, $property, array $options = [])
    {
        $value = null;
        if (array_key_exists($property, $transformed)) {
            $value = $transformed[$property];
        }
        if (isset($options['ref'])) {
            $ref = (array) $options['ref'];

            foreach ($ref as $ref_property) {
                if (preg_match('/^original\#(.*)$/', $ref_property, $matches)) {
                    if (array_key_exists($matches[1], $original)) {
                        $value = $original[$matches[1]];
                        break;
                    }
                } else {
                    if (array_key_exists($ref_property, $transformed)) {
                        $value = $transformed[$ref_property];
                        break;
                    }
                }
            }
        }
        if (!$value && isset($options['default'])) {
            $value = (string) $options['default'];
        }
        if (!$this->isValidValue($value)) {
            throw new TransformerException('Empty destination property', $this->getType(), $transformed, $property);
        }

        $filename = strtolower($value);
        $filename = str_replace(' ', '_', $filename);
        $filename = preg_replace('#[^\w\d\_\-\.\@]#i', '', $filename);

        if (!$this->isValidValue($filename)) {
            throw new TransformerException('Empty destination filename', $this->getType(), $transformed, $property);
        }
        if (isset($options['prefix'])) {
            $filename = rtrim($options['prefix'], '_').'_'.$filename;
        }

        return $filename;
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    private function isValidValue($value)
    {
        return $value || 0 === $value || '0' === $value;
    }
}
