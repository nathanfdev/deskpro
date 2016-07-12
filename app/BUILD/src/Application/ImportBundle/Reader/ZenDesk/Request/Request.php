<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace Application\ImportBundle\Reader\ZenDesk\Request;

/**
 * Class Request.
 */
class Request
{
    /**
     * @var string
     */
    private $api_group;

    /**
     * @var string
     */
    private $entity_type;

    /**
     * @var string
     */
    private $method;

    /**
     * @var array
     */
    private $params = [];

    /**
     * Constructor.
     *
     * @param string $api_group
     * @param string $entity_type
     * @param string $method
     * @param array  $params
     */
    public function __construct($api_group, $entity_type, $method, array $params = [])
    {
        $this->api_group   = $api_group;
        $this->entity_type = $entity_type;
        $this->method      = $method;
        $this->params      = $params;
    }

    /**
     * @return string
     */
    public function getApiGroup()
    {
        return $this->api_group;
    }

    /**
     * @return string
     */
    public function getEntityType()
    {
        return $this->entity_type;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @return string
     */
    public function concatClass()
    {
        return $this->api_group.'\\'.$this->entity_type;
    }

    /**
     * @return string
     */
    public function concatMethod()
    {
        return $this->concatClass().'::'.$this->method;
    }

    /**
     * @param string $helper_string
     * @param array  $params
     *
     * @return Request
     */
    public static function createFromString($helper_string, array $params = [])
    {
        if (preg_match('/^(\w+)\x5c(\w+)::(\w+)$/', $helper_string, $matches)) {
            return new self($matches[1], $matches[2], $matches[3], $params);
        }

        throw new \RuntimeException(sprintf('Unable to parse ZD request `%s`', $helper_string));
    }

    /**
     * Create CoreAPI request object.
     *
     * @param string $entity_type
     * @param string $method
     * @param array  $params
     *
     * @return Request
     */
    public static function createCoreAPI($entity_type, $method, array $params = [])
    {
        return new self('CoreAPI', $entity_type, $method, $params);
    }

    /**
     * Create help center request object.
     *
     * @param string $entity_type
     * @param string $method
     * @param array  $params
     *
     * @return Request
     */
    public static function createHelpCenter($entity_type, $method, array $params = [])
    {
        return new self('HelpCenter', $entity_type, $method, $params);
    }
}
