<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Ldap;

use Application\DeskPRO\App;
use Orb\Log\Logger;
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
     * most recently fetched ldap entry.
     */
    private $current;
    /**
     * @var bool
     */
    private $paged;

    public function __construct(ZendLdap $wrapped_ldap, $filter, $per_page, $basedn = null, $paged = true)
    {
        $this->wrapped_ldap = $wrapped_ldap;
        $this->resource     = $wrapped_ldap->getResource();
        $this->page_size    = (int) $per_page;
        $this->basedn       = $basedn;
        $this->filter       = $filter;
        $this->result       = null;
        $this->cookie       = '';
        if ($paged) {
            ldap_set_option($this->resource, LDAP_OPT_PROTOCOL_VERSION, 3);
        }
        $this->paged = (bool) $paged;
    }

    public function executePagedSearch()
    {
        $start_time = time();
        $this->logIfPossible(Logger::INFO, 'executing actual LDAP search');
        if ($this->paged) {
            ldap_control_paged_result($this->resource, $this->page_size, false, $this->cookie);
        }

        $this->result = ldap_search($this->resource, $this->basedn, $this->filter, [], 0, $this->paged ? $this->page_size : 0, 0);

        usleep(100000); // not sure why this hack works, but it avoids a hanging process, leaving it for now

        $time = ceil(time() - $start_time);
        $this->logIfPossible(Logger::INFO, 'finished executing actual LDAP paged search (took '.$time.'s)');

        $this->rewind();
    }

    protected function nextPage()
    {
        if (!$this->paged) {
            return;
        }

        $this->logIfPossible(Logger::INFO, 'getting next page');

        ldap_control_paged_result_response($this->resource, $this->result, $this->cookie);
        if ($this->cookie !== null && $this->cookie != '') {
            $this->executePagedSearch();
        }
    }

    public function current()
    {
        $curTime = microtime(true);
        if (!is_resource($this->current)) {
            $this->nextPage();
            if (!is_resource($this->current)) {
                return;
            }
        }

        $entry         = ['dn' => $this->key()];
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
                $data = [];
            }

            if (isset($data['count'])) {
                unset($data['count']);
            }

            $attrName         = strtolower($name);
            $entry[$attrName] = $data;

            ErrorHandler::start();
            $name = ldap_next_attribute(
                $resource, $this->current,
                $berIdentifier
            );
            ErrorHandler::stop();
        }

        $timeConsumed = round(microtime(true) - $curTime, 3) * 1000;
        if ($timeConsumed >= 5) {
            // only log if it took 1 second or more
            $this->logIfPossible(Logger::INFO, 'finished getting current LDAP entry (took '.$timeConsumed.'ms)');
        }

        return $entry;
    }

    public function next()
    {
        if (is_resource($this->current)) {
            ErrorHandler::start();
            $this->current = ldap_next_entry($this->resource, $this->current);
            ErrorHandler::stop();
            if ($this->current === false) {
                $this->nextPage();
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
            return;
        }
    }

    public function valid()
    {
        return is_resource($this->current);
    }

    public function rewind()
    {
        $this->logIfPossible(Logger::INFO, 'rewinding LDAP resource');
        $this->current = ldap_first_entry($this->resource, $this->result);
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * Closes the current result set.
     *
     * @return bool
     */
    public function close()
    {
        $this->logIfPossible(Logger::INFO, 'closing LDAP connection');
        $isClosed = false;
        if (is_resource($this->result)) {
            ErrorHandler::start();
            $isClosed = ldap_free_result($this->result);
            ErrorHandler::stop();

            $this->result  = null;
            $this->current = null;
        }

        return $isClosed;
    }

    protected function logIfPossible($orb_priority, $message, array $info = [])
    {
        $appEnv = App::$container->get('deskpro.app_env');
        if (!$appEnv->isDebug() && !$appEnv->getConfig('logs.enable_usersource_log')) {
            return;
        }

        if ($logger = App::$container->getUsersourceLogger()) {
            $logger->log('LDAP Paged Searcher: '.$message, $orb_priority, $info);
        }
    }
}
