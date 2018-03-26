<?php

/**
 * DeskPRO.
 */

namespace Orb\Auth\Adapter;

use Doctrine\DBAL\Connection;
use Orb\Auth\Identity;
use Orb\Auth\Result;
use Orb\Log\Loggable;
use Orb\Log\Logger;
use Orb\Util\Arrays;

class DbTable extends PluginAdapter implements FormLoginInterface, UserInfoFetchableInterface, Loggable
{
    const OPT_TABLE                   = 'table';
    const OPT_FIELD_ID                = 'field_id';
    const OPT_FIELD_USERNAME          = 'field_username';
    const OPT_FIELD_EMAIL             = 'field_email';
    const OPT_FIELD_PASSWORD          = 'field_password';
    const OPT_FIELD_FIRST_NAME        = 'field_first_name';
    const OPT_FIELD_LAST_NAME         = 'field_last_name';
    const OPT_FIELD_NAME              = 'field_name';
    const OPT_PASSWORD_HASH           = 'password_hash_scheme';
    const OPT_PASSWORD_CHECK_CALLBACK = 'password_check_callback';

    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $db;

    /**
     * @var callable
     */
    protected $db_callback = null;

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
     * @var \Orb\Util\OptionsArray
     */
    protected $options;

    public function __construct($db, array $options)
    {
        if ($db instanceof Connection) {
            $this->db = $db;
        } else {
            $this->db_callback = $db;
        }

        $this->initOptions();
        $this->options->setArray($options);
    }

    /**
     * @return Connection|mixed|null
     */
    public function getDb()
    {
        if ($this->db) {
            return $this->db;
        }

        try {
            $this->db = call_user_func($this->db_callback);
            if ($this->logger) {
                $this->logger->logDebug('Database connection success');
            }
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->logDebug("Error trying to connect to database: {$e->getCode()} {$e->getMessage()}");
            }

            return;
        }

