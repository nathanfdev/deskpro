<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Description of layout of the user portal.
 *
 * = $data format =
 * <pre>
 * array(
 *     array(
 *         'xxx' => 'xxx
 *     )
 * );
 * </pre>
 *
 * The type can be a short name in which case the full PHP namespace for DeskPRO's types
 * will be prepended (Application\DeskPRO\PageDisplay\Item\Portal\XXX). You may also use underscore
 * format which will be converted into camel case (some_type to SomeType).
 *
 * Keys in the data array are insignificant. They may be used to keep track of things in the designer.
 */
class PortalPageDisplay extends PageDisplayAbstract
{
    /**
     * Portal (main page).
     */
    const SECTION_PORTAL = 'portal';

    /**
     * Across the top (not columned).
     */
    const SECTION_PAGETOP = 'pagetop';

    /**
     * The sidebar.
     */
    const SECTION_SIDEBAR = 'sidebar';

    /**
     * The header content. Usually just one item thats rendered into the header.
     */
    const SECTION_HEADER = 'header';

    /**
     * The footer content. Usually just one item thats rendered into the footer.
     */
    const SECTION_FOOTER = 'footer';

    /**
     * The class handler.
     *
     * @var string
     */
    protected $type;

    /**
     * @var int
     */
    protected $display_order = 0;

    /**
     * @var bool
     */
    protected $is_enabled = 0;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $section = 'sidebar';

    /**
     * @var array
     */
    protected $data = [];

    public function addData($key, $value)
    {
        $old              = $this->data;
        $this->data[$key] = $value;
        $this->_onPropertyChanged('data', $old, $this->data);
    }

    public function removeData($key)
    {
        $old = $this->data;
        unset($this->data[$key]);
        $this->_onPropertyChanged('data', $old, $this->data);
    }

    public function deleteCachedPages()
    {
        $cache_id = "d.portal.block.block.portal_{$this->section}_".str_replace('\\', '', get_class($this));
        App::getDb()->executeUpdate('
            DELETE FROM cache WHERE id LIKE ?
        ', ["$cache_id%"]);
    }

    /**
     * @param string|null $k Specific key to fetch
     *
     * @return array|mixed|null
     */
    public function getData($k = null)
    {
        if ($k !== null) {
            if (!$this->data) {
                return;
            }

            return isset($this->data[$k]) ? $this->data[$k] : null;
        }

        return $this->data;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\PortalPageDisplay';
        $metadata->setPrimaryTable(['name' => 'portal_page_display']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField([
            'fieldName'  => 'type',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'type',
        ]);
        $metadata->mapField([
            'fieldName'  => 'display_order',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'display_order',
        ]);
        $metadata->mapField([
            'fieldName'  => 'is_enabled',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'is_enabled',
        ]);
        $metadata->mapField([
            'fieldName'  => 'id',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'id',
            'id'         => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'section',
            'type'       => 'string',
            'length'     => 50,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'section',
        ]);
        $metadata->mapField([
            'fieldName'  => 'data',
            'type'       => 'array',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'data',
        ]);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
    }
}
