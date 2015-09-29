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

namespace Application\ImportBundle\Generator\Exporter\Parser\Csv\Helper\ContactData\Inline;

use Application\ImportBundle\Entity\ContactData;

/**
 * Inline contact data parser factory.
 *
 * Class InlineContactDataFactory
 */
class InlineContactDataFactory
{
    /**
     * Returns inline contact data parser.
     *
     * @return InlineContactData
     */
    public static function create()
    {
        $contact_types = new ContactType\Collection();
        $contact_types
            ->attach(new ContactType\Mapping(ContactData::TYPE_ADDRESS, array(
                'address' => 'address',
                'city'    => 'city',
                'state'   => 'state',
                'zip'     => 'post_code',
                'country' => 'country',
            )))
            ->attach(new ContactType\Mapping(ContactData::TYPE_FACEBOOK, array(
                'profile_url' => 'facebook',
            )))
            ->attach(new ContactType\Mapping(ContactData::TYPE_INSTANT_MESSAGE, array(
                'username' => 'im',
            )))
            ->attach(new ContactType\Mapping(ContactData::TYPE_LINKED_IN, array(
                'profile_url' => 'linkedin',
            )))
            ->attach(new ContactType\Value(ContactData::TYPE_MOBILE, 'mobile', 'parseNumberToEntity'))
            ->attach(new ContactType\Value(ContactData::TYPE_FAX, 'fax', 'parseNumberToEntity'))
            ->attach(new ContactType\Value(ContactData::TYPE_PHONE, 'phone', 'parseNumberToEntity'))
            ->attach(new ContactType\Mapping(ContactData::TYPE_SKYPE, array(
                'username' => 'skype',
            )))
            ->attach(new ContactType\Mapping(ContactData::TYPE_TWITTER, array(
                'username' => 'twitter',
            )))
            ->attach(new ContactType\Mapping(ContactData::TYPE_WEBSITE, array(
                'url' => 'website',
            )))
        ;

        return new InlineContactData($contact_types);
    }
}
