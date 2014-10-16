<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Cache
 */
namespace Application\DeskPRO\Cache;


/**
 * All cache adapters implement this interface. A cache adapter must return the value exactly as it recieves it. So
 * the adapter must store the cached value in such a way that it can always (obv including future requests if need be)
 * return the same php value back.
 *
 * If we set an array, an object, etc, it must be returned equally. Must be a valid php value, but cannot be a callable,
 * or a closure.
 *
 * Recommended to store scalars or plain scalar arrays for best results.
 */
interface CacheAdapterInterface
{
	/**
	 * Set a value onto the cache
	 *
	 * @param $key
	 * @param $val
	 * @return mixed
	 */
	public function set($key, $val);


	/**
	 * True if cache appears to have a value for the key
	 *
	 * @param $key
	 * @return bool
	 */
	public function has($key);


	/**
	 * Gets the value for a key, in the same form as it was set (returns arrays, objects, scalars, etc)
	 *
	 * @param $key
	 * @return null|mixed
	 */
	public function get($key);


	/**
	 * Removes the value and unsets the key, should be safe to call even if key doesn't exist
	 *
     * @param $key
	 * @return mixed
	 */
	public function delete($key);
}