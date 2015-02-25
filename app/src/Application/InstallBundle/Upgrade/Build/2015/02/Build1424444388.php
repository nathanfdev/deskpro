<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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
 * @subpackage
 */

namespace Application\InstallBundle\Upgrade\Build;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\PhoneNumber;
use Application\DeskPRO\Form\Type\PhoneNumberType;

class Build1424444388 extends AbstractBuild
{
    public function run()
    {
        $this->out("Migrate Phone Numbers");

	    $limit = 100;
	    $offset = 0;

	    $sq = '
	        select * from people_contact_data
	        where contact_type = "phone" or contact_type = "mobile" or contact_type = "fax"
	        limit %d, %d
	        ';
	    $em = $this->container->getEm();
	    $ff = $this->container->getFormFactory();

	    while ($rows = $em->getConnection()->fetchAll(sprintf($sq, $offset, $limit))) {

		    foreach ($rows as $row) {
			    $phone = new PhoneNumber();
			    $form = $ff->create(new PhoneNumberType(), $phone);
			    $form->submit(array('number' => '+' . preg_replace('/[^0-9]/', '', $row['field_10'])));

			    if ($form->isValid()) {
				    $remove[] = $row['id'];
				    $phone->person = $em->getReference('DeskPRO:Person', $row['person_id']);
				    $em->persist($phone);
				    $em->flush();
			    }
		    }

		    $offset += $limit;
		    $em->clear();
	    }

	    $em->getConnection()->executeQuery(
		    'delete from people_contact_data where id in (:ids)',
		    array('ids' => $remove),
		    array('ids' => Connection::PARAM_INT_ARRAY)
	    );
    }
}