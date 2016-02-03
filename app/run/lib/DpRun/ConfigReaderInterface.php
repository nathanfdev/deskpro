<?php

namespace DpRun;

interface ConfigReaderInterface
{
    /**
     * Read a config value.
     *
     * @param string $id
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getConfig($id, $default = null);

    /**
     * Given the name of a particular file, try to find it within the config directory.
     *
     * @param string $f
     * @return null|string
     */
    public function findConfigFile($f);

    /**
     * Adds a config loader. You function should have the following signature:
     *
     * <code>
     * loader(string $group_id): array
     * </code>
     *
     * @param callable $loader
     * @return void
     */
    public function addConfigLoader($loader);
}
