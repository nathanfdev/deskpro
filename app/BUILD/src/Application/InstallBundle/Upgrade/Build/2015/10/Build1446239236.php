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

namespace Application\InstallBundle\Upgrade\Build;

use Orb\Util\Strings;

class Build1446239236 extends AbstractBuild
{
    public function run()
    {
        $this->out('Migrate Ticket Field Filters');

        $db = $this->container->getDb();
        $q  = 'select id, terms from ticket_filters';
        foreach ($db->fetchAll($q) as $row) {
            $terms  = json_decode($row['terms'], 1);
            $update = false;

            foreach ($terms as &$term) {
                if (0 !== strpos(@$term['type'], 'ticket_field') && 0 !== strpos(@$term['type'], 'person_field') && 0 !== strpos(@$term['type'], 'org_field')) {
                    continue;
                }
                $id = Strings::extractRegexMatch('#\[(\d+)\]$#', $term['type']);

                if (isset($term['options']['custom_fields']['field_'.$id])) {
                    continue;
                }

                $options = $term['options'];
                if (isset($options['value'])) {
                    $options = $options['value'];
                }

                $term['options'] = [
                    'custom_fields' => [
                        'field_'.$id => $options,
                    ],
                ];
                $update = true;
            }

            if ($update) {
                $db->update('ticket_filters', ['terms' => json_encode($terms)], ['id' => $row['id']]);
            }
        }
    }
}
