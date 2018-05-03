<?php

/**
 * DeskPRO.
 */

namespace Orb\Auth\Adapter;

use Application\DeskPRO\Ldap\LdapPagedSearcher;
use Orb\Log\Loggable;
use Orb\Log\Logger;

abstract class AbstractLdapBasedAdapter extends PluginAdapter implements Loggable
{
    protected $options;

    /**
     * @return \Zend\Authentication\Adapter\Ldap
     */
    abstract public function getZendAuthAdapter();

    /**
     * Return all user/person records.
     *
     * @throws \Zend\Ldap\Exception\LdapException
     *
     * @return \Zend\Ldap\Collection
     */
    public function findAllRecords($size_limit = 1000, $paging = true, $objectClass = 'inetOrgPerson')
    {
        if ($this->getLogger()) {
            $this->getLogger()->log('START find all', Logger::DEBUG);
        }

        $zend_auth = $this->getZendAuthAdapter();
        // Bogus because zend only creates ldap obj when its needed,
        // so this is a hack to get it to set all the correct options
        // for us
        try {
            $zend_auth->setUsername('__bogus__');
            $zend_auth->setPassword('__bogus__');
            $zend_auth->authenticate();
        } catch (\Exception $e) {
        }

        /** @var $ldap \Zend\Ldap\Ldap */
        $ldap = $zend_auth->getLdap();

        $filter = 'objectClass='.$objectClass;

        return new LdapPagedSearcher($ldap, $filter, $size_limit, $this->options['baseDn'], $paging);
    }
}
