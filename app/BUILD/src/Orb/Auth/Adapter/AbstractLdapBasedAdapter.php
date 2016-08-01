<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
