<?php

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Orb\Util\DpStrings;
use Orb\Util\Strings;

/**
 * Class ReportDashboardShareableShortUrl.
 */
class ReportDashboardShareableShortUrl extends DomainObject
{
    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var ReportDashboardShareableLink
     */
    protected $shareableLink;

    /**
     * @var string
     */
    protected $authCode;

    /**
     * @var \DateTime
     */
    protected $dateCreated;

    /**
     * @var \DateTime
     */
    protected $dateExpire;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->dateCreated = new \DateTime();
        $this->dateExpire  = new \DateTime('+1 hour');
        $this->authCode    = DpStrings::random(5, Strings::CHARS_KEY);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return ReportDashboardShareableLink
     */
    public function getShareableLink()
    {
        return $this->shareableLink;
    }

    /**
     * @param ReportDashboardShareableLink $shareableLink
     *
     * @return $this
     */
    public function setShareableLink(ReportDashboardShareableLink $shareableLink = null)
    {
        $this->setModelField('shareableLink', $shareableLink);

        return $this;
    }

    /**
     * @return string
     */
    public function getAuthCode()
    {
        return $this->authCode;
    }

    /**
     * @param string $authCode
     *
     * @return $this
     */
    public function setAuthCode($authCode)
    {
        $this->setModelField('authCode', $authCode);

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * @param \DateTime $dateCreated
     *
     * @return $this
     */
    public function setDateCreated(\DateTime $dateCreated = null)
    {
        $this->setModelField('dateCreated', $dateCreated);

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateExpire()
    {
        return $this->dateExpire;
    }

    /**
     * @param \DateTime $dateExpire
     *
     * @return $this
     */
    public function setDateExpire(\DateTime $dateExpire = null)
    {
        $this->setModelField('dateExpire', $dateExpire);

        return $this;
    }

    /**
     * @return bool
     */
    public function isExpired()
    {
        $current = new \DateTime();

        return $current > $this->dateExpire;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setPrimaryTable(
            [
                'name'              => 'report_dashboard_shareable_short_url',
                'uniqueConstraints' => [
                    'auth_code' => ['columns' => ['auth_code']],
                ],
            ]
        );
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_DEFERRED_IMPLICIT);
        $metadata->mapField(
            [
                'fieldName'  => 'id',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'id',
                'id'         => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'authCode',
                'type'       => 'string',
                'length'     => 50,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'auth_code',
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'shareableLink',
                'targetEntity' => ReportDashboardShareableLink::class,
                'mappedBy'     => null,
                'inversedBy'   => 'shortUrls',
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'shareable_link_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => false,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );
        $metadata->mapField([
            'fieldName'  => 'dateCreated',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'date_created',
        ]);
        $metadata->mapField([
            'fieldName'  => 'dateExpire',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'date_expire',
        ]);

        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
    }
}
