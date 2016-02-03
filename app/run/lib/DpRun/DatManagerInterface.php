<?php

namespace DpRun;

/**
 * This is a low-level util for managing 'dat' files within the user
 * cache directory. These low-level files are meant to store
 * things like trigger files to detect when the helpdesk is offline, etc.
 *
 * The difference between this an a general cache service is that this is
 * very low-level and so should be AVOIDED if your code is in general
 * symfony-land where services are available.
 *
 * There are two types of files this manager handles:
 *
 * - A 'trigger' file is simply a file that exists or does not exist. It
 *   is just a signal (boolean) for a particular thing. E.g., 'is_helpdesk_disabled'
 * - A 'dat' file is any arbitrary data file that contains data. Any data you set
 *   must be primitive PHP values (scalars and arrays only).
 */
interface DatManagerInterface
{
    /**
     * Check if a trigger file is set.
     *
     * @param string $id
     * @return bool
     */
    public function hasTrigger($id);

    /**
     * Enable a trigger file.
     *
     * @param string $id
     * @return bool True for success, false for error
     */
    public function enableTrigger($id);

    /**
     * Disable a trigger file (deletes it).
     *
     * @param string $id
     * @return bool True for success, false for error
     */
    public function disableTrigger($id);

    /**
     * Check if a data file exists.
     *
     * @param string $id
     * @return bool
     */
    public function hasDatFile($id);

    /**
     * Read a data file, or return $default if it does not exist.
     * If $default is the string '__throw__' then a RuntimeException will
     * be raised if the file does not exist or could not be read/decoded.
     *
     * @param string $id
     * @param string $default
     * @return mixed
     */
    public function readDatFile($id, $default = '__throw__');

    /**
     * Write a data file.
     *
     * @param string $id
     * @param array $content
     * @return bool True for success, false for error
     */
    public function writeDatFile($id, array $content);

    /**
     * Remove a data file.
     *
     * @param string $id
     * @return bool True for success, false for error
     */
    public function removeDatFile($id);
}
