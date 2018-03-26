<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

/**
 * Standard base for storing display information, such as fields or widgets on a page.
 *
 * @see \Application\DeskPRO\PageDisplay\Zone\BasicZone
 */
abstract class PageDisplayAbstract extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * @var int
     */
    protected $id = null;

    /**
     * The actual section within the page that this description applies (ex 'toptabs').
     *
     * @var string
     */
    protected $section = 'default';

    /**
     * This is a plain data array that is fed into the handler class
     * to reconstruct the display strcuture.
     *
     * @var array
     */
    protected $data = [];

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
