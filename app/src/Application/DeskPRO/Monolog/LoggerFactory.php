<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
 * @category Entities
 */

namespace Application\DeskPRO\Monolog;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Monolog\Handler\NullHandler;
use Orb\Util\Util;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;

class LoggerFactory
{
    /**
     * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    private $container;

    /**
     * @var string
     */
    private $logger_class = 'Application\\DeskPRO\\Monolog\\Logger';

    /**
     * @var array
     */
    private $presets = array();


    /**
     * @param DeskproContainer $container
     */
    public function __construct(DeskproContainer $container)
    {
        $this->container = $container;
    }


    /**
     * @param string $class
     */
    public function setLoggerClass($class)
    {
        $this->logger_class = $class;
    }


    /**
     * @param  string $channel
     * @param  string $preset_name
     * @return Logger
     */
    public function createLoggerFromPreset($channel, array $config = array())
    {
        $preset_name = isset($config['@preset']) ? $config['@preset'] : null;
        unset($config['@preset']);

        switch ($preset_name) {
            case 'upgrader':
                $logger = new $this->logger_class($channel);
                $handler = new ConsoleHandler($config['output']);
                $logger->pushHandler($handler);

                return $logger;
                break;
            default:
                if (isset($this->presets[$preset_name])) {
                    $config = $this->presets[$preset_name];
                } else {
                    $config = array();
                }

                return $this->createLogger($channel, $config);
        }
    }


    /**
     * @param  string $channel
     * @param  array  $config
     * @return Logger
     */
    public function createLogger($channel, array $config)
    {
        $class = $this->logger_class;
        if (isset($config['class'])) {
            $class = $config['class'];
        }

        $handlers = array();
        $processors = array();

        if (!empty($config['handlers'])) {
            foreach ($config['handlers'] as $handler_config) {
                $handler_class = $handler_config['class'];
                $params = isset($handler_config['params']) ? $handler_config['params'] : array();

                // params with a > are shortcuts for get()
                // params with >> are shortcuts for getSystemService
                foreach ($params as &$p) {
                    if (is_string($p)) {
                        if (substr($p, 0, 2) == '>>') {
                            $p = $this->container->getSystemService(substr($p, 2));
                        } elseif ($p[0] == '>') {
                            $p = $this->container->get(substr($p, 1));
                        }
                    }
                }
                unset($p);

                $handler = Util::callUserConstructorArray($handler_class, $params);

                $handlers[] = $handler;
            }
        }
        if (!empty($config['processors'])) {
            foreach ($config['processors'] as $proc_config) {
                $proc_class = $proc_config['class'];
                $params = isset($proc_config['params']) ? $proc_config['params'] : array();

                // params with a > are shortcuts for get()
                // params with >> are shortcuts for getSystemService
                foreach ($params as &$p) {
                    if (is_string($p)) {
                        if (substr($p, 0, 2) == '>>') {
                            $p = $this->container->getSystemService(substr($p, 2));
                        } elseif ($p[0] == '>') {
                            $p = $this->container->get(substr($p, 1));
                        }
                    }
                }
                unset($p);

                $proc = Util::callUserConstructorArray($proc_class, $params);

                $processors[] = $proc;
            }
        }

        if (!$handlers) {
            $handlers[] = new NullHandler();
        }

        $logger = new $class($channel, $handlers, $processors);

        return $logger;
    }
}
