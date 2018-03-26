<?php

/**
 * Orb.
 *
 * @category Highrise
 */

namespace Orb\Service\Highrise\Resource;

/**
 * @see http://developer.37signals.com/highrise/people
 */
class Person extends AbstractResource
{
    /**
     * Get information about a person.
     *
     * @param int $person_id
     *
     * @return array
     */
    public function getPerson($person_id)
    {
        $resource = '/people/'.$person_id.'.xml';
        $response = $this->highrise->sendReadRequest($resource);

        return $this->highrise->xmlToArray($response->getBody());
    }

    /**
     * Get information about all people. To see if more pages exist, check $page+1
     * etc. There is no way to get total pages. Results are returned in batches of 500.
     *
     * @param int $page The page number
     *
     * @return array
     */
    public function getAllPeople($page = 1)
    {
        $resource = '/people.xml';
        $offset   = ($page - 1) * 500;

        $response = $this->highrise->sendReadRequest($resource, ['n' => $offset]);

        return $this->highrise->xmlToArray($response->getBody());
    }

    /**
     * Get all people with a certain tag. Note that it's a tag_id.
     *
     * @param int $tag_id
     * @param int $page
     *
     * @return array
     */
    public function getPeopleWithTag($tag_id, $page = 1)
    {
        $resource = '/people.xml';
        $offset   = ($page - 1) * 500;

        $response = $this->highrise->sendReadRequest($resource, ['n' => $offset, 'tag_id' => $tag_id]);

        return $this->highrise->xmlToArray($response->getBody());
    }

    /**
     * Get all people with a certain title (i.e., 'CEO').
     *
     * @param string $title
     * @param int    $page
     *
     * @return array
     */
    public function getPeopleWithTitle($title, $page = 1)
    {
        $resource = '/people.xml';
        $offset   = ($page - 1) * 500;

        $response = $this->highrise->sendReadRequest($resource, ['n' => $offset, 'title' => $title]);

        return $this->highrise->xmlToArray($response->getBody());
    }

    /**
     * Get all people in a certain company. Note it's a company ID.
     *
     * @param int $company_id
     * @param int $page
     *
     * @return array
     */
    public function getCompanyPeople($company_id, $page = 1)
    {
        $resource = '/companies/'.$company_id.'/people.xml';
        $offset   = ($page - 1) * 500;

        $response = $this->highrise->sendReadRequest($resource, ['n' => $offset]);

        return $this->highrise->xmlToArray($response->getBody());
    }

    /**
     * Find all people whose match a certain criteria. Results are grouped in
     * batches of 25.
     *
     * The following criteria are supported:
     * - city
     * - state
     * - country
     * - zip
     * - phone
     * - email
     *
     * @param array $criteria
     * @param int   $page
     *
     * @return array
     */
    public function findPeopleWithCriteria(array $criteria = [], $page = 1)
    {
        $resource = '/people/search.xml';
        $offset   = ($page - 1) * 25;

        $postfields = ['n' => $offset];
        foreach ($criteria as $k => $v) {
            $postfields["criteria[$k]"] = $v;
        }

        $response = $this->highrise->sendReadRequest($resource, $postfields);
        $body     = new \SimpleXMLElement($response->getBody());

        $people = [];
        foreach ($body as $person) {
            $people[] = $this->highrise->xmlToArray($person);
        }

        return $people;
    }

    /**
     * Get all people who were created or updated since a certain time.
     *
     * @param int $since A timestamp, or a string in the format yyyymmddhhmmss
     * @param int $page
     *
     * @return array
     */
    public function getPeopleSince($since, $page = 1)
    {
        $resource = '/people.xml';
        $offset   = ($page - 1) * 500;

        // If it doesn't start with 1, then its probably a timestamp
        // so we need to convert it
        if (is_numeric($since) and $since[0] == '1') {
            $since = date('YmdHis', $since);
        }

        $response = $this->highrise->sendReadRequest($resource, ['n' => $offset, 'since' => $since]);

        return $this->highrise->xmlToArray($response->getBody());
    }

    /**
     * Create a new person record.
     *
     * @param array $person_info
     *
     * @return bool
     */
    public function createPerson(array $person_info)
    {
        $xml = $this->highrise->arrayToXml($person_info, 'person');

        $response = $this->highrise->sendWriteRequest('/people.xml', $xml);

        return $response->isSuccessful();
    }

    /**
     * Update a person.
     *
     * @param array $person_info
     *
     * @return bool
     */
    public function updatePerson($person_id, array $person_info)
    {
        $xml = $this->highrise->arrayToXml($person_info, 'person');

        $response = $this->highrise->sendPutRequest('/people/'.$person_id.'.xml', $xml);

        return $response->isSuccessful();
    }

    /**
     * Delete a person.
     *
     * @param int $person_id
     *
     * @return bool
     */
    public function deletePerson($person_id)
    {
        $response = $this->highrise->sendDeleteRequest('/people/'.$person_id.'.xml');

        return $response->isSuccessful();
    }
}
