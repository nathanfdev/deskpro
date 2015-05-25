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


use Zend\Ldap\Exception\LdapException;
use Zend\Ldap\Ldap as ZendLdap;
use Zend\Stdlib\ErrorHandler;

class LdapPagedSearcher implements \Iterator
{
    /**
     * @var ZendLdap
     */
    private $wrapped_ldap;
    private $resource;
    private $page_size;
    private $result;
    private $cookie;
    private $filter;
    private $basedn;
    /**
     * most recently fetched ldap entry
     */
    private $current;

    public function __construct(ZendLdap $wrapped_ldap, $filter, $per_page, $basedn = null)
    {
        $this->wrapped_ldap = $wrapped_ldap;
        $this->resource = $wrapped_ldap->getResource();
        $this->page_size = $per_page;
        $this->basedn = $basedn;
        $this->filter = $filter;
        $this->result = null;
        $this->cookie = '';
        ldap_set_option($this->resource, LDAP_OPT_PROTOCOL_VERSION, 3);
    }

    public function executePagedSearch()
    {
        ldap_control_paged_result($this->resource, $this->page_size, true, $this->cookie);

        $this->result = ldap_search($this->resource, $this->basedn, $this->filter, array(), null, $this->page_size, 0);

        $this->current = ldap_first_entry($this->resource, $this->result);
    }

    public function current()
    {
        if (!is_resource($this->current)) {
            ldap_control_paged_result_response($this->resource, $this->result, $this->cookie);
            if ($this->cookie !== null && $this->cookie != '') {
                $this->executePagedSearch();
                if (!is_resource($this->current)) {
                    return null;
                }
            } else {
                return null;
            }
        }

        $entry = array('dn' => $this->key());
        $berIdentifier = null;

        $resource = $this->resource;
        ErrorHandler::start();
        $name = ldap_first_attribute(
            $resource, $this->current,
            $berIdentifier
        );
        ErrorHandler::stop();

        while ($name) {
            ErrorHandler::start();
            $data = ldap_get_values_len($resource, $this->current, $name);
            ErrorHandler::stop();

            if (!$data) {
                $data = array();
            }

            if (isset($data['count'])) {
                unset($data['count']);
            }

            $attrName = strtolower($name);
            $entry[$attrName] = $data;

            ErrorHandler::start();
            $name = ldap_next_attribute(
                $resource, $this->current,
                $berIdentifier
            );
            ErrorHandler::stop();
        }
        //ksort($entry, SORT_LOCALE_STRING);

        return $entry;
    }

    public function next()
    {
        $code = 0;

        if (is_resource($this->current)) {
            ErrorHandler::start();
            $this->current = ldap_next_entry($this->resource, $this->current);
            ErrorHandler::stop();
            if ($this->current === false) {
                ldap_control_paged_result_response($this->resource, $this->result, $this->cookie);
                if ($this->cookie !== null && $this->cookie != '') {
                    $this->executePagedSearch();
                } else {
                    $msg = $this->wrapped_ldap->getLastError($code);
                    if ($code === LdapException::LDAP_SIZELIMIT_EXCEEDED) {
                        // we have reached the size limit enforced by the server
                        return;
                    } elseif ($code > LdapException::LDAP_SUCCESS) {
                        throw new LdapException($this->wrapped_ldap, 'getting next entry (' . $msg . ')');
                    }
                }
            }
        } else {
            $this->current = false;
        }
    }

    public function key()
    {
        if (is_resource($this->current)) {
            $resource = $this->resource;
            ErrorHandler::start();
            $currentDn = ldap_get_dn($resource, $this->current);
            ErrorHandler::stop();

            if ($currentDn === false) {
                throw new LdapException($this->wrapped_ldap, 'getting dn');
            }

            return $currentDn;
        } else {
            return null;
        }
    }

    public function valid()
    {
        return is_resource($this->current);
    }

    public function rewind()
    {
        $this->current = ldap_first_entry($this->resource, $this->result);
    }
}
