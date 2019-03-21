<?php

/**
 * DeskPRO.
 */

namespace DpSys\LowScript;

use DpRun\LowUtil;
use DpSys\LowError\SystemErrorHandler;
use Orb\Util\Util;

abstract class LowScriptAbstract
{
    /**
     * @var \DpRun\DpEnv
     */
    protected $dpEnv;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @var string
     */
    protected $base_url;

    /**
     * @var string
     */
    protected $path_info;

    /**
     * @var string
     */
    protected $request_uri;

    /**
     * @var \PDO
     */
    protected $pdo;

    /**
     * @var \PDO
     */
    protected $pdo_read;

    /**
     * @var \PDO
     */
    protected $pdoVoice;

    /**
     * @var array
     */
    protected $settings;

    /**
     * serve_abstract constructor.
     *
     * @param \DpRun\DpEnv                              $dpEnv
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function __construct(\DpRun\DpEnv $dpEnv, \Symfony\Component\HttpFoundation\Request $request)
    {
        $this->dpEnv   = $dpEnv;
        $this->request = $request;

        if (extension_loaded('newrelic')) {
            newrelic_name_transaction($request->getPathInfo());
        }
    }

    public function run()
    {
        $this->runAction();

        if (session_id() != '') {
            session_write_close();
        }
    }

    /**
     * @return mixed
     */
    abstract protected function runAction();

    /**
     * Handle a fatal exception.
     *
     * @param \Exception $e
     */
    protected function handleException(\Exception $e)
    {
        try {
            $container = $this->bootFullSystem();
        } catch (\Exception $e) {
            error_log("Error handling error: {$e->getMessage()}");
            echo 'Error while processing error';
            exit(1);
        }

        SystemErrorHandler::handleException($e);

        header('HTTP/1.1 500 Internal Server Error');
        echo 'There was an error while processing your request.';
        exit(1);
    }

    /**
     * @return \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    protected function bootFullSystem()
    {
        static $container;

        if (!$container) {
            $res = \DpSys\Boot\Boot::runBootTasks($this->dpEnv, [
                'HttpKernel',
            ], [
                'request'      => $this->request,
                'interface_id' => 'sys',
            ]);

            $kernel = $res['http_kernel'];
            $kernel->boot();

            /** @var $container \Application\DeskPRO\DependencyInjection\DeskproContainer */
            $container = $kernel->getContainer();

