<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use Doctrine\ORM\Mapping\ClassMetadata;
use Orb\Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

/**
 * @property int $id
 * @property string $app_id
 * @property string $app_secret
 * @property string $name
 * @property string $icon_url
 * @property string $logo_url
 */
class FacebookApp extends DomainObject
{
    /**
     * The unique ID.
     *
     * @var int
     */
    protected $id = null;

    /**
     * @var string facebook app id
     */
    protected $app_id;

    /**
     * @var string facebook app secret
     */
    protected $app_secret;

    /**
     * @var string an identifier tthat we put next to the app
     */
    protected $name;

    /**
     * @var string url to smaller icon image
     */
    protected $icon_url;

    /**
     * @var string url to logo url
     */
    protected $logo_url;

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);
        $builder
            ->setTable('facebook_apps')
            ->setCustomRepositoryClass('Application\DeskPRO\EntityRepository\FacebookApp')
            ->setChangeTrackingPolicyNotify()
        ;
        $builder->mapId();
        $builder->mapString('app_id', null, null, true);
        $builder->mapString('app_secret');
        $builder->mapString('name');
        $builder->mapString('icon_url');
        $builder->mapString('logo_url');
    }
}
