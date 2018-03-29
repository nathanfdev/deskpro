<?php

/**
 * Orb.
 */

namespace Orb\Scraper;

/**
 * A scraper is something that fetches data from some remote source. The method of fetching
 * the data, or the format of that data, is not relevant (that'll matter for whatever
 * system implements the system).
 */
abstract class AbstractScraper
{
    /**
     * Array of options.
     *
     * @var array
     */
    protected $_options;

    public function __construct(array $options = [])
    {
        $this->_options = $options;
    }

    /**
     * Get the value of an option.
     *
     * @param string $key     The option to get
     * @param mixed  $default What to return if the option doesnt exist
     */
    public function getOption($key, $default = null)
    {
        return isset($this->_options[$key]) ? $this->_options[$key] : $default;
    }

    /**
     * Check to see if an option exists.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasOption($key)
    {
        return isset($this->_options[$key]);
    }

    /**
     * @param mixed $identity Info we're requesting. A URL, an ID, etc. Depends on the scraper
     *
     * @return ItemInterface
     */
    abstract public function getData($identity = null);
}
