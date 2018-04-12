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

namespace DeskPRO\Bundle\ImportBundle\Writer\Helper;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PhoneNumber;
use DeskPRO\Bundle\ImportBundle\Model\ContactData\Phone;
use DeskPRO\Bundle\ImportBundle\Writer\EntityPersister;
use DeskPRO\Bundle\ImportBundle\Writer\Mapper\MapperInterface;
use DeskPRO\Bundle\ImportBundle\Writer\Mapper\PhoneNumberMapper;
use libphonenumber\PhoneNumber as PhoneNumberModel;
use libphonenumber\PhoneNumberUtil;
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
            ->setGuessedType($contactModel->getType() ?: 'phone')
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
