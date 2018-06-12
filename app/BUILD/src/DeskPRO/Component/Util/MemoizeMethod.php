<?php

namespace DeskPRO\Component\Util;

trait MemoizeMethod
{
    /**
     * @var array [method][id]
     */
    private $memoizedResults = [];

    /**
     * Memoize using a method called $methodName with a prefix underscore. This makes this a bit easier to use.
     *
     * <code>
     * class Foo
     * {
     *     use MemoizeMethod;
     *
     *     public function getFoo($a, $b)
     *     {
     *         return $this->memoizedInit(__FUNCTION__, [$a, $b]);
     *     }
     *
     *     private function _getFoo($a, $b)
     *     {
     *         return $a * $b;
     *     }
     * }
     * </code>
     *
     * @param string $methodName
     * @param string $args
     * @param null   $id
     *
     * @return mixed
     */
    protected function memoizedInit($methodName, $args, $id = null)
    {
        $methodName = '_'.$methodName;

        return $this->memoizedMethod($methodName, $args, $id);
    }

    /**
     * @param string      $methodName The method to call to get the value
     * @param array       $args       The args to pass
     * @param string|null $id         An ID to give this cached result. If null, then $args will be serialized and used as the id
     *
     * @return mixed
     */
    protected function memoizedMethod($methodName, $args, $id = null)
    {
        if ($id === null) {
            if (empty($args)) {
                $id = '_';
            } else {
                $id = \serialize($args);
            }
        }
        if (!isset($this->memoizedResults[$methodName]) || !array_key_exists($id, $this->memoizedResults[$methodName])) {
            if (!isset($this->memoizedResults[$methodName])) {
                $this->memoizedResults[$methodName] = [];
            }
            $this->memoizedResults[$methodName][$id] = $this->$methodName(...$args);
        }

        return $this->memoizedResults[$methodName][$id];
    }

    /**
     * @param callable $fn
     * @param string   $id
     *
     * @return mixed
     */
    protected function memoizedRun($fn, $id)
    {
        if (!isset($this->memoizedResults['@run']) || !array_key_exists($id, $this->memoizedResults['@run'])) {
            if (!isset($this->memoizedResults['@run'])) {
                $this->memoizedResults['@run'] = [];
            }
            $this->memoizedResults['@run'][$id] = call_user_func($fn);
        }

        return $this->memoizedResults['@run'][$id];
    }
}
