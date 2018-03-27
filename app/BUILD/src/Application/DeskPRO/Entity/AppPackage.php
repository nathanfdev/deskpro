<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * @property string $name
 * @property string $title
 * @property string $description
 * @property string $author_name
 * @property string $author_email
 * @property string $author_link
 * @property int    $api_version
 * @property int    $version
 * @property string $version_name
 * @property string $native_name
 * @property bool   $is_single
 * @property bool   $is_custom
 * @property array  $tags
 * @property array  $trigger_events
 * @property array  $settings_def
 * @property AppAsset[] $assets
 * @property array  $scopes
 */
class AppPackage extends DomainObject
{
    const SCOPE_AGENT     = 'agent';
    const TAG_USERSOURCES = 'usersources';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var
     */
    protected $author_name;

    /**
     * @var
     */
    protected $author_email;

    /**
     * @var
     */
    protected $author_link;

    /**
     * @var int
     */
    protected $api_version;

    /**
     * @var int
     */
    protected $version;

    /**
     * @var string
     */
    protected $version_name;

    /**
     * The app ID if this app has resources in the filesystem.
     *
     * @var string
     */
    protected $native_name;

    /**
     * True to only allow one instance of the app to be installed.
     *
     * @var bool
     */
    protected $is_single = false;

    /**
     * True to mean that this is a custom app we created from within the admin interface.
     * This shows a simplified editor in the package screen.
     *
     * @var bool
     */
    protected $is_custom = false;

    /**
     * @var array
     */
    protected $tags = [];

    /**
     * @var array
     */
    protected $trigger_events = [];

    /**
     * @var array
     */
    protected $settings_def = [];

    /**
     * @var array
     */
    protected $scopes = [];

    /**
     * @var \Application\DeskPRO\Entity\AppAsset[]
     */
    protected $assets;

