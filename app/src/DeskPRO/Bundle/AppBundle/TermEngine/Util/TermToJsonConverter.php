<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at https://www.deskpro.com/eula/                            |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Util;

use DeskPRO\Bundle\AppBundle\TermEngine\CompositeTermInterface;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

class TermToJsonConverter
{
    /**
     * Takes a TermInterface and returns a serialized form in JSON format.
     *
     * Works with CompositeTermInterface too, of course.
     *
     * @param TermInterface $term
     * @return string
     */
    public function toJson(TermInterface $term)
    {
        $serialized = $this->termToArray($term);

        return json_encode($serialized);
    }

    /**
     * Takes a JSON string produced by toJson and returns the TermInterface.
     *
     * @param string $json
     * @return TermInterface
     */
    public function toTerm($json)
    {
        $serialized_array = json_decode($json, true);

        return $this->unserializeArrayToTerm($serialized_array);
    }

    /**
     * @param TermInterface $term
     * @return array
     */
    private function termToArray(TermInterface $term)
    {
        if ($term instanceof CompositeTermInterface) {
            $serialized = array(
                'class' => get_class($term),
                'serialized' => $term->serialize(),
                'terms' => array()
            );

            foreach ($term->getTerms() as $term) {
                $serialized['terms'][] = $this->termToArray($term);
            }

            return $serialized;
        }

        return array(
            'class' => get_class($term),
            'serialized' => $term->serialize()
        );
    }

    /**
     * @param $serialized_array
     */
    private function unserializeArrayToTerm($serialized_array)
    {
        $class = $serialized_array['class'];
        $term = new $class($serialized_array['serialized']['options']);
        $term->setOp($serialized_array['serialized']['op']);

        if ($term instanceof CompositeTermInterface) {
            foreach ($serialized_array['terms'] as $term_array) {
                $term->addTerm($this->unserializeArrayToTerm($term_array));
            }
        }

        return $term;
    }
}
