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

/**
 * Address contact data helper.
 *
 * Class Address
 */
final class Address extends AbstractContactData
{
    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return ContactData::TYPE_ADDRESS;
    }

    /**
     * {@inheritdoc}
     */
    public function toEntity(array $data)
    {
        $contact = parent::toEntity($data);

        $contact->setField1(isset($data['address']) ? $data['address'] : '');
        $contact->setField2(isset($data['city']) ? $data['city'] : '');
        $contact->setField3(isset($data['state']) ? $data['state'] : '');
        $contact->setField4(isset($data['zip']) ? $data['zip'] : '');
        $contact->setField5(isset($data['country']) ? $data['country'] : '');

        return $contact;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(ContactData $entity)
    {
        return array_merge(parent::toArray($entity), [
            'address' => $entity->getField1(),
            'city'    => $entity->getField2(),
            'state'   => $entity->getField3(),
            'zip'     => $entity->getField4(),
            'country' => $entity->getField5(),
        ]);
    }
}
