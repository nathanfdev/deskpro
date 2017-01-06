<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
 *
 * @category Entities
 */

namespace Application\DeskPRO\Usersource;

use Application\DeskPRO\Entity\Usersource;
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
            $result_raw .= print_r($result->getIdentity()->getRawData(), true);
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