    public function __construct()
    {
        $this->assets = new ArrayCollection();
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function hasTag($tag)
    {
        return in_array($tag, $this->tags);
    }

    public function isUsersource()
    {
        return $this->hasTag(self::TAG_USERSOURCES);
    }

    /**
     * @param AppAsset $asset
     */
    public function addAsset(AppAsset $asset)
    {
        $this->assets->add($asset);
        $asset->package = $this;
    }

    /**
     * @param Blob   $blob
     * @param string $filename
     *
     * @return AppAsset
     */
    public function addAssetFromBlob(Blob $blob, $filename = null)
    {
        $asset          = new AppAsset();
        $asset->name    = $filename ? $filename : $blob->filename;
        $asset->blob    = $blob;
        $asset->package = $this;
        $this->assets->add($asset);

        return $asset;
    }

    /**
     * Get one asset tagged with some name.
     *
     * @param string $tag
     *
     * @return AppAsset
     */
    public function getTaggedAsset($tag)
    {
        foreach ($this->assets as $asset) {
            if ($asset->tag == $tag) {
                return $asset;
            }
        }

        return;
    }

    /**
     * Get an array of all assets tagged with a name.
     *
     * @param string $tag
     *
     * @return AppAsset[]
     */
    public function getTaggedAssets($tag)
    {
        $assets = [];

        foreach ($this->assets as $asset) {
            if ($asset->tag == $tag) {
                $assets[] = $asset;
            }
        }

        return $assets;
    }

    /**
     * Get the asset with the filename $name.
     *
     * @param string $name
     *
     * @return AppAsset|null
     */
    public function getAsset($name)
    {
        foreach ($this->assets as $asset) {
            if ($asset->name == $name) {
                return $asset;
            }
        }

        return;
    }

    /**
     * Get manifest array.
     *
     * @return array
     */
    public function getManifest()
    {
        return [
            'package_name' => $this['name'],
            'title'        => $this['title'],
            'description'  => $this['description'],
            'tags'         => $this['tags'],
            'api_version'  => $this['api_version'],
            'version'      => $this['version'],
            'version_name' => $this['version_name'],
            'is_single'    => (bool) $this['is_single'],
            'is_native'    => null !== $this['native_name'],
            'author'       => [
                'name'  => $this['author_name'],
                'email' => $this['author_email'],
                'link'  => $this['author_link'],
            ],
            'trigger_events' => $this['trigger_events'],
            'settings_def'   => $this['settings_def'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function toApiData($primary = true, $deep = true, array $visited = [])
    {
        $data                      = [];
        $data['name']              = $this->name;
        $data['native_name']       = $this->native_name;
        $data['title']             = $this->title;
        $data['description']       = $this->description;
        $data['author_name']       = $this->author_name;
        $data['author_email']      = $this->author_email;
        $data['author_link']       = $this->author_link;
        $data['version']           = $this->version;
        $data['version_name']      = $this->version_name;
        $data['trigger_events']    = $this->trigger_events;
        $data['settings_def']      = $this->settings_def;
        $data['api_version']       = $this->api_version;
        $data['is_single']         = $this->is_single;
        $data['is_custom']         = $this->is_custom;
        $data['tags']              = $this->tags;
        $data['scopes']            = $this->scopes;
        $data['is_usersource_app'] = $this->isUsersource();

        $sizes = [16, 24, 32, 48, 64, 96, 128, 192, 256, 512];
        foreach ($sizes as $size) {
            $icon = $this->getTaggedAsset("icons.app.$size");
            if ($icon) {
                $data["icon_$size"] = $icon->blob->getDownloadUrl();
            }
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
        $metadata->setPrimaryTable([
            'name' => 'app_packages',
        ]);

        $metadata->mapField([
            'columnName' => 'name',
            'fieldName'  => 'name',
            'type'       => 'string',
            'id'         => true,
            'nullable'   => false,
        ]);

        $metadata->mapField([
            'columnName' => 'title',
            'fieldName'  => 'title',
            'type'       => 'string',
            'length'     => 255,
            'nullable'   => false,
        ]);

        $metadata->mapField([
            'columnName' => 'description',
            'fieldName'  => 'description',
            'type'       => 'string',
            'length'     => 1000,
            'nullable'   => false,
        ]);

        $metadata->mapField([
            'columnName' => 'author_name',
            'fieldName'  => 'author_name',
            'type'       => 'string',
            'length'     => 255,
            'nullable'   => false,
        ]);

        $metadata->mapField([
            'columnName' => 'author_email',
            'fieldName'  => 'author_email',
            'type'       => 'string',
            'length'     => 255,
            'nullable'   => false,
        ]);

        $metadata->mapField([
            'columnName' => 'author_link',
            'fieldName'  => 'author_link',
            'type'       => 'string',
            'length'     => 255,
            'nullable'   => false,
        ]);

        $metadata->mapField([
            'columnName' => 'api_version',
            'fieldName'  => 'api_version',
            'type'       => 'integer',
            'nullable'   => false,
        ]);

        $metadata->mapField([
            'columnName' => 'version',
            'fieldName'  => 'version',
            'type'       => 'integer',
            'nullable'   => false,
        ]);

        $metadata->mapField([
            'columnName' => 'version_name',
            'fieldName'  => 'version_name',
            'type'       => 'string',
            'length'     => 100,
            'nullable'   => false,
        ]);

        $metadata->mapField([
            'columnName' => 'native_name',
            'fieldName'  => 'native_name',
            'type'       => 'string',
            'length'     => 255,
            'nullable'   => true,
        ]);

        $metadata->mapField([
            'columnName' => 'is_single',
            'fieldName'  => 'is_single',
            'type'       => 'boolean',
            'nullable'   => false,
        ]);

        $metadata->mapField([
            'columnName' => 'is_custom',
            'fieldName'  => 'is_custom',
            'type'       => 'boolean',
            'nullable'   => false,
        ]);

        $metadata->mapField([
            'columnName' => 'tags',
            'fieldName'  => 'tags',
            'type'       => 'simple_array',
            'nullable'   => false,
        ]);

        $metadata->mapField([
            'columnName' => 'trigger_events',
            'fieldName'  => 'trigger_events',
            'type'       => 'json_array',
            'nullable'   => false,
        ]);

        $metadata->mapField([
            'columnName' => 'settings_def',
            'fieldName'  => 'settings_def',
            'type'       => 'json_array',
            'nullable'   => false,
        ]);

        $metadata->mapField([
            'columnName' => 'scopes',
            'fieldName'  => 'scopes',
            'type'       => 'simple_array',
            'nullable'   => true,
        ]);

        $metadata->mapOneToMany([
            'fieldName'    => 'assets',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\AppAsset',
            'mappedBy'     => 'package',
            'fetch'        => ClassMetadataInfo::FETCH_LAZY,
            'joinColumns'  => [
                [
                    'name'                 => 'package_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'CASCADE',
                ],
            ],
        ]);
    }
}
