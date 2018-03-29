<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use JMS\Serializer\Annotation as JMS;
use libphonenumber\PhoneNumberUtil;
use Orb\Util\PhoneNumbers;

/**
 * A Phone Number that is registered somewhere in the system (people can have many phone numbers).
 *
 * Plans are to add: $label human readable label, $type flag, $sms_capable flag, $sms_validated bool,
 *                   $voice_validated bool (and a virtual field that is bool isValidated(), returns true if
 *                   it was validated in either way).
 *
 * @property int $id
 * @property Person $person
 * @property string $number
 * @property string $region
 * @property string $guessed_type
 * @property \DateTime $date_created
 *
 * @JMS\ExclusionPolicy("ALL")
 *
 * @AppAssert\PhoneNumber()
 */
class PhoneNumber extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * The unique ID.
     *
     * @var int
     */
    protected $id = null;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * The number, stored in E.164 string format, ie. +19021111111.
     *
     * @var string
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    protected $number;

    /**
     * A human-defined (optional) label to describe what this phone number is.
     *
     * @var string
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    protected $label;

    /**
     * An extension for the number - optional.
     *
     * @var string
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     * @JMS\SerializedName("extension")
     */
    protected $ext;

    /**
     * The ISO 3166-1 country/region code of the phone number (2 char).
     *
     * @var string
     */
    protected $region;

    /**
     * @var int see Orb\Util\PhoneNumbers constants for the meanings of the ints stored here
     */
    protected $guessed_type;

    /**
     * @var \DateTime
     */
    protected $date_created;

    /**
     * LOOK at the static createEntity factory method, don't try to create yourself.
     *
     * @param null $number
     * @param null $region
     */
    public function __construct($number = null, $region = null, $guessed_type = null)
    {
        if ($number) {
            $this->setModelField('number', $number);
            $this->setModelField('region', $region);
            $this->setModelField('guessed_type', $guessed_type);
        }
        $this->setModelField('date_created', new \DateTime());
    }

    /**
     * @param $phone_number
     *
     * @return PhoneNumber
     */
    public static function createEntity($phone_number)
    {
        try {
            return self::parseNumber($phone_number);
        } catch (\Exception $e) {
            return;
        }
    }

    /**
     * @param $phone_number
     *
     * @throws \Exception
     *
     * @return void|static
     */
    public static function parseNumber($phone_number)
    {
        $phone_util = PhoneNumberUtil::getInstance();
        $number     = $phone_util->parse($phone_number, null);

        if (!$phone_util->isValidNumber($number)) {
            throw new \Exception(sprintf('Invalid number %s', $number));
        }

        $phone_number = (string) $number;
        $region       = $phone_util->getRegionCodeForNumber($number);
        $type         = PhoneNumbers::getType($phone_number);

        if (empty($phone_number) || empty($region) || empty($type)) {
            return;
        }

        return new static($phone_number, $region, $type);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function getFullFormatted()
    {
        $num = $this->getNumberFormatted();

        if ($ext = $this->ext) {
            $num .= ' ext . '.$this->ext;
        }

        return $num;
    }

    public function getNumberFormatted()
    {
        try {
            return PhoneNumbers::toInternationalFormat($this->number);
        } catch (\Exception $e) {
            return $this->number;
        }
    }

    public function getFormattedForVCard()
    {
        $number = (string) $this->getPhoneNumber();

        if ($ext = $this->ext) {
            $number .= ';ext='.$ext;
        }

        return $number;
    }

    /**
     * @param Person $person
     *
     * @return $this
     */
    public function setPerson(Person $person = null)
    {
        $this->setModelField('person', $person);

        return $this;
    }

    /**
     * @param string $number
     *
     * @return $this
     */
    public function setNumber($number)
    {
        $this->setModelField('number', $number);

        return $this;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param string $guessed_type
     *
     * @return $this
     */
    public function setGuessedType($guessed_type)
    {
        $this->setModelField('guessed_type', $guessed_type);

        return $this;
    }

    /**
     * @return string
     */
    public function getGuessedType()
    {
        return $this->guessed_type;
    }

    /**
     * @return string|int|null
     */
    public function getExt()
    {
        return $this->ext;
    }

    /**
     * @param string|int|null $ext
     */
    public function setExt($ext)
    {
        $this->setModelField('ext', $ext);
    }

    /**
     * @return \libphonenumber\PhoneNumber|null
     */
    public function getPhoneNumber()
    {
        $phone_util = PhoneNumberUtil::getInstance();
        try {
            return $phone_util->parse($this->number, null);
        } catch (\Exception $e) {
            return;
        }
    }

    /**
     * @param string $region
     *
     * @return $this
     */
    public function setRegion($region)
    {
        $region = strtoupper($region);
        $this->setModelField('region', $region);

        return $this;
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\PhoneNumber';

        $metadata->setPrimaryTable(
            [
                'name'    => 'phone_numbers',
                'indexes' => ['phone_number_idx' => ['columns' => ['number']]],
            ]
        );
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);

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
                'fieldName'  => 'number',
                'type'       => 'string',
                'length'     => 30,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'number',
            ]
        );

        $metadata->mapField(
            [
                'fieldName'  => 'ext',
                'type'       => 'string',
                'length'     => 30,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'ext',
            ]
        );

        $metadata->mapField(
            [
                'fieldName'  => 'label',
                'type'       => 'string',
                'length'     => 100,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'label',
            ]
        );

        $metadata->mapField(
            [
                'fieldName'  => 'region',
                'type'       => 'string',
                'length'     => 2,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'region',
            ]
        );

        $metadata->mapField(
            [
                'fieldName'  => 'guessed_type',
                'type'       => 'string',
                'precision'  => 10,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'guessed_type',
            ]
        );

        $metadata->mapField(
            [
                'fieldName'  => 'date_created',
                'type'       => 'datetime',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'date_created',
            ]
        );

        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'person',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
                'mappedBy'     => null,
                'inversedBy'   => 'phone_numbers',
                'cascade'      => ['persist'],
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'person_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );
    }
}
