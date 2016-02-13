<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace DpRun;

class ConfigReader implements ConfigReaderInterface
{
    /**
     * In our well-known config files we name
     * the variable something semantic, this is the map
     * of which varname we use in each file. Just a small usability thing.
     * @var array
     */
    private static $id_to_varnames = [
        'database'          => 'DB_CONFIG',
        'logs'              => 'LOGS_CONFIG',
        'paths'             => 'PATHS_CONFIG',
        'settings'          => 'SETTINGS',
        'database_advanced' => 'DB_CONFIG',
        'env'               => 'ENV_CONFIG',
    ];

    /**
     * We put some default files under 'advanced'.
     * @var array
     */
    private static $id_to_path = [
        'env'               => 'advanced',
        'logs'              => 'advanced',
        'settings'          => 'advanced',
        'database_advanced' => 'advanced',
    ];

    /**
     * @var array
     */
    private $config_values = [];

    /**
     * @var array
     */
    private $config_values_short = [];

    /**
     * @var string[]
     */
    private $config_dirs;

    /**
     * @var callable[]
     */
    private $config_loaders = [];

    /**
     * ConfigReader constructor.
     *
     * @param string[]   $config_dirs Paths that can contain config.
     * @param callable[] $config_loaders Functions can can load config
     */
    public function __construct(array $config_dirs, array $config_loaders = [])
    {
        $this->config_dirs = $config_dirs;
        $this->config_loaders = $config_loaders;
    }

    /**
     * Add a config loader
     *
     * @param callable $loader
     */
    public function addConfigLoader($loader)
    {
        $this->config_loaders[] = $loader;
    }

    /**
     * Read a config value.
     *
     * @param string $id
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getConfig($id, $default = null)
    {
        if (array_key_exists($id, $this->config_values_short)) {
            return $this->config_values_short[$id];
        }

        $parts   = explode('.', $id);
        $file_id = array_shift($parts);

        // Special 'all' is a file keyed by $id
        // for other files (mainly as an easier way to define
        // config in a single file, like testing)
        if ($id !== 'all' && !array_key_exists('all', $this->config_values)) {
            $this->getConfig('all');// load all file
        }

        if (!isset($this->config_values[$file_id])) {
            foreach ($this->config_dirs as $config_dir) {
                $config_file_path = $config_dir
                    . DIRECTORY_SEPARATOR
                    . (isset(self::$id_to_path[$file_id]) ? self::$id_to_path[$file_id] . DIRECTORY_SEPARATOR : '')
                    . 'config.' . $file_id . '.php';

                if (file_exists($config_file_path)) {
                    $array = $this->_loadConfigFile(
                        $config_file_path,
                        isset(self::$id_to_varnames[$file_id]) ? self::$id_to_varnames[$file_id] : 'CONFIG'
                    );
                } else {
                    $array = [ ];
                }

                if (!isset($this->config_values[$file_id])) {
                    $this->config_values[$file_id] = $array;
                } else if ($array) {
                    $this->config_values[$file_id] = array_merge($this->config_values[$file_id], $array);
                }
            }
            foreach ($this->config_loaders as $loader) {
                $array = call_user_func($loader, $file_id, $this->config_values[$file_id]) ?: [];

                if (!isset($this->config_values[$file_id])) {
                    $this->config_values[$file_id] = $array;
                } else if ($array) {
                    $this->config_values[$file_id] = array_merge($this->config_values[$file_id], $array);
                }
            }

            // merge in 'all' file
            if ($id !== 'all' && isset($this->config_values['all'][$file_id])) {
                $this->config_values[$file_id] = array_merge($this->config_values['all'][$file_id], $this->config_values[$file_id]);
            }
        }

        $val = $this->_fetchFromArray($this->config_values[$file_id], $parts, $default);

        // Save the value to config_values_short just to avoid
        // lookups on the same key again in future
        return $this->config_values_short[$id] = $val;
    }

    /**
     * @param string $__fp
     * @param string $__varname
     *
     * @return array
     */
    private function _loadConfigFile($__fp, $__varname)
    {
        require $__fp;
        if (isset(${$__varname})) {
            return ${$__varname};
        } else {
            return [];
        }
    }

    /**
     * @param array $array
     * @param array $parts
     * @param mixed $default
     *
     * @return mixed
     */
    private function _fetchFromArray(array $array, array $parts, $default = null)
    {
        if (empty($parts)) {
            return $array;
        }

        $current = $array;
        while ($parts) {
            $next_key = array_shift($parts);
            if (isset($current[$next_key])) {
                $current = $current[$next_key];
            } else {
                // Edge case where the name has dots in it
                $full_id = $next_key.'.'.implode('.', $parts);
                if (isset($current[$full_id])) {
                    return $current[$full_id];
                }

                return $default;
            }
        }

        return $current;
    }

    /**
     * Given the name of a particular file, try to find it within the config directory.
     *
     * @param string $f
     * @return null|string
     */
    public function findConfigFile($f)
    {
        foreach ($this->config_dirs as $dir) {
            $path  = $dir . DIRECTORY_SEPARATOR . $f;
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function resetCache()
    {
        $this->config_values = [];
        $this->config_values_short = [];
    }
}
