<?php

/**
 * Orb.
 *
 * @category Auth
 */

namespace Orb\Auth\Adapter;

use Orb\Auth\Identity;
use Orb\Auth\Result;
use Orb\Log\Logger;
use Orb\Util\Arrays;
use Orb\Util\Strings;

class ActiveDirectory extends AbstractLdapBasedAdapter implements FormLoginInterface
{
    const OPT_HOST = 'host';

    const OPT_PORT = 'port';

    const OPT_TLS = 'useStartTls';

    const OPT_SSL = 'useSsl';

    const OPT_BASE_DN = 'baseDn';

    const OPT_DOMAIN_NAME = 'accountDomainName';

    const OPT_DOMAIN_NAME_SHORT = 'accountDomainNameShort';

    const OPT_FILTER_FORMAT = 'accountFilterFormat';

    const OPT_LOOKUP_USERNAME = 'username';

    const OPT_LOOKUP_PASSWORD = 'password';

    /**
     * Disable SSL certificate validation. Allow to use self-signed certificates.
     */
    const OPT_DISABLE_CERT_VALIDATION = 'disable_cert_validation';

    /**
     * @var \Orb\Log\Logger
     */
    protected $logger;

    /**
     * @var string
     */
    protected $set_username;
    /**
     * @var string
     */
    protected $set_password;

    /**
     * @var array
     */
    protected $options = [
        self::OPT_HOST                    => 'localhost',
        self::OPT_PORT                    => null, // null means default of 389 or 636 if ssl enabled
        self::OPT_TLS                     => false,
        self::OPT_SSL                     => false,
        self::OPT_BASE_DN                 => '',
        self::OPT_DOMAIN_NAME             => '',
        self::OPT_DOMAIN_NAME_SHORT       => '',
        self::OPT_FILTER_FORMAT           => false,
        self::OPT_LOOKUP_USERNAME         => null,
        self::OPT_LOOKUP_PASSWORD         => null,
        self::OPT_DISABLE_CERT_VALIDATION => false,
    ];

    public function __construct(array $options)
    {
        $this->options                         = array_merge($this->options, $options);
        $this->options['accountCanonicalForm'] = 4;
    }

    /**
     * @param string $username
     * @param string $password
     */
    public function setFormData(array $form_data)
    {
        $this->set_username = !empty($form_data['username']) ? (string) $form_data['username'] : '';
        $this->set_password = !empty($form_data['password']) ? (string) $form_data['password'] : '';
    }

    /**
     * @return \Zend\Authentication\Adapter\Ldap
     */
    public function getZendAuthAdapter()
    {
        if (isset($this->options[self::OPT_DISABLE_CERT_VALIDATION]) && $this->options[self::OPT_DISABLE_CERT_VALIDATION]) {
            putenv('LDAPTLS_REQCERT=never');
        }
        $options = [];
        foreach (
            [
                'host',
                'port',
                'useStartTls',
                'useSsl',
                'baseDn',
                'username',
                'password',
                'accountFilterFormat',
                'accountDomainName',
                'accountDomainNameShort',
                'accountCanonicalForm',
            ] as $k) {
            if (isset($this->options[$k]) && $this->options[$k]) {
                $options[$k] = $this->options[$k];
            }
        }

        $auth = new \Zend\Authentication\Adapter\Ldap([$options], $this->set_username, $this->set_password);

        if (!empty($this->options['ldapClass'])) {
            $class = $this->options['ldapClass'];
            $ldap  = new $class();
            $auth->setLdap($ldap);

            // setLdap resets options (why?!), so we need to reset it back
            $auth->setOptions([$options]);
        }

        return $auth;
    }

    public function findAllRecords($size_limit = 1000, $paging = true, $objectClass = 'User')
    {
        return parent::findAllRecords($size_limit, $paging, $objectClass);
    }

    /**
     * Authenticate a user.
     *
     * @return Result
     */
    public function doAuthenticate()
    {
        $res = $this->doAdAuthenticate();

        if (!$res->isValid() && strpos($this->set_username, '@')) {
            $record = $this->findRecordViaEmail();
            if ($record) {
                $old = $this->set_username;
                if (!empty($record['samaccountname'][0])) {
                    $this->set_username = $record['samaccountname'][0];
                } elseif (!empty($record['userprincipalname'][0])) {
                    $this->set_username = $record['userprincipalname'][0];
                } else {
                    $this->set_username = $record['distinguishedname'][0];
                }

                $res2               = $this->doAuthenticate();
                $this->set_username = $old;
                if ($res2->isValid()) {
                    return $res2;
                }
            }
        }

        return $res;
    }

