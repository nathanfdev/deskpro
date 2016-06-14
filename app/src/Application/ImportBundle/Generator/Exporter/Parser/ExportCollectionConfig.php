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

namespace Application\ImportBundle\Generator\Exporter\Parser;

/**
 * Class ExportCollectionConfig.
 */
class ExportCollectionConfig
{
    /**
     * @var \Traversable|array
     */
    private $data = array();

    /**
     * @var string
     */
    private $prefix;

    /**
     * @var string
     */
    private $ref_column;

    /**
     * @var string|callable
     */
    private $method;

    /**
     * @var bool
     */
    private $advance_progressbar = false;

    /**
     * @param \Traversable|array $data
     *
     * @return $this
     */
    public function setData($data)
    {
        if (!is_array($data) && !$data instanceof \Traversable) {
            throw new \RuntimeException('Export collection data should be array or instance of Traversable');
        }

        $this->data = $data;

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @param string $prefix
     *
     * @return $this
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * @return string
     */
    public function getRefColumn()
    {
        return $this->ref_column;
    }

    /**
     * @param string $ref_column
     *
     * @return $this
     */
    public function setRefColumn($ref_column)
    {
        $this->ref_column = $ref_column;

        return $this;
    }

    /**
     * @return callable|string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param callable|string $method
     *
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAdvanceProgressbar()
    {
        return $this->advance_progressbar;
    }

    /**
     * @param bool $advance_progressbar
     *
     * @return $this
     */
    public function setAdvanceProgressbar($advance_progressbar)
    {
        $this->advance_progressbar = (bool) $advance_progressbar;

        return $this;
    }
}
