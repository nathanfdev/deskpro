<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Util;

use DeskPRO\Bundle\AppBundle\TermEngine\Exception\TermTypeDoesNotExistException;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use Orb\Util\Strings;

/**
 * A static utility class that allows you to map between term type codes and class names.
 *
 * Type code: ticket_status
 * Class: DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketStatusTerm
 */
class TermTypeCodes
{
    /**
     * Remove namespace and remove "Term" from the end of the class name, lowercase.
     *
     * example: DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketStatusTerm converts to: "ticket_status"
     *
     * @param TermInterface $term
     *
     * @return string type code
     */
    public static function getTermTypeCode(TermInterface $term)
    {
        return strtolower(
            Strings::camelCaseToUnderscore(
                substr(implode('', array_slice(explode('\\', get_class($term)), -1)), 0, -4)
            )
        );
    }

    /**
     * Example: "ticket_status" to classname: DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketStatusTerm.
     *
     * @param $term_type_code
     *
     * @throws TermTypeDoesNotExistException
     *
     * @return string
     */
    public static function getTermClassForTypeCode($term_type_code)
    {
        $term_class_name = ucfirst(
            Strings::underscoreToCamelCase(
                $term_type_code
            )
        );
        $term_class = sprintf(
            'DeskPRO\\Bundle\\AppBundle\\TermEngine\\Term\\%s\\%sTerm',
            $term_class_name,
            $term_class_name
        );

        if (!class_exists($term_class)) {
            $term_class = sprintf(
                'DeskPRO\\Bundle\\AppBundle\\TermEngine\\Term\\%sTerm',
                $term_class_name
            );
            if (!class_exists($term_class)) {
                throw new TermTypeDoesNotExistException($term_type_code);
            }
        }

        return $term_class;
    }
}
