<?php
/**
 * Orb
 *
 * @package Orb
 * @subpackage Scraper
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Orb\Scraper;

/**
 * An item returned by a scraper
 */
class Item implements \Orb\Scraper\ItemInterface
{
	protected $identity;
	protected $identity_friendly;
	protected $data;

	public function __construct($identity, $identity_friendly, $data)
	{
		$this->identity = $identity;
		$this->identity_friendly = $identity_friendly;

		if (!is_array($data)) {
			$data = array('body' => $data);
		}

		$this->data = $data;
	}



	/**
	 * Gets the unique ID that identifies this remote item.
	 * The ID should NOT change. This means that integer ID's are good,
	 * but usernames are bad because most systems allow you to change usernames.
	 *
	 * @return mixed
	 */
	public function getIdentity()
	{
		return $this->identity;
	}


	/**
	 * This is a human-friendly ID. So the above is a computer friendly ID, such
	 * as a userid, and this is one that humans prefer, like a username.
	 *
	 * @return mixed
	 */
	public function getFriendlyIdentity()
	{
		return $this->identity_friendly;
	}


	/**
	 * The actual data for the item.
	 *
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}
}