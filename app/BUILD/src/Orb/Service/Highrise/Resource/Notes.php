<?php

/**
 * Orb.
 *
 * @category Highrise
 */

namespace Orb\Service\Highrise\Resource;

/**
 * @see http://developer.37signals.com/highrise/notes
 */
class Notes extends AbstractResource
{
    /**
     * Get information about a person.
     *
     * @param int $person_id
     *
     * @return array
     */
    public function getNotesForPerson($person_id)
    {
        $resource = '/people/'.$person_id.'/notes.xml';
        $response = $this->highrise->sendReadRequest($resource);

        return $this->highrise->xmlToArray($response->getBody());
    }
}
