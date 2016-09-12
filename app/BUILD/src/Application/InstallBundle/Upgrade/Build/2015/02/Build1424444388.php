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

/**
 * DeskPRO.
 */

namespace Application\InstallBundle\Upgrade\Build;

use Application\DeskPRO\Entity\PhoneNumber;
use Application\DeskPRO\Form\Type\PhoneNumberType;

class Build1424444388 extends AbstractBuild
{
    public function run()
    {
        $this->out('Migrate Phone Numbers');

        $sq = "
            SELECT * FROM people_contact_data
            WHERE contact_type IN ('phone', 'mobile', contact_type = 'fax')
            LIMIT 250
        ";

        $date_now = date('Y-m-d H:i:s');
        $db       = $this->container->getDb();
        $ff       = $this->container->getFormFactory();

        while ($rows = $db->fetchAll($sq)) {
            $remove         = [];
            $values         = [];
            $invalid_values = [];

            foreach ($rows as $row) {
                $remove[] = $row['id'];
                $phone    = new PhoneNumber();
                $form     = $ff->create(new PhoneNumberType(), $phone);
                $form->submit(['number' => '+'.preg_replace('/[^0-9]/', '', $row['field_10'])]);

                if ($form->isValid()) {
                    $values[] = [
                        'person_id'    => $row['person_id'],
                        'number'       => $phone->number,
                        'region'       => $phone->region,
                        'guessed_type' => $phone->guessed_type,
                        'date_created' => $date_now,
                    ];
                } else {
                    $invalid_values[] = [
                        'person_id'    => $row['person_id'],
                        'agent_id'     => $row['person_id'],
                        'date_created' => $date_now,
                        'note'         => 'Invalid phone number could not be imported: '.$row['field_10'],
                    ];
                }
            }

            if ($values) {
                $db->batchInsert('phone_numbers', $values, true);
                $this->out('Migrated '.count($values).' numbers...');
            }

            if ($invalid_values) {
                $db->batchInsert('people_notes', $invalid_values, true);
                $this->out('Saved '.count($invalid_values).' invalid numbers as notes...');
            }

            if ($remove) {
                $db->deleteIn('people_contact_data', $remove);
            }
        }
    }
}
