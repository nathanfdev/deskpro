<?php

/**
 * DeskPRO.
 */
class DpShutdown
{
    /**
     * @var \SplPriorityQueue[]
     */
    private static $stack = null;

    /**
     * @var array
     */
    private static $params = [];

    /**
     * @var array
     */
    private static $callbacks;

    /**
     * Inits the queue.
     */
    private static function _init()
    {
        if (self::$stack !== null) {
            return;
        }

        static $has_init_shutdown = false;
        if (!$has_init_shutdown) {
            $has_init_shutdown = true;
            register_shutdown_function(['DpShutdown', 'run']);
        }

        self::$stack     = [];
        self::$callbacks = [];
    }

    /**
     *	Register a new shutdown function.
     *
     * @param callback $callback
     * @param int      $priority
     * @param string   $tag
     */
    public static function add($callback, array $params = null, $tag = null, $priority = 0)
    {
        self::_init();
        if ($tag === null) {
            $tag = 'shutdown';
        }

        static $gen_id = 0;
        ++$gen_id;

        if (!isset(self::$stack[$tag])) {
            self::$stack[$tag] = new \SplPriorityQueue();
        }

        self::$stack[$tag]->insert('cb'.$gen_id, $priority);
        self::$callbacks['cb'.$gen_id] = [$callback, $params, $priority, $tag];
    }

    /**
     * @param string $tag
     *
     * @return bool
     */
    public static function hasTag($tag = null)
    {
        if ($tag === null) {
            $tag = 'shutdown';
        }

        return isset(self::$stack[$tag]);
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public static function setGlobalParam($name, $value, $overwrite = false)
    {
        if (isset(self::$params) && !$overwrite) {
            throw new \InvalidArgumentException("$name is already set");
        }

        self::$params[$name] = $value;
    }

    /**
     * Run all shutdown functions.
     *
     * @param array|string $tags a tag or multiple tags to run
     */
    public static function run($tags = null)
    {
        if ($tags === null) {
            $tags = 'shutdown';
        }

        if (!is_array($tags)) {
            $tags = [$tags];
        }

        // shutdown needs to run all others too
        if (in_array('shutdown', $tags)) {
            $tags = array_keys(self::$stack);
        }

        foreach ($tags as $tag) {
            if (!isset(self::$stack[$tag])) {
                return;
            }

            $proc_stack = self::$stack[$tag];
            unset(self::$stack[$tag]);

            foreach ($proc_stack as $id) {
                if (!isset(self::$callbacks[$id])) {
                    continue;
                }

                $info = self::$callbacks[$id];
                unset(self::$callbacks[$id]);
                $callback = $info[0];

                $pass_params = self::$params;
                if ($info[1]) {
                    $pass_params = array_merge($pass_params, $info[1]);
                }

                call_user_func($callback, $pass_params);
            }
        }
    }
}
