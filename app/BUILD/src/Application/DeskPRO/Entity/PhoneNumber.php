<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
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
     */
    protected $number;

    /**
     * A human-defined (optional) label to describe what this phone number is.
     *
     * @var string
     */
    protected $label;

    /**
     * An extension for the number - optional.
     *
     * @var string
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

    public function setRegion($region)
    {
        $region = strtoupper($region);
        $this->setModelField('region', $region);
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
                'type'       => 'integer',
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
