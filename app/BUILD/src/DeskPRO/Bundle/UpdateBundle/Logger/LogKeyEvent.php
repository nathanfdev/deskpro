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

namespace DeskPRO\Bundle\UpdateBundle\Logger;

class LogKeyEvent
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var array
     */
    private $data;

    /**
     * LogKeyEvent constructor.
     *
     * @param string|string[] $id
     * @param array           $data
     */
    private function __construct($id, array $data = [])
    {
        $this->id   = is_array($id) ? implode('.', $id) : $id;
        $this->data = $data;
    }

    /**
     * @param string     $id
     * @param \Exception $e
     * @param array      $data
     *
     * @return LogKeyEvent
     */
    public static function createForException($id, \Exception $e, array $data = [])
    {
        $data['exception'] = $e;

        return new self($id, $data);
    }

    /**
     * @param string $id
     * @param array  $data
     *
     * @return LogKeyEvent
     */
    public static function create($id, array $data = [])
    {
        return new self($id, $data);
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed|null
     */
    public function get($key, $default = null)
    {
        return array_key_exists($key, $this->data) ? $this->data[$key] : $default;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->data);
    }
}
