<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * A scraper is something that fetches data from a remote resource. This is the abstract
 * scraper type, but each different type of scrape defines its own entity and may have
 * its own dramatically different processing/handling.
 *
 * Actual scrapers are also responsible for how to store any scraped data (hence there is no
 * use in an abstract ScraperData class).
 */
abstract class Scraper
{
    /**
     * The unique ID.
     *
     * @var int
     */
    protected $id;

    /**
     * The handler classname. A handler is created from this usersource, and is responsible for
     * creating all the resources needed for a scraper to do its job.
     *
     * @var string
     */
    protected $handler_class;

    /**
     * Options we'll pass to the handler.
     *
     * @var array
     */
    protected $options = [];

    /**
     * True if this scraper is enabled.
     *
     * @var bool
     */
    protected $is_enabled = true;

    /**
     * @var Application\DeskPRO\Scraper\Handler\HandlerAbstract
     */
    protected $_handler_instance = null;

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->isMappedSuperclass = true;
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setPrimaryTable(['name' => 'Scraper']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_DEFERRED_IMPLICIT);
    }
}