    public function findRecordViaDn($provided_dn)
    {
        try {
            $auth = $this->getZendAuthAdapter();
            // Bogus because zend only creates ldap obj when its needed,
            // so this is a hack to get it to set all the correct options
            // for us
            try {
                $auth->setUsername('__bogus__');
                $auth->setPassword('__bogus__');
                $auth->authenticate();
            } catch (\Exception $e) {
            }
            /** @var $ldap \Zend\Ldap\Ldap */
            $ldap = $auth->getLdap();

            return $ldap->getEntry($provided_dn);
        } catch (\Exception $e) {
            return;
        }
    }

    public function getIdentityForDn($provided_dn)
    {
        try {
            $auth = $this->getZendAuthAdapter();
            // Bogus because zend only creates ldap obj when its needed,
            // so this is a hack to get it to set all the correct options
            // for us
            try {
                $auth->setUsername('__bogus__');
                $auth->setPassword('__bogus__');
                $auth->authenticate();
            } catch (\Exception $e) {
            }
            $raw_info = [];
            /** @var $ldap \Zend\Ldap\Ldap */
            $ldap = $auth->getLdap();
            $dn   = $ldap->getCanonicalAccountName($provided_dn, \Zend\Ldap\Ldap::ACCTNAME_FORM_DN);

            /** @var $rec \Zend\Ldap\Node */
            $rec = $ldap->getNode($dn);
            if ($rec) {
                foreach ($rec->getData() as $name => $value) {
                    try {
                        $raw_info[$name] = $rec->getAttribute($name, null);
                    } catch (\Exception $e) {
                        $raw_info[$name] = $value;
                    }
                }

                if (!empty($raw_info['samaccountname'])) {
                    $raw_info['friendly_identity'] = Arrays::getFirstItem($raw_info['samaccountname']);
                } elseif (!empty($raw_info['uid'])) {
                    $raw_info['friendly_identity'] = Arrays::getFirstItem($raw_info['uid']);
                }
                if (!empty($raw_info['distinguishedname'])) {
                    $raw_info['identity'] = Arrays::getFirstItem($raw_info['distinguishedname']);
                } elseif (!empty($raw_info['dn'])) {
                    $raw_info['identity'] = Arrays::getFirstItem($raw_info['dn']);
                } else {
                    $raw_info['identity'] = $provided_dn;
                }

                $raw_info['domain'] = $this->options['accountDomainName'];

                if ($rec->getAttribute('givenName')) {
                    $raw_info['first_name'] = $rec->getAttribute('givenName', 0);
                }
                if ($rec->getAttribute('sn')) {
                    $raw_info['last_name'] = $rec->getAttribute('sn', 0);
                }

                if (isset($raw_info['first_name']) && isset($raw_info['last_name'])) {
                    $raw_info['name'] = $raw_info['first_name'].' '.$raw_info['last_name'];
                } elseif ($rec->getAttribute('name')) {
                    $raw_info['name'] = $rec->getAttribute('name', 0);
                } elseif ($rec->getAttribute('cn')) {
                    $raw_info['name'] = $rec->getAttribute('cn', 0);
                }

                if ($rec->getAttribute('mail')) {
                    $raw_info['email_address'] = $rec->getAttribute('mail', 0);
                } elseif (\Orb\Validator\StringEmail::isValueValid($rec->getAttribute('userPrincipalName', 0))) {
                    $raw_info['email_address'] = $rec->getAttribute('userPrincipalName', 0);
                }

                if ($rec->getAttribute('jpegPhoto')) {
                    $raw_info['picture_data'] = $rec->getAttribute('jpegPhoto', 0);

                    // Large photo can potentially cause memory issues when we resize/edit it, so ignore it
                    if (isset($raw_info['picture_data'][15000000])) {
                        unset($raw_info['picture_data'][15000000]);
                    }
                } elseif ($rec->getAttribute('thumbnailPhoto')) {
                    $raw_info['picture_data'] = $rec->getAttribute('thumbnailPhoto', 0);
                }

                if ($rec->getAttribute('telephoneNumber')) {
                    $raw_info['phone'] = $rec->getAttribute('telephoneNumber', 0);
                }
            } else {
                $raw_info['dp_error'] = "Empty record from node: $dn";
            }
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->log("Exception: {$e->getCode()} {$e->getMessage()}\n{$e->getTraceAsString()}", Logger::ERR);
            }

            throw $e;
        }

        $identity = new Identity($raw_info['identity'], $raw_info);

