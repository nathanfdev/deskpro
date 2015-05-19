<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace Application\DeskPRO\Ldap;


use Zend\Ldap\Collection\DefaultIterator;
use Zend\Ldap\Dn;
use Zend\Ldap\Exception\LdapException;
use Zend\Ldap\Filter\AbstractFilter;
use Zend\Ldap\Ldap;
use Zend\Stdlib\ErrorHandler;

class CustomLdapSearcher extends Ldap
{
    /**
     * A hacked version of the parent "search" method to paginate results and return an array
     * of paginated collections.
     *
     * @param $filter
     * @param null $basedn
     * @param int $scope
     * @param array $attributes
     * @param null $sort
     * @param null $collectionClass
     * @param int $sizelimit
     * @param int $timelimit
     * @return \Zend\Ldap\Collection[]
     * @throws LdapException
     * @throws mixed
     */
    public function searchAll($filter, $basedn = null, $scope = self::SEARCH_SCOPE_SUB, array $attributes = array(), $sort = null, $collectionClass = null, $sizelimit = 0, $timelimit = 0)
    {
        if (is_array($filter)) {
            $options = array_change_key_case($filter, CASE_LOWER);
            foreach ($options as $key => $value) {
                switch ($key) {
                    case 'filter':
                    case 'basedn':
                    case 'scope':
                    case 'sort':
                        $$key = $value;
                        break;
                    case 'attributes':
                        if (is_array($value)) {
                            $attributes = $value;
                        }
                        break;
                    case 'collectionclass':
                        $collectionClass = $value;
                        break;
                    case 'sizelimit':
                    case 'timelimit':
                        $$key = (int)$value;
                        break;
                }
            }
        }

        if ($basedn === null) {
            $basedn = $this->getBaseDn();
        } elseif ($basedn instanceof Dn) {
            $basedn = $basedn->toString();
        }

        if ($filter instanceof AbstractFilter) {
            $filter = $filter->toString();
        }

        $resource = $this->getResource();

        $results = array();

        ErrorHandler::start(E_WARNING);
        $cookie = '';
        do {
            ldap_control_paged_result($resource, 1000, true, $cookie);

            $search = ldap_search($resource, $basedn, $filter, $attributes, 0, 1000, $timelimit);

            if ($sort !== null && is_string($sort)) {
                $isSorted = ldap_sort($resource, $search, $sort);

                if ($isSorted === false) {
                    throw new LdapException($this, 'sorting: ' . $sort);
                }
            }

            $iterator = new DefaultIterator($this, $search);

            $results[] = $this->createCollection($iterator, $collectionClass);

            ldap_control_paged_result_response($resource, $search, $cookie);
        } while ($cookie !== null && $cookie != '');
        ErrorHandler::stop();


        return $results;
    }
}
