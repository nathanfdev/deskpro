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
use Orb\Util\Strings;

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

    public function getTermTypeCode(TermInterface $term)
    {
        // remove namespace and remove "Term" from the end of the class name, lowercase.
        // DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketStatusTerm convert to: "ticket_status"
        return strtolower(
            Strings::camelCaseToUnderscore(
                substr(join('', array_slice(explode('\\', get_class($term)), -1)), 0, -4)
            )
        );
    }

    public function getTermClassForTypeCode($term_type_code)
    {
        // "ticket_status" to classname: DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketStatusTerm
        return 'DeskPRO\\Bundle\\AppBundle\\TermEngine\\Term\\' . ucfirst(
            Strings::underscoreToCamelCase(
                $term_type_code
            )
        ) . 'Term';
    }

    /**
     * @param TermInterface $term
     * @return array
     */
    private function termToArray(TermInterface $term)
    {
        if ($term instanceof CompositeTermInterface) {
            $serialized = array(
                'type' => $this->getTermTypeCode($term),
                'data' => $term->serialize(),
                'terms' => array()
            );

            foreach ($term->getTerms() as $term) {
                $serialized['terms'][] = $this->termToArray($term);
            }

            return $serialized;
        }

        return array(
            'type' => $this->getTermTypeCode($term),
            'data' => $term->serialize()
        );
    }

    /**
     * @param $serialized_array
     */
    private function unserializeArrayToTerm($serialized_array)
    {
        $class = $this->getTermClassForTypeCode($serialized_array['type']);
        $term = new $class($serialized_array['data']['options']);
        $term->setOp($serialized_array['data']['op']);

        if ($term instanceof CompositeTermInterface) {
            foreach ($serialized_array['terms'] as $term_array) {
                $term->addTerm($this->unserializeArrayToTerm($term_array));
            }
        }

        return $term;
    }
}
