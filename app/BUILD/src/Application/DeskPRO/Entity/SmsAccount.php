<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Orb\Util\PhoneNumbers;

/**
 * @property int $id
 * @property string $type
 * @property array $params
 * @property string $identifier
 * @property bool $is_enabled
 * @property bool $is_connected
 * @property bool $is_tested
 * @property string $test_code
 * @property PhoneNumber|null $phone_number
 */
class SmsAccount extends DomainObject
{
    /**
     * The unique ID.
     *
     * @var int
     */
    protected $id = null;

    /**
     * @var string the string type of the provider (SmsProviderInterface::getName)
     */
    protected $type;

    /**
     * @var array any parameters that the provider factory needs to create the provider of
     */
    protected $params;

    /**
     * @var string an identifier that we put next to the account in the UI
     */
    protected $identifier;

    /**
     * @var PhoneNumber a stored phone number that is used
     */
    protected $phone_number;

    /**
     * @var bool if the acount is enabled or not
     */
    protected $is_enabled;

    /**
     * @var bool the account succeeded in connecting to the API with current credentials
     */
    protected $is_connected;

    /**
     * @var bool if the account was tested via SMS with the current credentials
     */
    protected $is_tested;

    /**
     * @var string the code used in a text message to test this account
     */
    protected $test_code;

    public function __construct()
    {
        $this->is_enabled   = false;
        $this->is_connected = false;
        $this->is_tested    = false;
    }

    public function toApiData($primary = true, $deep = true, array $visited = [])
    {
        $data = parent::toApiData($primary, $deep, $visited);

        $data['phone_number'] = null;
        if ($this->phone_number) {
            $data['phone_number'] = PhoneNumbers::toInternationalFormat($this->phone_number->number);
        }
        $data['phone_number_region'] = $this->phone_number ? $this->phone_number->region : null;

        return $data;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\SmsAccount';
        $metadata->setPrimaryTable(['name' => 'sms_accounts']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapField(
            [
                'fieldName'  => 'id',
                'type'       => 'integer',
                'nullable'   => false,
                'columnName' => 'id',
                'id'         => true,
                'precision'  => 0,
                'scale'      => 0,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'type',
                'type'       => 'string',
                'length'     => 20,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'type',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'params',
                'type'       => 'array',
                'columnName' => 'params',
                'nullable'   => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'identifier',
                'type'       => 'string',
                'length'     => 128,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'identifier',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'is_enabled',
                'type'       => 'boolean',
                'columnName' => 'is_enabled',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'is_connected',
                'type'       => 'boolean',
                'columnName' => 'is_connected',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'is_tested',
                'type'       => 'boolean',
                'columnName' => 'is_tested',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'test_code',
                'type'       => 'string',
                'columnName' => 'test_code',
                'nullable'   => true,
            ]
        );
        $metadata->mapOneToOne(
            [
                'fieldName'     => 'phone_number',
                'targetEntity'  => 'Application\\DeskPRO\\Entity\\PhoneNumber',
                'cascade'       => ['all'],
                'orphanRemoval' => true,
                'joinColumns'   => [
                    [
                        'name'                 => 'phone_number_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'SET NULL',
                    ],
                ],
            ]
        );
    }
}
