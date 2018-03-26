<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Usersource;

use Application\DeskPRO\Entity\Usersource;
use Application\DeskPRO\Util;
use DeskPRO\Component\Util\MapUtils;
use DpSys\LowError\SystemErrorHandler;
use Orb\Auth\Adapter\AdapterInterface;
use Orb\Log\Logger;
use Orb\Log\Writer\ArrayWriter;

class UsersourceTester
{
    /**
     * @var Adapter\AbstractAdapter
     */
    private $adapter;

    /**
     * @var bool
     */
    private $has_run = false;

    /**
     * @var bool
     */
    private $is_valid;

    /**
     * @var string
     */
    private $log;

    /**
     * @var array
     */
    private $raw_data;

    /**
     * @param string $type
     * @param array  $settings
     *
     * @return UsersourceTester
     */
    public static function createFromOptions($type, array $settings)
    {
        $us              = new Usersource();
        $us->source_type = $type;
        $us->setOptions($settings);

        return new self($us->getAdapter()->getAuthAdapter());
    }

    /**
     * @param Usersource $usersource
     *
     * @return UsersourceTester
     */
    public static function createFromUsersource(Usersource $usersource)
    {
        return new self($usersource->getAdapter()->getAuthAdapter());
    }

    /**
     * @param AdapterInterface $adapter
     */
    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @param string $username
     * @param string $password
     *
     * @throws \RuntimeException
     *
     * @return bool
     */
    public function test($username, $password)
    {
        if ($this->has_run) {
            throw new \RuntimeException('Test has already been run');
        }

        try {
            return $this->doTest($username, $password);
        } catch (\Exception $e) {
            $log = 'There was an error while performing your test ('.get_class($e).')';
            $log .= "\n\n";
            $log .= $e->getMessage();
            $log .= "\n\n";
            $log .= SystemErrorHandler::formatBacktrace($e->getTrace(), true);
            $this->log      = $log;
            $this->is_valid = false;

            return false;
        }
    }

    /**
     * @param string $username
     * @param string $password
     *
     * @throws \RuntimeException
     *
     * @return bool
     */
    private function doTest($username, $password)
    {
        if ($this->has_run) {
            throw new \RuntimeException('Test has already been run');
        }

        $this->has_run = true;
        $adapter       = $this->adapter;

        $logger = new Logger();
        $arr_wr = new ArrayWriter();
        $logger->addWriter($arr_wr);

        $logger->logDebug('Adapter: '.get_class($adapter));
        $logger->logDebug("Test: $username :: $password");
        $logger->logDebug('--- Begin ---');
        $start = microtime(true);

        if (method_exists($adapter, 'setLogger')) {
            $adapter->setLogger($logger);
        }

        $adapter->setFormData([
            'username' => $username,
            'password' => $password,
        ]);
        $result = $adapter->authenticate();

        $time = microtime(true) - $start;
        $logger->logDebug('--- Done ('.sprintf('%.4f', $time).'s) ---');

        $log = implode("\n", $arr_wr->getMessages());

        if ($result && $result->isValid() && $result->getIdentity()) {
            $result_raw = "DATA RECORD:\n=======================================================\n";
            $data       = $this->_cleanDataForLog($result->getIdentity()->getRawData());
            $result_raw .= print_r($data, true);
        } else {
            $result_raw = 'No Identity';
        }

        $this->log      = $log;
        $this->raw_data = $result_raw;
        $this->is_valid = $result->isValid();

        return $this->is_valid;
    }

    /**
     * @throws \RuntimeException
     *
     * @return string
     */
    public function getLog()
    {
        if (!$this->has_run) {
            throw new \RuntimeException('You must run test() first');
        }

        return $this->log;
    }

    /**
     * @throws \RuntimeException
     *
     * @return array
     */
    public function getRawData()
    {
        if (!$this->has_run) {
            throw new \RuntimeException('You must run test() first');
        }

        return $this->raw_data;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function _cleanDataForLog(array $data)
    {
        $fn = function ($k, $v) use (&$fn) {
            if (is_array($v)) {
                return MapUtils::mapValues($v, $fn);
            } else {
                if (is_string($v) && !empty($v)) {
                    if (strpos('photo', $k) !== false || strpos('picture', $k) !== false || strpos('cert', $k) !== false) {
                        return '[binary data]';
                    } else {
                        // some data might still break json encoding, so we're doing a test on every value
                        $enc = Util::jsonEncode($v);
                        if (!$enc) {
                            return 'string:'.strlen($v);
                        }
                    }
                }
            }

            return $v;
        };

        return MapUtils::mapValues($data, $fn);
    }

    /**
     * @throws \RuntimeException
     *
     * @return bool
     */
    public function isValid()
    {
        if (!$this->has_run) {
            throw new \RuntimeException('You must run test() first');
        }

        return $this->is_valid;
    }
}
