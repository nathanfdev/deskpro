<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * Contact data helper factory.
 *
 * Class ContactDataFactory
 */
class ContactDataFactory
{
    /**
     * Return helper by contact type.
     *
     * @param string $contact_type
     *
     * @return ContactDataInterface
     */
    public static function getHandler($contact_type)
    {
        static $collection;

        if (null === $collection) {
            $collection = new Collection();
            $collection
                ->attach(new Address())
                ->attach(new Facebook())
                ->attach(new Fax())
                ->attach(new InstantMessage())
                ->attach(new LinkedIn())
                ->attach(new Mobile())
                ->attach(new Phone())
                ->attach(new Skype())
                ->attach(new Twitter())
                ->attach(new Website())
            ;
        }

        return $collection->getByType($contact_type);
    }
}
