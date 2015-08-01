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

namespace Application\ImportBundle\Generator\Exporter\ContactData;

use Application\ImportBundle\Entity\ContactData;

/**
 * Contact data helper
 *
 * Interface ContactDataInterface
 * @package Application\ImportBundle\Generator\Exporter\ContactData
 */
interface ContactDataInterface
{
    const TYPE_ADDRESS         = 'address';
    const TYPE_FACEBOOK        = 'facebook';
    const TYPE_FAX             = 'fax';
    const TYPE_INSTANT_MESSAGE = 'instant_message';
    const TYPE_LINKED_IN       = 'linked_in';
    const TYPE_MOBILE          = 'mobile';
    const TYPE_PHONE           = 'phone';
    const TYPE_SKYPE           = 'skype';
    const TYPE_TWITTER         = 'twitter';
    const TYPE_WEBSITE         = 'website';

    /**
     * Helper type
     *
     * @return string
     */
    public function getType();

    /**
     * Creates a contact data entity
     *
     * @param array $data
     * @return ContactData
     */
    public function create(array $data);
}
