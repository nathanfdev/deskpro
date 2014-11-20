<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Application\DeskPRO\Domain\DomainObject;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * @property int           $id
 * @property string        $name
 * @property string        $tag
 * @property array         $metadata
 * @property AppPackage    $package
 * @property Blob          $blob
 */
class AppAsset extends DomainObject
{
    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $tag = null;

    /**
     * @var \Application\DeskPRO\Entity\AppPackage
     */
    protected $package;

    /**
     * @var \Application\DeskPRO\Entity\Blob
     */
    protected $blob;

    /**
     * @var array
     */
    protected $metadata = null;

    /**
     * Set metadata
     *
     * @param array $metadata
     */
    public function setMetadata(array $metadata = null)
    {
        if (!$metadata) {
            $this->setModelField('metadata', null);
        } else {
            $this->setModelField('metadata', $metadata);
        }
    }

    /**
     * Get metadata
     *
     * @return array
     */
    public function getMetadata()
    {
        return $this->metadata ? $this->metadata : array();
    }

    /**
     * {@inheritDoc}
     */
    public function toApiData($primary = true, $deep = true, array $visited = array())
    {
        $data             = array();
        $data['id']       = $this->id;
        $data['name']     = $this->name;
        $data['tag']      = $this->tag;
        $data['metadata'] = $this->metadata;
        $data['blob']     = $this->blob->toApiData(false, $deep);

        if ($this->package->native_name && $this->tag && in_array($this->tag, array('js', 'css', 'html', 'res'))) {
            $data['blob']['download_url'] = App::get('router')->generate('serve_blob_app_asset', array('app_name' => $this->package->name, 'type' => $this->tag, 'path' => $this->name), true);
            $data['blob']['relative_url'] = App::get('router')->generate('serve_blob_app_asset', array('app_name' => $this->package->name, 'type' => $this->tag, 'path' => $this->name), true);
        }

        if ($primary) {
            $data['package'] = $this->package->toApiData(false, $deep);
        }

        return $data;
    }

    ############################################################################
    # Doctrine Metadata
    ############################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->inheritanceType           = ClassMetadataInfo::INHERITANCE_TYPE_NONE;
        $metadata->changeTrackingPolicy      = ClassMetadataInfo::CHANGETRACKING_NOTIFY;
        $metadata->generatorType             = ClassMetadataInfo::GENERATOR_TYPE_IDENTITY;
        $metadata->setPrimaryTable(array(
            'name' => 'app_assets',
        ));

        $metadata->mapField(array(
            'columnName' => 'id',
            'fieldName'  => 'id',
            'type'       => 'integer',
            'id'         => true,
            'nullable'   => false,
        ));

        $metadata->mapField(array(
            'columnName' => 'name',
            'fieldName'  => 'name',
            'type'       => 'string',
            'length'     => 255,
            'nullable'   => false,
        ));

        $metadata->mapField(array(
            'columnName' => 'tag',
            'fieldName'  => 'tag',
            'type'       => 'string',
            'length'     => 50,
            'nullable'   => true,
        ));

        $metadata->mapField(array(
            'columnName' => 'metadata',
            'fieldName'  => 'metadata',
            'type'       => 'json_array',
            'nullable'   => true,
        ));

        $metadata->mapManyToOne(array(
            'fieldName'    => 'package',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\AppPackage',
            'inversedBy'   => 'assets',
            'joinColumns'  => array(array(
                'name'                 => 'package_name',
                'referencedColumnName' => 'name',
                'nullable'             => true,
                'onDelete'             => 'CASCADE',
                'fetch'                => 'EAGER',
            )),
        ));

        $metadata->mapOneToOne(array(
            'fieldName'    => 'blob',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Blob',
            'joinColumns'  => array(array(
                'name'                 => 'blob_id',
                'referencedColumnName' => 'id',
                'nullable'             => true,
                'onDelete'             => 'CASCADE',
                'fetch'                => 'EAGER',
            )),
        ));
    }
}
