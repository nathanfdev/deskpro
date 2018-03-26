<?php

/**
 * Orb.
 */

namespace Orb\Scraper;

interface ItemInterface
{
    /**
     * Gets the unique ID that identifies this remote item.
     * The ID should NOT change. This means that integer ID's are good,
     * but usernames are bad because most systems allow you to change usernames.
     *
     * @return mixed
     */
    public function getIdentity();

    /**
     * This is a human-friendly ID. So the above is a computer friendly ID, such
     * as a userid, and this is one that humans prefer, like a username.
     *
     * @return mixed
     */
    public function getFriendlyIdentity();

    /**
     * The actual data for the item. This must always be a plain PHP array. If
     * the data returned from the resource is a single piece of information, by convention
     * the array should contain a single key 'body',.
     */
    public function getData();
}
