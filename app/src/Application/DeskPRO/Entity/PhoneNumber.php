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
     * The number, stored in E.164 string format, ie. +19021111111
     *
     * @var string
     */
    protected $number;

    /**
     * The ISO 3166-1 country/region code of the phone number (2 char)
     *
     * @var string
     */
    protected $region;

    /**
     * @var int see Orb\Utils\PhoneNumbers constants for the meanings of the ints stored here
     */
    protected $guessed_type;

    /**
     * @var \DateTime
     */
    protected $date_created;

    public function __construct($number = null)
    {
        if ($number) {
            $this->setNumber($number);
        }
        $this->setModelField('date_created', new \DateTime());
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * We do logic here (with the help of Google's libphonenumber) to
     * get the region code, and validate/format the number.
     *
     * @param string $number
     */
    public function setNumber($number)
    {
        if (!PhoneNumbers::isValid($number)) {
            throw new \InvalidArgumentException("Phone number is invalid");
        }

        $region = PhoneNumbers::getRegionForNumber($number);
        $formatted = PhoneNumbers::toE164Format($number);
        if ($region && $formatted) {
            $guessed_type = PhoneNumbers::getTypeCode($formatted);
            $this->setRegion($region);
            $this->setModelField('number', $formatted);
            $this->setModelField('guessed_type', $guessed_type);
        } else {
            throw new \InvalidArgumentException("Phone number is invalid - couldn't extract region information");
        }
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
            return null;
        }
    }

    public function setRegion($region)
    {
        $region = strtoupper($region);
        $this->setModelField('region', $region);
    }

    ############################################################################
    # Doctrine Metadata
    ############################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\PhoneNumber';

        $metadata->setPrimaryTable(array( 'name'    => 'phone_numbers',
                                          'indexes' => array( 'phone_number_idx' => array( 'columns' => array( 'number' ) ), ), ));
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);

        $metadata->mapField(array( 'fieldName' => 'id', 'type' => 'integer', 'precision' => 0, 'scale' => 0,
                                   'nullable'  => false, 'columnName' => 'id', 'id' => true, ));

        $metadata->mapField(array( 'fieldName' => 'number', 'type' => 'string', 'length' => 30, 'precision' => 0,
                                   'scale'     => 0, 'nullable' => false, 'columnName' => 'number', ));

        $metadata->mapField(array( 'fieldName' => 'region', 'type' => 'string', 'length' => 2, 'precision' => 0,
                                   'scale'     => 0, 'nullable' => false, 'columnName' => 'region', ));

        $metadata->mapField(array( 'fieldName' => 'guessed_type', 'type' => 'integer', 'precision' => 10,
                                   'scale'     => 0, 'nullable' => false, 'columnName' => 'guessed_type', ));

        $metadata->mapField(array( 'fieldName' => 'date_created', 'type' => 'datetime', 'precision' => 0, 'scale' => 0,
                                   'nullable'  => false, 'columnName' => 'date_created', ));

        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne(array( 'fieldName'    => 'person',
                                       'targetEntity' => 'Application\\DeskPRO\\Entity\\Person', 'mappedBy' => null,
                                       'inversedBy'   => 'phone_numbers',
                                       'joinColumns'  => array( 0 => array( 'name'                 => 'person_id',
                                                                            'referencedColumnName' => 'id',
                                                                            'nullable'             => true,
                                                                            'onDelete'             => 'cascade',
                                                                            'columnDefinition'     => null, ), ), ));
    }
}
