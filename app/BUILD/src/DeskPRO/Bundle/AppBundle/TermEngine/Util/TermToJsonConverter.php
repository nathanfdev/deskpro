<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Util;

use DeskPRO\Bundle\AppBundle\TermEngine\CompositeTermInterface;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class TermToJsonConverter.
 */
class TermToJsonConverter
{
    /**
     * Takes a TermInterface and returns a serialized form in JSON format.
     *
     * Works with CompositeTermInterface too, of course.
     *
     * @param TermInterface $term
     *
     * @return string
     */
    public function toJson(TermInterface $term)
    {
        return json_encode($this->termToArray($term));
    }

    /**
     * Takes a JSON string produced by toJson and returns the TermInterface.
     *
     * @param string $json
     *
     * @return TermInterface
     */
    public function toTerm($json)
    {
        return $this->arrayToTerm(json_decode($json, true));
    }

    /**
     * @param TermInterface $term
     *
     * @return array
     */
    public function termToArray(TermInterface $term)
    {
        if ($term instanceof CompositeTermInterface) {
            $serialized = array_merge([
                'type'  => TermTypeCodes::getTermTypeCode($term),
                'terms' => [],
            ], $term->serialize());

            foreach ($term->getTerms() as $term) {
                $serialized['terms'][] = $this->termToArray($term);
            }

            return $serialized;
        }

        return array_merge(['type' => TermTypeCodes::getTermTypeCode($term)], $term->serialize());
    }

    /**
     * @param $serialized_array
     *
     * @return TermInterface
     */
    public function arrayToTerm($serialized_array)
    {
        $class   = TermTypeCodes::getTermClassForTypeCode($serialized_array['type']);
        $options = array_key_exists('options', $serialized_array) ? $serialized_array['options'] : [];

        /** @var TermInterface $term */
        $term = new $class($options);

        if ($serialized_array['op']) {
            $term->setOp($serialized_array['op']);
        }
        if ($term instanceof CompositeTermInterface) {
            foreach ($serialized_array['terms'] as $child_term) {
                if (is_array($child_term)) {
                    $child_term = $this->arrayToTerm($child_term);
                }

                /* @var CompositeTermInterface $term */
                $term->addTerm($child_term);
            }
        }

        return $term;
    }
}