        return $this->db;
    }

    protected function initOptions()
    {
        $opts = [
            self::OPT_TABLE            => '',
            self::OPT_FIELD_ID         => 'id',
            self::OPT_FIELD_USERNAME   => null,
            self::OPT_FIELD_EMAIL      => null,
            self::OPT_FIELD_PASSWORD   => 'password',
            self::OPT_FIELD_FIRST_NAME => null,
            self::OPT_FIELD_LAST_NAME  => null,
            self::OPT_FIELD_NAME       => null,
        ];
        $this->options = new \Orb\Util\OptionsArray($opts);
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

    public function doAuthenticate()
    {
        if (!$this->set_username) {
            if ($this->logger) {
                $this->logger->logDebug('Missing username');
            }

            return new Result(Result::FAILURE, null, ['error_code' => 'missing_input_username', 'error_message' => 'No username provided']);
        }
        if (!$this->set_password) {
            if ($this->logger) {
                $this->logger->logDebug('Missing password');
            }

            return new Result(Result::FAILURE, null, ['error_code' => 'missing_input_password', 'error_message' => 'No password provided']);
        }

        $time_start = microtime(true);
        if ($this->logger) {
            $this->logger->log('START DbTable::authenticate', Logger::DEBUG);

            $log_opt                = $this->options->all();
            $log_opt['db_password'] = '***';
            $this->logger->log('Options: '.trim(Arrays::implodeTemplate($log_opt, "{KEY}: {VAL}\n")), Logger::DEBUG);
            $this->logger->log("Request: {$this->set_username}:{$this->set_password}", Logger::DEBUG);
        }

        try {
            $try = [];
            if (\Orb\Validator\StringEmail::isValueValid($this->set_username)) {
                $try[] = 'getUserInfoForEmail';
            }
            $try[] = 'getUserInfoForUsername';

            $userinfo = null;
            foreach ($try as $m) {
                $userinfo = $this->$m($this->set_username);
                if (!$userinfo) {
                    continue;
                }

                if (!empty($this->options[self::OPT_PASSWORD_CHECK_CALLBACK])) {
                    $pass = call_user_func($this->options[self::OPT_PASSWORD_CHECK_CALLBACK], $userinfo, $this->set_password);
                } else {
                    $pass = $this->isValidPassword($userinfo, $this->set_password);
                }

                if ($pass) {
                    break;
                } else {
                    $userinfo = null;
                }
            }

            if (!$userinfo) {
                if ($this->logger) {
                    $this->logger->logDebug('Invalid credentials');
                }

                return new Result(Result::FAILURE_INVALID_CREDS);
            }
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->log("Exception: {$e->getCode()} {$e->getMessage()}\n{$e->getTraceAsString()}", Logger::ERR);
            }

            return new Result(Result::FAILURE_EXCEPTION, null, ['error_code' => 'exception', 'error_message' => 'An exception occurred', 'exception' => $e]);
        }

        $identity = $this->getIdentityFromUserInfo($userinfo);
        if (!$identity->getIdentity()) {
            $this->logger->log('No identity id provided', Logger::DEBUG);

            return new Result(Result::FAILURE, null, ['error_code' => 'missing_identity', 'error_message' => 'No identity id provided']);
        }

        if ($this->logger) {
            if ($userinfo) {
                $this->logger->log('Found user '.$identity->getIdentity(), Logger::DEBUG);
            } else {
                $this->logger->log('No user found', Logger::DEBUG);
            }

            $this->logger->log(sprintf('END DbTable::authenticate (took %.4fs)', microtime(true) - $time_start), Logger::DEBUG);
        }

        return new Result(Result::SUCCESS, $identity);
    }

    /**
     * Get an Identity from a userinfo array.
     *
     * @param array $userinfo
     *
     * @return \Orb\Auth\Identity
     */
    public function getIdentityFromUserInfo(array $userinfo)
    {
        $userinfo = Arrays::removeEmptyString($userinfo);

        // Map fields from the raw userinfo to common fields that most
        // auth adapters use by convention
        $map = [
            self::OPT_FIELD_EMAIL      => 'email_address',
            self::OPT_FIELD_FIRST_NAME => 'first_name',
            self::OPT_FIELD_LAST_NAME  => 'last_name',
            self::OPT_FIELD_NAME       => 'name',
        ];

        foreach ($map as $field_key => $info_key) {
            $field = $this->options[$field_key];
            if (!$field || empty($userinfo[$field])) {
                continue;
            }

            $userinfo[$info_key] = $userinfo[$field];
        }

        $identity = new Identity($userinfo[$this->options[self::OPT_FIELD_ID]], $userinfo);

        return $identity;
    }

    /**
     * Checks an inputted password against a found user info record to see if it matches.
     *
     * @param string $userinfo
     * @param string $password_input
     *
     * @return bool
     */
    protected function isValidPassword(array $userinfo, $password_input)
    {
        $field = $this->options[self::OPT_FIELD_PASSWORD];

        switch ($this->options[self::OPT_PASSWORD_HASH]) {
            case 'md5':
                $password_compare = md5($password_input);
                break;

            case 'sha1':
                $password_compare = sha1($password_input);
                break;

            default:
                $password_compare = $password_input;
                break;
        }

        return $userinfo[$field] == $password_compare;
    }

    public function getAllUserInfo($offset = 0, $limit = 1000)
    {
        if (!$this->getDb()) {
            return [];
        }

        $table  = $this->options[self::OPT_TABLE];
        $result = $this->db
            ->executeQuery(sprintf('SELECT * FROM %s LIMIT %d, %d', $table, $offset, $limit))
            ->fetchAll();

        if (!$result) {
            return [];
        }

        return $result;
    }

    /**
     * Get user info from a username.
     *
     * @param $username
     *
     * @return array
     */
    public function getUserInfoForUsername($username)
    {
        if (!$this->options[self::OPT_FIELD_USERNAME]) {
            return;
        }

        if (!$this->getDb()) {
            return;
        }

        $table  = $this->options[self::OPT_TABLE];
        $field  = $this->options[self::OPT_FIELD_USERNAME];
        $driver = $this->db->getDriver()->getName();
        if ($driver == 'pdo_dblib' || $driver == 'pdo_sqlsrv' || $driver == 'sqlsrv' || $driver == 'pdo_odbc') {
            $field_e = $this->db->quote($username, \PDO::PARAM_STR);
            $sql     = "SELECT TOP 1 * FROM $table WHERE $field = $field_e";
            $result  = $this->db->fetchAssoc($sql);
        } else {
            $sql    = "SELECT * FROM $table WHERE $field = ? LIMIT 1";
            $result = $this->db->fetchAssoc($sql, [$username]);
        }

        if (!$result) {
            return;
        }

        return $result;
    }

    /**
     * Get user info from an email address.
     *
     * @return array
     */
    public function getUserInfoForEmail($email)
    {
        if (!$this->options[self::OPT_FIELD_EMAIL]) {
            return;
        }

        if (!$this->getDb()) {
            return;
        }

        $table  = $this->options[self::OPT_TABLE];
        $field  = $this->options[self::OPT_FIELD_EMAIL];
        $driver = $this->db->getDriver()->getName();
        if ($driver == 'pdo_dblib' || $driver == 'pdo_sqlsrv' || $driver == 'sqlsrv' || $driver == 'pdo_odbc') {
            $field_e = $this->db->quote($email, \PDO::PARAM_STR);
            $sql     = "SELECT TOP 1 * FROM $table WHERE $field = $field_e";
            $result  = $this->db->fetchAssoc($sql);
        } else {
            $sql    = "SELECT * FROM $table WHERE $field = ? LIMIT 1";
            $result = $this->db->fetchAssoc($sql, [$email]);
        }

        if (!$result) {
            return;
        }

        return $result;
    }

    /**
     * Get user info from an email address.
     *
     * @return array
     */
    public function getUserInfoForId($id)
    {
        $table = $this->options[self::OPT_TABLE];
        $field = $this->options[self::OPT_FIELD_ID];

        if (!$this->getDb()) {
            return;
        }

        $driver = $this->db->getDriver()->getName();
        if ($driver == 'pdo_dblib' || $driver == 'pdo_sqlsrv' || $driver == 'sqlsrv' || $driver == 'pdo_odbc') {
            $field_e = $this->db->quote($id, \PDO::PARAM_STR);
            $sql     = "SELECT TOP 1 * FROM $table WHERE $field = $field_e ";
            $result  = $this->db->fetchAssoc($sql);
        } else {
            $sql    = "SELECT * FROM $table WHERE $field = ? LIMIT 1";
            $result = $this->db->fetchAssoc($sql, [$id]);
        }

        if (!$result) {
            return;
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getUserInfoFromIdentity($id, $id_type = null)
    {
        $try = [];
        if ($id_type === 'email' || (!$id_type && \Orb\Validator\StringEmail::isValueValid($id))) {
            $try[] = 'getUserInfoForEmail';
        }

        if ($id_type == 'username' || !$id_type) {
            $try[] = 'getUserInfoForUsername';
        }

        if ($id_type == 'id' || (!$id_type && \Orb\Util\Numbers::isInteger($id))) {
            $try[] = 'getUserInfoForId';
        }

        $userinfo = null;
        foreach ($try as $m) {
            $userinfo = $this->$m($this->set_username);
            if ($userinfo) {
                break;
            }
        }

        return $userinfo;
    }

    /**
     * @param \Orb\Log\Logger $logger
     */
    public function setLogger(\Orb\Log\Logger $logger)
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
