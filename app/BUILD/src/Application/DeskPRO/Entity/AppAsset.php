<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Application\DeskPRO\Domain\DomainObject;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
     * Set metadata.
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
     * Get metadata.
     *
     * @return array
     */
    public function getMetadata()
    {
        return $this->metadata ? $this->metadata : [];
    }

    /**
     * {@inheritdoc}
     */
    public function toApiData($primary = true, $deep = true, array $visited = [])
    {
        $data             = [];
        $data['id']       = $this->id;
        $data['name']     = $this->name;
        $data['tag']      = $this->tag;
        $data['metadata'] = $this->metadata;
        $data['blob']     = $this->blob->toApiData(false, $deep);

        if ($this->package->native_name && $this->tag && in_array($this->tag, ['js', 'css', 'html', 'res'])) {
            $data['blob']['download_url'] = App::get('router')->generate('serve_blob_app_asset', ['app_name' => $this->package->name, 'type' => $this->tag, 'path' => $this->name], UrlGeneratorInterface::ABSOLUTE_URL);
            $data['blob']['relative_url'] = App::get('router')->generate('serve_blob_app_asset', ['app_name' => $this->package->name, 'type' => $this->tag, 'path' => $this->name], UrlGeneratorInterface::ABSOLUTE_PATH);
        } else {
            $data['blob']['download_url'] = $this->blob->getDownloadUrl(true, false);
            $data['blob']['relative_url'] = $this->blob->getDownloadUrl(false, false);
        }

        if ($primary) {
            $data['package'] = $this->package->toApiData(false, $deep);
        }

        return $data;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->inheritanceType      = ClassMetadataInfo::INHERITANCE_TYPE_NONE;
        $metadata->changeTrackingPolicy = ClassMetadataInfo::CHANGETRACKING_NOTIFY;
        $metadata->generatorType        = ClassMetadataInfo::GENERATOR_TYPE_IDENTITY;
        $metadata->setPrimaryTable([
            'name' => 'app_assets',
        ]);

        $metadata->mapField([
            'columnName' => 'id',
            'fieldName'  => 'id',
            'type'       => 'integer',
            'id'         => true,
            'nullable'   => false,
        ]);

        $metadata->mapField([
            'columnName' => 'name',
            'fieldName'  => 'name',
            'type'       => 'string',
            'length'     => 255,
            'nullable'   => false,
        ]);

        $metadata->mapField([
            'columnName' => 'tag',
            'fieldName'  => 'tag',
            'type'       => 'string',
            'length'     => 50,
            'nullable'   => true,
        ]);

        $metadata->mapField([
            'columnName' => 'metadata',
            'fieldName'  => 'metadata',
            'type'       => 'json_array',
            'nullable'   => true,
        ]);

        $metadata->mapManyToOne([
            'fieldName'    => 'package',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\AppPackage',
            'inversedBy'   => 'assets',
            'fetch'        => ClassMetadataInfo::FETCH_LAZY,
            'joinColumns'  => [
                [
                    'name'                 => 'package_name',
                    'referencedColumnName' => 'name',
                    'nullable'             => true,
                    'onDelete'             => 'CASCADE',
                ],
            ],
        ]);

        $metadata->mapOneToOne([
            'fieldName'    => 'blob',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Blob',
            'fetch'        => ClassMetadataInfo::FETCH_EAGER,
            'joinColumns'  => [
                [
                    'name'                 => 'blob_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'CASCADE',
                ],
            ],
        ]);
    }
}
