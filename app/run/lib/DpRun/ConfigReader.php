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

class ConfigReader
{
    /**
     * @var array
     */
    private $config_values = [];

    /**
     * @var array
     */
    private $config_values_short = [];

    /**
     * @var string
     */
    private $config_dir;

    /**
     * ConfigReader constructor.
     *
     * @param string $config_dir
     */
    public function __construct($config_dir)
    {
        $this->config_dir = $config_dir;
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

        // In our well-known config files we name
        // the variable something semantic, this is the map
        // of which varname we use in each file
        static $id_to_varname = [
            'database' => 'DB_CONFIG',
            'logs'     => 'LOGS_CONFIG',
            'paths'    => 'PATHS_CONFIG',
            'settings' => 'SETTINGS_CONFIG',
        ];

        $parts   = explode('.', $id);
        $file_id = array_shift($parts);

        if (isset($this->config_values[$file_id])) {
            $array = $this->config_values[$file_id];
        } else {
            $config_file_path = $this->config_dir.DIRECTORY_SEPARATOR.$file_id.'.php';
            if (file_exists($config_file_path)) {
                $array = $this->_loadConfigFile(
                    $config_file_path,
                    isset($id_to_varname[$file_id]) ? $id_to_varname[$file_id] : 'CONFIG'
                );
            } else {
                $array = [];
            }

            $this->config_values[$file_id] = $array;
        }

        $val = $this->_fetchFromArray($array, $parts, $default);

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
}
