<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\DeskPRO\Cache;

/**
 * Decorates the raw cache adapter with convenient awesomeness. Largely inspired by Laravel's cache component.
 *
 * Most of our services that actually hand you a cache (except for the lowest levels) will be decorated by this.
 *
 * Allows you to do the following as an extension to any adapter:
 *
 *  $convenient->get('key', 'some default string')
 *
 * The underlying adapter will be asked if it has a cache entry (rem, the adapter handles expiration etc itself)
 * and if not it will set the default string on the cache and return the default. This is awesome because we no longer
 * have to do the has -> get -> set logic every time we want to do this use case.
 *
 * Even more awesome: the default can be anything, including any callable
 *
 *  $template = $convenient->get('brand2.template5', function () use ($template_service, $something) {
 *     $template_service->render($something)
 *  });
 *
 */
class ConvenientCache implements CacheAdapterInterface
{
    /**
     * @var CacheAdapterInterface
     */
    private $adapter;

    /**
     * @param CacheAdapterInterface $adapter
     */
    public function __construct(CacheAdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @return CacheAdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Similar to CacheAdapterInterface but adds a default value (which also sets on cache if used)
     *
     * @param             $key
     * @param  null       $default
     * @param  array      $params if $default is a callable, $params will be passed as arguments
     * @return mixed|null
     */
    public function get($key, $default = null, array $params = array())
    {
        if ($default && !$this->adapter->has($key)) {
            $val = $this->resolveDefault($default, $params);

            $this->adapter->set($key, $val);

            return $val;
        }

        return $this->adapter->get($key);
    }

    protected function resolveDefault($val, array $params)
    {
        if (is_callable($val)) {
            return call_user_func_array($val, $params);
        }

        return $val;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $val)
    {
        $this->adapter->set($key, $val);
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        return $this->adapter->has($key);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        return $this->adapter->delete($key);
    }
}
