<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Ldap;

class Ldap extends \Zend\Ldap\Ldap
{
    public function connect($host = null, $port = null, $useSsl = null, $useStartTls = null, $networkTimeout = 10)
    {
        return parent::connect($host, $port, $useSsl, $useStartTls, $networkTimeout);
    }

    /**
     * Custom version to support multiple %s placeholders in custom account filter format.
     *
     * @param $acctname
     *
     * @return string
     */
    protected function _getAccountFilter($acctname)
    {
        $this->_splitName($acctname, $dname, $aname);
        $accountFilterFormat = $this->_getAccountFilterFormat();
        $aname               = \Zend\Ldap\Filter\AbstractFilter::escapeValue($aname);

        if ($accountFilterFormat) {
            $count_args = substr_count($accountFilterFormat, '%s');
            $args       = array_fill(0, $count_args, $aname);

            return vsprintf($accountFilterFormat, $args);
        }

        if (!$this->_getBindRequiresDn()) {
            return sprintf('(&(objectClass=user)(sAMAccountName=%s))', $aname);
        }

        return sprintf('(&(objectClass=posixAccount)(uid=%s))', $aname);
    }
}