        return $identity;
    }

    /**
     * Authenticate a user.
     *
     * @return Result
     */
    public function doAdAuthenticate()
    {
        if (!$this->set_username) {
            if ($this->logger) {
                $this->logger->logDebug('No username provided');
            }

            return new Result(Result::FAILURE, null, ['error_code' => 'missing_input_username', 'error_message' => 'No username provided']);
        }
        if (!$this->set_password) {
            if ($this->logger) {
                $this->logger->logDebug('No password provided');
            }

            return new Result(Result::FAILURE, null, ['error_code' => 'missing_input_password', 'error_message' => 'No password provided']);
        }

        $time_start = microtime(true);
        if ($this->logger) {
            $this->logger->log('START ActiveDirectory::authenticate', Logger::DEBUG);
            $this->logger->log('Options: '.trim(print_r($this->options, 1)), Logger::DEBUG);
            $this->logger->log("Request: {$this->set_username}:{$this->set_password}", Logger::DEBUG);
        }

        $auth = $this->getZendAuthAdapter();

        try {
            /** @var $result \Zend\Authentication\Result */
            $result = $auth->authenticate();
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->log("Exception: {$e->getCode()} {$e->getMessage()}", Logger::ERR);
            }

            return new Result(Result::FAILURE_EXCEPTION, null, ['error_code' => 'exception', 'error_message' => 'An exception occurred', 'exception' => $e]);
        }

        if ($this->logger) {
            foreach ($result->getMessages() as $msg) {
                $this->logger->log($msg, \Orb\Log\Logger::DEBUG);
            }

            $this->logger->log(sprintf('END ActiveDirectory::authenticate (took %.4fs)', microtime(true) - $time_start), Logger::DEBUG);
        }

        if (!$result->isValid()) {
            return new Result(Result::FAILURE_INVALID_CREDS, null, ['error_code' => 'invalid_credentials', 'error_message' => 'Invalid username or password']);
        }

        $raw_info                      = [];
        $raw_info['identity_friendly'] = $result->getIdentity();

        try {
            $identity = $this->getIdentityForDn($result->getIdentity());

            return new Result(Result::SUCCESS, $identity);
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->log("Exception: {$e->getCode()} {$e->getMessage()}", Logger::ERR);
            }

            return new Result(Result::FAILURE_EXCEPTION, null, ['error_code' => 'exception', 'error_message' => 'An exception occurred', 'exception' => $e]);
        }
    }

    /**
     * Search the AD for the user based on email address.
     */
    public function findRecordViaEmail()
    {
        if (!$this->set_username || !preg_match('#^.+@.+$#', $this->set_username)) {
            return;
        }

        if ($this->logger) {
            $this->logger->log('START Filter for email', Logger::DEBUG);
        }

        $set = false;
        if (!$this->options['accountDomainName']) {
            $set                                = true;
            $this->options['accountDomainName'] = Strings::extractRegexMatch('#@(.*?)$#', $this->set_username, 1);
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

        $filter = sprintf('(&(objectClass=user)(mail=%s))', \Zend\Ldap\Filter::escapeValue($this->set_username));
        if ($this->logger) {
            $this->logger->log("Sending filter: $filter", Logger::DEBUG);
        }

        try {
            $r = $ldap->search($filter, $this->options['baseDn']);
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->log('Failed to search: '.$e->getCode().' '.$e->getMessage(), Logger::DEBUG);
            }

            return;
        }

        if ($set) {
            $this->options['accountDomainName'] = '';
        }

        if ($this->logger) {
            $this->logger->log('Filter results: '.print_r($r->toArray(), 1), Logger::DEBUG);
        }

        if ($r->count() == 1) {
            $arr                      = $r->getFirst();
            $arr['domain']            = $this->options['accountDomainName'];
            $arr['accountDomainName'] = $this->options['accountDomainName'];

            return $arr;
        }

        return;
    }

    /**
     * Search the AD for the user based on username.
     */
    public function findRecordViaUsername()
    {
        if ($this->logger) {
            $this->logger->log('START Filter for username', Logger::DEBUG);
        }

        $set = false;
        if (!$this->options['accountDomainName']) {
            $set                                = true;
            $this->options['accountDomainName'] = Strings::extractRegexMatch('#@(.*?)$#', $this->set_username, 1);
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

        $filter = sprintf('(&(objectClass=user)(sAMAccountName=%s))', \Zend\Ldap\Filter::escapeValue($this->set_username));
        if ($this->logger) {
            $this->logger->log("Sending filter: $filter", Logger::DEBUG);
        }

        try {
            $r = $ldap->search($filter, $this->options['baseDn']);
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->log('Failed to search: '.$e->getCode().' '.$e->getMessage(), Logger::DEBUG);
            }

            return;
        }

        if ($set) {
            $this->options['accountDomainName'] = '';
        }

        if ($this->logger) {
            $this->logger->log('Filter results: '.print_r($r->toArray(), 1), Logger::DEBUG);
        }

        if ($r->count() == 1) {
            $arr                      = $r->getFirst();
            $arr['domain']            = $this->options['accountDomainName'];
            $arr['accountDomainName'] = $this->options['accountDomainName'];

            return $arr;
        }

        return;
    }

    /**
     * @param \Orb\Log\Logger $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return \Orb\Log\Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }
}
