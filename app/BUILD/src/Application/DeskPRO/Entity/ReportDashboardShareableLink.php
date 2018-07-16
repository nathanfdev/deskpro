<?php

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Orb\Util\DpStrings;
use Orb\Util\Strings;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ReportDashboardShareableLink.
 */
class ReportDashboardShareableLink extends DomainObject
{
    const USE_ANYONE    = 'anyone';
    const USE_WHITELIST = 'whitelist';

    /**
     * @var int
     */
    protected $id = null;

    /**
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $authCode;

    /**
     * @Assert\NotNull()
     *
     * @var ReportDashboard
     */
    protected $dashboard;

    /**
     * @var ReportDashboardReport
     */
    protected $defaultReport;

    /**
     * @var string
     */
    protected $whoCanUse = self::USE_ANYONE;

    /**
     * @Assert\All(constraints={
     *     @Assert\Ip()
     * })
     *
     * @var array
     */
    protected $ipWhitelist = [];

    /**
     * @var ReportDashboardShareableShortUrl[]|ArrayCollection
     */
    protected $shortUrls;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->shortUrls = new ArrayCollection();
        $this->authCode  = DpStrings::random(15, Strings::CHARS_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->setModelField('title', $title);

        return $this;
    }

    /**
     * @return ReportDashboard
     */
    public function getDashboard()
    {
        return $this->dashboard;
    }

    /**
     * @param ReportDashboard $dashboard
     *
     * @return $this
     */
    public function setDashboard(ReportDashboard $dashboard = null)
    {
        $this->setModelField('dashboard', $dashboard);

        return $this;
    }

    /**
     * @return ReportDashboardReport
     */
    public function getDefaultReport()
    {
        return $this->defaultReport;
    }

    /**
     * @param ReportDashboardReport $defaultReport
     *
     * @return $this
     */
    public function setDefaultReport(ReportDashboardReport $defaultReport = null)
    {
        $this->setModelField('defaultReport', $defaultReport);

        return $this;
    }

    /**
     * @return string
     */
    public function getWhoCanUse()
    {
        return $this->whoCanUse;
    }

    /**
     * @param string $whoCanUse
     *
     * @return $this
     */
    public function setWhoCanUse($whoCanUse)
    {
        $this->setModelField('whoCanUse', $whoCanUse);

        return $this;
    }

    /**
     * @return array
     */
    public function getIpWhitelist()
    {
        return $this->ipWhitelist;
    }

    /**
     * @param array $ipWhitelist
     *
     * @return $this
     */
    public function setIpWhitelist(array $ipWhitelist)
    {
        $this->setModelField('ipWhitelist', $ipWhitelist);

        return $this;
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
     * @return string
     */
    public function getAuthCode()
    {
        return $this->authCode;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setPrimaryTable(
            [
                'name'              => 'report_dashboard_shareable_links',
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
                'fieldName'  => 'title',
                'type'       => 'string',
                'length'     => 255,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'title',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'authCode',
                'type'       => 'string',
                'length'     => 255,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'auth_code',
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'dashboard',
                'targetEntity' => ReportDashboard::class,
                'mappedBy'     => null,
                'inversedBy'   => 'reports',
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'dashboard_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => false,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'defaultReport',
                'targetEntity' => ReportDashboardReport::class,
                'mappedBy'     => null,
                'inversedBy'   => 'reports',
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'default_report_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'whoCanUse',
                'type'       => 'string',
                'length'     => 50,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'who_can_use',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'ipWhitelist',
                'type'       => 'json_array',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'ip_whitelist',
            ]
        );
        $metadata->mapOneToMany([
            'fieldName'     => 'shortUrls',
            'targetEntity'  => ReportDashboardShareableShortUrl::class,
            'mappedBy'      => 'shareableLink',
            'inversedBy'    => null,
            'cascade'       => ['persist', 'remove'],
            'orphanRemoval' => true,
        ]);

        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
    }
}