            // Set PDO now that we are connected...
            if (!$this->pdo) {
                $this->pdo = $container->getDb();
            }
            if (!$this->pdo_read) {
                $this->pdo_read = $container->getDbRead();
            }
        }

        return $container;
    }

    /**
     * @return \PDO
     */
    public function getPdo()
    {
        if ($this->pdo) {
            return $this->pdo;
        }

        $this->pdo = LowUtil::getPdoFromMysqlInfo($this->dpEnv->getConfig('database'));

        return $this->pdo;
    }

    /**
     * @return \PDO
     */
    public function getPdoRead()
    {
        if ($this->pdo_read) {
            return $this->pdo_read;
        }

        $read_config = $this->dpEnv->getConfig('database_advanced.read');

        // verify the config is actually set
        if ($read_config) {
            $read_config = LowUtil::getMysqlInfoFromConfigArray($read_config);
            if (empty($read_config['host']) || empty($read_config['dbname'])) {
                $read_config = null;
            }
        }

        if ($read_config) {
            $this->pdo_read = LowUtil::getPdoFromMysqlInfo($read_config);
        } else {
            $this->pdo_read = $this->getPdo();
        }

        return $this->pdo_read;
    }

    /**
     * @return \PDO
     */
    protected function getVoicePdo()
    {
        if ($this->pdoVoice) {
            return $this->pdoVoice;
        }

        $voiceConfig = $this->dpEnv->getConfig('database_advanced.voice');

        // verify the config is actually set
        if ($voiceConfig) {
            $voiceConfig = LowUtil::getMysqlInfoFromConfigArray($voiceConfig);
            if (empty($voiceConfig['host']) || empty($voiceConfig['dbname'])) {
                $voiceConfig = null;
            }
        }

        if ($voiceConfig) {
            $this->pdoVoice = LowUtil::getPdoFromMysqlInfo($voiceConfig);
        } else {
            $this->pdoVoice = $this->getPdo();
        }

        return $this->pdoVoice;
    }

    /**
     * @return array
     */
    public function getAllSettings()
    {
        if ($this->settings !== null) {
            return $this->settings;
        }

        $this->settings = [];

        $q = $this->getPdo()->prepare('
            SELECT name, value
            FROM settings
        ');
        $q->execute();
        while ($row = $q->fetch(\PDO::FETCH_NUM)) {
            $this->settings[$row[0]] = $row[1];
        }

        return $this->settings;
    }

    /**
     * @param string $name
     * @param null   $default
     *
     * @return mixed
     */
    public function getSetting($name, $default = null)
    {
        if ($this->settings === null) {
            $this->getAllSettings();
        }

        return isset($this->settings[$name]) ? $this->settings[$name] : $default;
    }

    //###################################################################################################################
    // Request Helpers
    //###################################################################################################################

    /**
     * @param bool $own Own pathinfo means we dont consider ourselfs as part of the path. In other
     *                  words, we shift off the first segment of the URL
     *
     * @return string
     */
    public function getPathInfo($own = true)
    {
        $pathinfo = $this->request->getPathInfo();
        if ($own) {
            $pathinfo = preg_replace('#^/.*?/#', '/', $pathinfo);
        }

        return $pathinfo;
    }

    public function getBaseUrl()
    {
        return $this->request->getBaseUrl();
    }

    public function getRequestUri()
    {
        return $this->request->getRequestUri();
    }

    public function getScheme()
    {
        return $this->request->getScheme();
    }

    public function isSecure()
    {
        return $this->request->isSecure();
    }

    public function getHttpHost()
    {
        return $this->request->getHttpHost();
    }

    public function getPort()
    {
        return isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : null;
    }

    public function getHost()
    {
        return $this->request->getHost();
    }

    public static function formatBacktrace(array $backtrace)
    {
        $trace = '';
        foreach ($backtrace as $k => $v) {
            $line = "#$k ";

            if (isset($v['object'])) {
                $line .= get_class($v['object']).'::';
            } elseif (isset($v['class'])) {
                $line .= $v['class'].'::';
            }

            $line .= "{$v['function']}(";

            if (!empty($v['args'])) {
                $line .= self::varToString($v['args']);
            }

            $line .= ')';

            if (!empty($v['file'])) {
                $line .= " called at [{$v['file']}:{$v['line']}]";
            }

            $line .= "\n";

            $trace .= $line;
        }

        return $trace;
    }

    public static function varToString($var)
    {
        if (is_object($var)) {
            return sprintf('[object](%s)', get_class($var));
        }
        if (is_array($var)) {
            $a = [];
            foreach ($var as $k => $v) {
                $a[] = sprintf('%s => %s', $k, self::varToString($v));
            }

            return sprintf('[array](%s)', implode(', ', $a));
        }
        if (is_resource($var)) {
            return '[resource]';
        }

        return str_replace("\n", '', var_export((string) $var, true));
    }

    /**
     * @return array
     */
    protected function getAgentSession()
    {
        $agentSessionId = isset($_COOKIE['dpsid-agent']) ? strval($_COOKIE['dpsid-agent']) : '';
        if (!$agentSessionId) {
            echo 'no session';
            exit;
        }

        if (!strpos($agentSessionId, '-')) {
            echo 'no session';
            exit;
        }
        list($sessionId) = explode('-', $agentSessionId, 2);
        $sessionId       = Util::baseDecode($sessionId, Util::BASE36_ALPHABET);

        $agentSession = $this->getPdoRead()->query('
                SELECT sessions.*, people.is_agent
                FROM sessions
                INNER JOIN people ON (sessions.person_id = people.id)
                WHERE sessions.id = '.$this->getPdoRead()->quote($sessionId)
        )->fetch(\PDO::FETCH_ASSOC);
        if (!$agentSession || $agentSessionId !== (Util::baseEncode($agentSession['id'], Util::BASE36_ALPHABET).'-'.$agentSession['auth'])) {
            echo 'no/invalid session';
            exit;
        }

        if (!$agentSession['is_agent']) {
            echo 'invalid session';
            exit;
        }

        return $agentSession;
    }
}
