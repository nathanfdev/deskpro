<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Helper;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PhoneNumber;
use DeskPRO\Bundle\ImportBundle\Model\ContactData\Phone;
use DeskPRO\Bundle\ImportBundle\Writer\EntityPersister;
use DeskPRO\Bundle\ImportBundle\Writer\Mapper\MapperInterface;
use DeskPRO\Bundle\ImportBundle\Writer\Mapper\PhoneNumberMapper;
use libphonenumber\PhoneNumber as PhoneNumberModel;
use libphonenumber\PhoneNumberUtil;
use Orb\Util\PhoneNumbers;
use Psr\Log\LoggerInterface;

/**
 * Class PersonContactDataHelper.
 */
class PersonContactDataHelper extends AbstractContactDataHelper
{
    /**
     * @var PhoneNumberMapper
     */
    protected $phoneNumberMapper;

    /**
     * Constructor.
     *
     * @param PhoneNumberMapper  $phoneNumberMapper
     * @param MapperInterface    $contactDataMapper
     * @param CreateEntityHelper $createEntityHelper
     * @param EntityPersister    $persister
     * @param LoggerInterface    $logger
     */
    public function __construct(
        PhoneNumberMapper  $phoneNumberMapper,
        MapperInterface    $contactDataMapper,
        CreateEntityHelper $createEntityHelper,
        EntityPersister    $persister,
        LoggerInterface    $logger
    ) {
        parent::__construct($contactDataMapper, $createEntityHelper, $persister, $logger);
        $this->phoneNumberMapper = $phoneNumberMapper;
    }

    /**
     * {@inheritdoc}
     *
     * @param Person $entity
     */
    protected function createOrUpdatePhoneNumber(PhoneNumberModel $phoneNumber, Phone $contactModel, $entity)
    {
        /** @var PhoneNumber $contactEntity */
        $contactEntity = $this->createEntityHelper->findOrCreateEntity($this->phoneNumberMapper, $contactModel);
        $contactEntity
            ->setNumber($contactModel->getNumber())
            ->setRegion(PhoneNumberUtil::getInstance()->getRegionCodeForNumber($phoneNumber))
            ->setLabel($contactModel->getType() ?: 'phone')
            ->setGuessedType(PhoneNumbers::getTypeCode($contactModel->getNumber()))
            ->setPerson($entity)
        ;

        // prevent dupes
        foreach ($entity->getPhoneNumbers() as $existingPhone) {
            if ($existingPhone->getNumber() === $contactEntity->getNumber()) {
                return;
            }
        }

        $this->persister->persistAndFlush($contactEntity, $contactModel);
    }
}
