<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace Application\ImportBundle\ContactData;

use Application\ImportBundle\Entity\ContactData;
use Orb\Util\PhoneNumbers;

/**
 * Abstract phone contact data helper.
 *
 * Class AbstractPhone
 */
abstract class AbstractPhone extends AbstractContactData
{
    /**
     * Parses number to a contact data entity.
     *
     * @param string $number
     *
     * @return array
     */
    public function parseNumberToEntity($number)
    {
        $contact = new ContactData();
        $contact
            ->setRawData($number)
            ->setContactType($this->getType())
            ->setField1(PhoneNumbers::getRegionForNumber($number))
            ->setField2(PhoneNumbers::toE164Format($number))
            ->setField3(PhoneNumbers::getType($number))
        ;

        return $contact;
    }

    /**
     * {@inheritdoc}
     */
    public function toEntity(array $data)
    {
        if (isset($data['number']) && !isset($data['country_calling_code']) && !isset($data['type'])) {
            return $this->parseNumberToEntity($data['number']);
        }

        $contact = parent::toEntity($data);

        $contact->setField1(isset($data['country_calling_code']) ? $data['country_calling_code'] : '');
        $contact->setField2(isset($data['number']) ? $data['number'] : '');
        $contact->setField3(isset($data['type']) ? $data['type'] : 'phone');

        return $contact;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(ContactData $entity)
    {
        return array_merge(parent::toArray($entity), [
            'country_calling_code' => $entity->getField1(),
            'number'               => $entity->getField2(),
            'type'                 => $entity->getField3(),
        ]);
    }
}
